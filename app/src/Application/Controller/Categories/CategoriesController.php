<?php

namespace App\Application\Controller\Categories;

use App\Application\Domain\Dtos\Categories\CreateCategoryDto;
use App\Application\Domain\Dtos\Categories\UpdateCategoryDto;
use App\Application\Domain\Dtos\Commons\PaginationQueryDto;
use App\Application\Domain\Entities\Categories\Entity\Category;
use App\Application\Helpers\ApiResponse;
use App\Application\Port\Output\Repositories\CategoryRepositoryPort;
use App\Application\Presenters\Category\CategoryPresenter;
use App\Application\Presenters\Commons\PaginatorPresenter;
use App\Application\UseCases\Categories\CreateCategoryUseCase;
use App\Application\UseCases\Categories\DeleteCategorytUseCase;
use App\Application\UseCases\Categories\FindAllCategoriesUseCase;
use App\Application\UseCases\Categories\FindCategoryUseCase;
use App\Application\UseCases\Categories\UpdateCategoryUseCase;
use App\Application\UseCases\Products\DeleteProductUseCase;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/categories')]
class CategoriesController extends AbstractController
{
    public function __construct(
        private readonly CategoryRepositoryPort $categoryRepository,
    ) {

    }

    #[Route('', methods: ['POST'])]
    #[OA\Post(
        path: '/api/categories',
        operationId: 'createCategory',
        summary: 'Cria uma categoria',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', maxLength: 150, example: 'Bebidas'),
                    new OA\Property(property: 'description', type: 'string', maxLength: 5000, example: 'Refrigerantes, sucos e água', nullable: true),
                ]
            )
        ),
        tags: ['Categories'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Categoria criada com sucesso',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'success'),
                        new OA\Property(property: 'code', type: 'integer', example: 200),
                        new OA\Property(property: 'message', type: 'string', example: 'OK'),
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'name', type: 'string', example: 'Bebidas'),
                                new OA\Property(property: 'description', type: 'string', example: 'Refrigerantes, sucos e água', nullable: true),
                                new OA\Property(property: 'created_at', type: 'string', example: '2025-12-25 12:34:56'),
                                new OA\Property(property: 'updated_at', type: 'string', example: '2025-12-25 12:34:56'),
                            ],
                            type: 'object'
                        ),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Payload inválido (erros de validação)',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'errors', type: 'string', example: 'name: This value should not be blank.'),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: 500,
                description: 'Erro interno',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'error'),
                        new OA\Property(property: 'code', type: 'integer', example: 500),
                        new OA\Property(property: 'message', type: 'string', example: '  Internal server error: ...'),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items(type: 'string')),
                    ],
                    type: 'object'
                )
            ),
        ]
    )]
    public function create(Request $req, ValidatorInterface $validator): JsonResponse
    {
        try {
            $payload = json_decode($req->getContent() ?? '{}', true) ?: [];
            $useCase = new CreateCategoryUseCase($this->categoryRepository);

            $dto = new CreateCategoryDto();
            $dto->name = $payload['name'] ?? '';
            $dto->description = $payload['description'] ?? null;

            $errors = $validator->validate($dto);

            if (count($errors) > 0) {
                return $this->json(['errors' => (string)$errors], 400);
            }

            $category = $useCase->execute($dto);

            return ApiResponse::success(CategoryPresenter::toResponse($category));
        } catch (\Throwable $e) {
            return ApiResponse::error('  Internal server error: ' . $e->getMessage());
        }
    }

    #[Route('', methods: ['GET'])]
    #[OA\Get(
        path: '/api/categories',
        summary: 'Lista categorias com paginação',
        tags: ['Categories'],
        parameters: [
            new OA\Parameter(name: 'page', description: 'Número da página', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 1, minimum: 1)),
            new OA\Parameter(name: 'perPage', description: 'Itens por página (legacy)', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 10, minimum: 1, maximum: 100)),
            new OA\Parameter(name: 'limit', description: 'Quantidade de itens retornados', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 10, minimum: 1, maximum: 100)),
            new OA\Parameter(name: 'orderBy', description: 'Campo para ordenação', in: 'query', required: false, schema: new OA\Schema(type: 'string', default: 'id')),
            new OA\Parameter(name: 'orderDirection', description: 'Direção da ordenação', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['ASC', 'DESC'], default: 'ASC'))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Lista paginada de categorias',
                content: new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(
                        properties: [
                            new OA\Property(property: 'success', type: 'boolean', example: true),
                            new OA\Property(
                                property: 'data',
                                properties: [
                                    new OA\Property(
                                        property: 'items',
                                        type: 'array',
                                        items: new OA\Items(
                                            properties: [
                                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                                new OA\Property(property: 'name', type: 'string', example: 'Bebidas'),
                                                new OA\Property(property: 'description', type: 'string', example: 'Refrigerantes, sucos e água', nullable: true),
                                                new OA\Property(property: 'created_at', type: 'string', example: '2025-12-25 12:34:56'),
                                                new OA\Property(property: 'updated_at', type: 'string', example: '2025-12-25 12:34:56'),
                                            ],
                                            type: 'object'
                                        )
                                    ),
                                    new OA\Property(
                                        property: 'meta',
                                        properties: [
                                            new OA\Property(property: 'page', type: 'integer', example: 1),
                                            new OA\Property(property: 'limit', type: 'integer', example: 10),
                                            new OA\Property(property: 'total', type: 'integer', example: 2)
                                        ],
                                        type: 'object'
                                    )
                                ],
                                type: 'object'
                            )
                        ],
                        type: 'object'
                    )
                )
            ),
            new OA\Response(response: 500, description: 'Erro interno do servidor')
        ]
    )]
    public function findAll(Request $req): JsonResponse
    {
        try {
            $pagination = PaginationQueryDto::fromRequest($req);
            $useCase = new FindAllCategoriesUseCase($this->categoryRepository);

            $result = $useCase->execute($pagination);

            $items = array_map(function (Category $product) {
                return CategoryPresenter::toResponse($product);
            }, $result);

            return ApiResponse::success(PaginatorPresenter::toResponse($pagination, $items));
        } catch (\Throwable $e) {
            return ApiResponse::error('  Internal server error: ' . $e->getMessage());
        }
    }

    #[Route('/{id}', methods: ['GET'])]
    #[OA\Get(
        path: '/api/categories/{id}',
        summary: 'Obtém uma categoria pelo ID',
        tags: ['Categories'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID da categoria',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Categoria encontrada com sucesso',
                content: new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(
                        properties: [
                            new OA\Property(property: 'success', type: 'boolean', example: true),
                            new OA\Property(
                                property: 'data',
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 1),
                                    new OA\Property(property: 'name', type: 'string', example: 'Bebidas'),
                                    new OA\Property(property: 'description', type: 'string', example: 'Refrigerantes, sucos e água', nullable: true),
                                    new OA\Property(property: 'created_at', type: 'string', example: '2025-12-25 12:34:56'),
                                    new OA\Property(property: 'updated_at', type: 'string', example: '2025-12-25 12:34:56'),
                                ],
                                type: 'object'
                            )
                        ],
                        type: 'object'
                    )
                )
            ),
            new OA\Response(response: 404, description: 'Categoria não encontrada'),
            new OA\Response(response: 500, description: 'Erro interno do servidor')
        ]
    )]
    public function find(Request $req): JsonResponse
    {
        try {
            $id = (int)$req->attributes->get('id');
            $useCase = new FindCategoryUseCase($this->categoryRepository);

            $category = $useCase->execute($id);

            return ApiResponse::success(CategoryPresenter::toResponse($category));
        } catch (NotFoundHttpException $e) {
            return ApiResponse::error(
                message: $e->getMessage(),
                code: 404
            );
        } catch (\Throwable $e) {
            return ApiResponse::error('   Internal server error: ' . $e->getMessage());
        }
    }

    #[Route('/{id}', methods: ['PATCH'])]
    #[OA\Patch(
        path: '/api/categories/{id}',
        summary: 'Atualiza uma categoria existente',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'name', type: 'string', maxLength: 150, example: 'Bebidas (atualizado)', nullable: true),
                    new OA\Property(property: 'description', type: 'string', maxLength: 5000, example: 'Refrigerantes, sucos e água', nullable: true),
                ]
            )
        ),
        tags: ['Categories'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID da categoria:',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Categoria atualizada com sucesso',
                content: new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(
                        properties: [
                            new OA\Property(property: 'success', type: 'boolean', example: true),
                            new OA\Property(
                                property: 'data',
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 1),
                                    new OA\Property(property: 'name', type: 'string', example: 'Bebidas (atualizado)'),
                                    new OA\Property(property: 'description', type: 'string', example: 'Refrigerantes, sucos e água', nullable: true),
                                    new OA\Property(property: 'created_at', type: 'string', example: '2025-12-25 12:34:56'),
                                    new OA\Property(property: 'updated_at', type: 'string', example: '2025-12-25 12:34:56'),
                                ],
                                type: 'object'
                            )
                        ],
                        type: 'object'
                    )
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Payload inválido (erros de validação)',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'errors', type: 'string', example: 'name: This value should not be blank.'),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(response: 409, description: 'Conflito (nome já existente)'),
            new OA\Response(response: 404, description: 'Categoria não encontrada'),
            new OA\Response(response: 500, description: 'Erro interno do servidor')
        ]
    )]
    public function update(Request $req, ValidatorInterface $validator): JsonResponse
    {
        try {
            $id = (int)$req->attributes->get('id');
            $payload = json_decode($req->getContent() ?? '{}', true) ?: [];
            $useCase = new UpdateCategoryUseCase($this->categoryRepository);

            $dto = new UpdateCategoryDto();

            $dto->id = $id;
            $dto->name = $payload['name'] ?? '';
            $dto->description = $payload['description'] ?? null;

            $errors = $validator->validate($dto);

            if (count($errors) > 0) {
                return $this->json(['errors' => (string)$errors], 400);
            }

            $product = $useCase->execute($dto);

            return ApiResponse::success(CategoryPresenter::toResponse($product));
        } catch (NotFoundHttpException|ConflictHttpException $e) {
            return ApiResponse::error($e->getMessage());
        } catch (\Throwable $e) {
            return ApiResponse::error('  Internal server error: ' . $e->getMessage());
        }
    }

    #[Route('/{id}', methods: ['DELETE'])]
    #[OA\Delete(
        path: '/api/categories/{id}',
        summary: 'Remove uma categoria',
        tags: ['Categories'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID da categoria',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Categoria removida com sucesso',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'success'),
                        new OA\Property(property: 'code', type: 'integer', example: 200),
                        new OA\Property(property: 'message', type: 'string', example: 'Category deleted successfully'),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: 409,
                description: 'Conflito: existem produtos associados à categoria',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'error'),
                        new OA\Property(property: 'code', type: 'integer', example: 409),
                        new OA\Property(property: 'message', type: 'string', example: 'Cannot delete category because it is associated with existing products.'),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(response: 404, description: 'Categoria não encontrada'),
            new OA\Response(response: 500, description: 'Erro interno do servidor')
        ]
    )]
    public function delete(Request $req): JsonResponse
    {
        try {
            $id = (int)$req->attributes->get('id');
            $useCase = new DeleteCategorytUseCase($this->categoryRepository);

            $useCase->execute($id);

            return ApiResponse::success(message: 'Category deleted successfully');
        } catch (ForeignKeyConstraintViolationException) {
            return ApiResponse::error(
                message: 'Cannot delete category because it is associated with existing products.',
                code: 409
            );
        } catch (NotFoundHttpException $e) {
            return ApiResponse::error(
                message: $e->getMessage(),
                code: 404
            );
        } catch (\Throwable $e) {
            return ApiResponse::error('  Internal server error: ' . $e->getMessage());
        }
    }
}
