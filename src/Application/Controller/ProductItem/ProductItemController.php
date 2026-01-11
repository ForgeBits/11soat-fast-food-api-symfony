<?php

namespace App\Application\Controller\ProductItem;

use App\Application\Domain\Dtos\Commons\PaginationQueryDto;
use App\Application\Domain\Dtos\ProductItem\CreateProductItemDto;
use App\Application\Domain\Dtos\ProductItem\UpdateProductItemDto;
use App\Application\Helpers\ApiResponse;
use App\Application\Port\Output\Repositories\ItemRepositoryPort;
use App\Application\Port\Output\Repositories\ProductItemRepositoryPort;
use App\Application\Port\Output\Repositories\ProductRepositoryPort;
use App\Application\Presenters\Commons\PaginatorPresenter;
use App\Application\Presenters\ProductItem\ProductItemPresenter;
use App\Application\UseCases\ProductItem\CreateProductItemUseCase;
use App\Application\UseCases\ProductItem\DeleteProductItemUseCase;
use App\Application\UseCases\ProductItem\FindAllProductItemsUseCase;
use App\Application\UseCases\ProductItem\FindProductItemUseCase;
use App\Application\UseCases\ProductItem\FindProductItemsByProductUseCase;
use App\Application\UseCases\ProductItem\UpdateProductItemUseCase;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/product-items')]
class ProductItemController extends AbstractController
{
    public function __construct(
        private readonly ProductItemRepositoryPort $productItemRepository,
        private readonly ProductRepositoryPort $productRepository,
        private readonly ItemRepositoryPort $itemRepository,
    ) {
    }

    #[Route('', methods: ['POST'])]
    #[OA\Post(
        path: '/api/product-items',
        summary: 'Cria uma relação entre Produto e Item',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    required: ['productId', 'itemId', 'quantity'],
                    properties: [
                        new OA\Property(property: 'productId', type: 'integer', example: 1),
                        new OA\Property(property: 'itemId', type: 'integer', example: 2),
                        new OA\Property(property: 'essential', type: 'boolean', example: true),
                        new OA\Property(property: 'quantity', type: 'integer', example: 2, minimum: 1),
                        new OA\Property(property: 'customizable', type: 'boolean', example: false),
                    ],
                    type: 'object'
                )
            )
        ),
        tags: ['ProductItems'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Relação criada com sucesso',
                content: new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(
                        properties: [
                            new OA\Property(property: 'success', type: 'boolean', example: true),
                            new OA\Property(
                                property: 'data',
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 10),
                                    new OA\Property(property: 'productId', type: 'integer', example: 1),
                                    new OA\Property(property: 'itemId', type: 'integer', example: 2),
                                    new OA\Property(property: 'essential', type: 'boolean', example: true),
                                    new OA\Property(property: 'quantity', type: 'integer', example: 2),
                                    new OA\Property(property: 'customizable', type: 'boolean', example: false),
                                    new OA\Property(
                                        property: 'product',
                                        properties: [
                                            new OA\Property(property: 'id', type: 'integer', example: 1),
                                            new OA\Property(property: 'name', type: 'string', example: 'X-Burger'),
                                        ],
                                        type: 'object'
                                    ),
                                    new OA\Property(
                                        property: 'item',
                                        properties: [
                                            new OA\Property(property: 'id', type: 'integer', example: 2),
                                            new OA\Property(property: 'name', type: 'string', example: 'Batata Frita Média'),
                                        ],
                                        type: 'object'
                                    ),
                                    new OA\Property(property: 'created_at', type: 'string', example: '2025-01-01 12:00:00'),
                                    new OA\Property(property: 'updated_at', type: 'string', example: '2025-01-01 12:00:00'),
                                ],
                                type: 'object'
                            )
                        ],
                        type: 'object'
                    )
                )
            ),
            new OA\Response(response: 400, description: 'Payload inválido'),
            new OA\Response(response: 404, description: 'Produto ou item não encontrado'),
            new OA\Response(response: 409, description: 'Relação já existente'),
            new OA\Response(response: 500, description: 'Erro interno do servidor')
        ]
    )]
    public function create(Request $req, ValidatorInterface $validator): JsonResponse
    {
        try {
            $payload = json_decode($req->getContent() ?? '{}', true) ?: [];

            $dto = new CreateProductItemDto();
            $dto->productId = (int)($payload['productId'] ?? 0);
            $dto->itemId = (int)($payload['itemId'] ?? 0);
            $dto->essential = (bool)($payload['essential'] ?? false);
            $dto->quantity = (int)($payload['quantity'] ?? 0);
            $dto->customizable = (bool)($payload['customizable'] ?? false);

            $errors = $validator->validate($dto);
            if (count($errors) > 0) {
                return $this->json(['errors' => (string)$errors], 400);
            }

            $useCase = new CreateProductItemUseCase(
                $this->productItemRepository,
                $this->productRepository,
                $this->itemRepository,
            );

            $relation = $useCase->execute($dto);

            return ApiResponse::success(ProductItemPresenter::toResponse($relation));
        } catch (NotFoundHttpException|ConflictHttpException $e) {
            return ApiResponse::error(
                message: $e->getMessage(),
                code: $e->getCode() ?: 400
            );
        } catch (\Throwable $e) {
            return ApiResponse::error('Internal server error: ' . $e->getMessage());
        }
    }

    #[Route('', methods: ['GET'])]
    #[OA\Get(
        path: '/api/product-items',
        summary: 'Lista relações Produto-Item com paginação',
        tags: ['ProductItems'],
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
                description: 'Lista paginada de relações',
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
                                                new OA\Property(property: 'id', type: 'integer', example: 10),
                                                new OA\Property(property: 'productId', type: 'integer', example: 1),
                                                new OA\Property(property: 'itemId', type: 'integer', example: 2),
                                                new OA\Property(property: 'essential', type: 'boolean', example: true),
                                                new OA\Property(property: 'quantity', type: 'integer', example: 2),
                                                new OA\Property(property: 'customizable', type: 'boolean', example: false),
                                                new OA\Property(
                                                    property: 'product',
                                                    properties: [
                                                        new OA\Property(property: 'id', type: 'integer', example: 1),
                                                        new OA\Property(property: 'name', type: 'string', example: 'X-Burger'),
                                                    ],
                                                    type: 'object'
                                                ),
                                                new OA\Property(
                                                    property: 'item',
                                                    properties: [
                                                        new OA\Property(property: 'id', type: 'integer', example: 2),
                                                        new OA\Property(property: 'name', type: 'string', example: 'Batata Frita Média'),
                                                    ],
                                                    type: 'object'
                                                ),
                                                new OA\Property(property: 'created_at', type: 'string', example: '2025-01-01 12:00:00'),
                                                new OA\Property(property: 'updated_at', type: 'string', example: '2025-01-01 12:00:00'),
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
            $useCase = new FindAllProductItemsUseCase($this->productItemRepository);
            $result = $useCase->execute($pagination);

            $items = array_map(function ($pi) {
                return ProductItemPresenter::toResponse($pi);
            }, $result);

            return ApiResponse::success(PaginatorPresenter::toResponse($pagination, $items));
        } catch (\Throwable $e) {
            return ApiResponse::error('Internal server error: ' . $e->getMessage());
        }
    }

    #[Route('/{id}', methods: ['GET'])]
    #[OA\Get(
        path: '/api/product-items/{id}',
        summary: 'Obtém uma relação Produto-Item pelo ID',
        tags: ['ProductItems'],
        parameters: [
            new OA\Parameter(name: 'id', description: 'ID da relação', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Relação encontrada com sucesso',
                content: new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(
                        properties: [
                            new OA\Property(property: 'success', type: 'boolean', example: true),
                            new OA\Property(
                                property: 'data',
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 10),
                                    new OA\Property(property: 'productId', type: 'integer', example: 1),
                                    new OA\Property(property: 'itemId', type: 'integer', example: 2),
                                    new OA\Property(property: 'essential', type: 'boolean', example: true),
                                    new OA\Property(property: 'quantity', type: 'integer', example: 2),
                                    new OA\Property(property: 'customizable', type: 'boolean', example: false),
                                    new OA\Property(
                                        property: 'product',
                                        properties: [
                                            new OA\Property(property: 'id', type: 'integer', example: 1),
                                            new OA\Property(property: 'name', type: 'string', example: 'X-Burger'),
                                        ],
                                        type: 'object'
                                    ),
                                    new OA\Property(
                                        property: 'item',
                                        properties: [
                                            new OA\Property(property: 'id', type: 'integer', example: 2),
                                            new OA\Property(property: 'name', type: 'string', example: 'Batata Frita Média'),
                                        ],
                                        type: 'object'
                                    ),
                                    new OA\Property(property: 'created_at', type: 'string', example: '2025-01-01 12:00:00'),
                                    new OA\Property(property: 'updated_at', type: 'string', example: '2025-01-01 12:00:00'),
                                ],
                                type: 'object'
                            )
                        ],
                        type: 'object'
                    )
                )
            ),
            new OA\Response(response: 404, description: 'Relação não encontrada'),
            new OA\Response(response: 500, description: 'Erro interno do servidor'),
        ]
    )]
    public function find(Request $req): JsonResponse
    {
        try {
            $id = (int)$req->attributes->get('id');
            $useCase = new FindProductItemUseCase($this->productItemRepository);
            $entity = $useCase->execute($id);

            return ApiResponse::success(ProductItemPresenter::toResponse($entity));
        } catch (NotFoundHttpException $e) {
            return ApiResponse::error(message: $e->getMessage(), code: 404);
        } catch (\Throwable $e) {
            return ApiResponse::error('Internal server error: ' . $e->getMessage());
        }
    }

    #[Route('/product/{productId}', methods: ['GET'])]
    #[OA\Get(
        path: '/api/product-items/by-product/{productId}',
        summary: 'Lista os itens relacionados de um produto',
        tags: ['ProductItems'],
        parameters: [
            new OA\Parameter(name: 'productId', description: 'ID do produto', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Relações do produto',
                content: new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(
                        properties: [
                            new OA\Property(property: 'success', type: 'boolean', example: true),
                            new OA\Property(
                                property: 'data',
                                type: 'array',
                                items: new OA\Items(
                                    properties: [
                                        new OA\Property(property: 'id', type: 'integer', example: 10),
                                        new OA\Property(property: 'productId', type: 'integer', example: 1),
                                        new OA\Property(property: 'itemId', type: 'integer', example: 2),
                                        new OA\Property(property: 'essential', type: 'boolean', example: true),
                                        new OA\Property(property: 'quantity', type: 'integer', example: 2),
                                        new OA\Property(property: 'customizable', type: 'boolean', example: false),
                                        new OA\Property(
                                            property: 'product',
                                            properties: [
                                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                                new OA\Property(property: 'name', type: 'string', example: 'X-Burger'),
                                            ],
                                            type: 'object'
                                        ),
                                        new OA\Property(
                                            property: 'item',
                                            properties: [
                                                new OA\Property(property: 'id', type: 'integer', example: 2),
                                                new OA\Property(property: 'name', type: 'string', example: 'Batata Frita Média'),
                                            ],
                                            type: 'object'
                                        ),
                                        new OA\Property(property: 'created_at', type: 'string', example: '2025-01-01 12:00:00'),
                                        new OA\Property(property: 'updated_at', type: 'string', example: '2025-01-01 12:00:00'),
                                    ],
                                    type: 'object'
                                )
                            )
                        ],
                        type: 'object'
                    )
                )
            ),
            new OA\Response(response: 404, description: 'Produto não encontrado'),
            new OA\Response(response: 500, description: 'Erro interno do servidor'),
        ]
    )]
    public function findByProduct(Request $req): JsonResponse
    {
        try {
            $productId = (int)$req->attributes->get('productId');
            $useCase = new FindProductItemsByProductUseCase($this->productItemRepository, $this->productRepository);
            $list = $useCase->execute($productId);
            $items = array_map(fn($pi) => ProductItemPresenter::toResponse($pi), $list);
            return ApiResponse::success($items);
        } catch (NotFoundHttpException $e) {
            return ApiResponse::error(message: $e->getMessage(), code: 404);
        } catch (\Throwable $e) {
            return ApiResponse::error('Internal server error: ' . $e->getMessage());
        }
    }

    #[Route('/{id}', methods: ['PATCH'])]
    #[OA\Patch(
        path: '/api/product-items/{id}',
        summary: 'Atualiza parcialmente uma relação Produto-Item (apenas essential, quantity, customizable)',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'essential', type: 'boolean', example: true, nullable: true),
                        new OA\Property(property: 'quantity', type: 'integer', example: 2, nullable: true),
                        new OA\Property(property: 'customizable', type: 'boolean', example: false, nullable: true),
                    ],
                    type: 'object'
                )
            )
        ),
        tags: ['ProductItems'],
        parameters: [
            new OA\Parameter(name: 'id', description: 'ID da relação', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Relação atualizada com sucesso'),
            new OA\Response(response: 400, description: 'Payload inválido'),
            new OA\Response(response: 404, description: 'Relação não encontrada'),
            new OA\Response(response: 500, description: 'Erro interno do servidor'),
        ]
    )]
    public function update(Request $req, ValidatorInterface $validator): JsonResponse
    {
        try {
            $id = (int)$req->attributes->get('id');
            $payload = json_decode($req->getContent() ?? '{}', true) ?: [];

            $dto = new UpdateProductItemDto();
            $dto->id = $id;
            if (array_key_exists('essential', $payload)) {
                $dto->essential = (bool)$payload['essential'];
            }
            if (array_key_exists('quantity', $payload)) {
                $dto->quantity = (int)$payload['quantity'];
            }
            if (array_key_exists('customizable', $payload)) {
                $dto->customizable = (bool)$payload['customizable'];
            }

            $errors = $validator->validate($dto);
            if (count($errors) > 0) {
                return $this->json(['errors' => (string)$errors], 400);
            }

            $useCase = new UpdateProductItemUseCase($this->productItemRepository);
            $updated = $useCase->execute($dto);

            return ApiResponse::success(ProductItemPresenter::toResponse($updated));
        } catch (NotFoundHttpException $e) {
            return ApiResponse::error(message: $e->getMessage(), code: 404);
        } catch (\Throwable $e) {
            return ApiResponse::error('Internal server error: ' . $e->getMessage());
        }
    }

    #[Route('/{id}', methods: ['DELETE'])]
    #[OA\Delete(
        path: '/api/product-items/{id}',
        summary: 'Remove uma relação Produto-Item pelo ID',
        tags: ['ProductItems'],
        parameters: [
            new OA\Parameter(name: 'id', description: 'ID da relação', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Relação removida com sucesso'),
            new OA\Response(response: 404, description: 'Relação não encontrada'),
            new OA\Response(response: 500, description: 'Erro interno do servidor'),
        ]
    )]
    public function delete(Request $req): JsonResponse
    {
        try {
            $id = (int)$req->attributes->get('id');
            $useCase = new DeleteProductItemUseCase($this->productItemRepository);
            $useCase->execute($id);
            return ApiResponse::success(message: 'ProductItem deleted successfully');
        } catch (NotFoundHttpException $e) {
            return ApiResponse::error(message: $e->getMessage(), code: 404);
        } catch (\Throwable $e) {
            return ApiResponse::error('Internal server error: ' . $e->getMessage());
        }
    }
}
