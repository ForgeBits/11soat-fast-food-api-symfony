<?php

namespace App\Application\Controller\Products;

use App\Application\Domain\Dtos\Commons\PaginationQueryDto;
use App\Application\Domain\Dtos\Products\CreateProductDto;
use App\Application\Domain\Dtos\Products\UpdateProductDto;
use App\Application\Domain\Entities\Products\Entity\Product;
use App\Application\Helpers\ApiResponse;
use App\Application\Port\Output\Repositories\CategoryRepositoryPort;
use App\Application\Port\Output\Repositories\ProductRepositoryPort;
use App\Application\Presenters\Commons\PaginatorPresenter;
use App\Application\Presenters\Products\ProductPresenter;
use App\Application\UseCases\Products\CreateProductUseCase;
use App\Application\UseCases\Products\DeleteProductUseCase;
use App\Application\UseCases\Products\FindAllProductsUseCase;
use App\Application\UseCases\Products\FindProductUseCase;
use App\Application\UseCases\Products\UpdateProductUseCase;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/products')]
class ProductController extends AbstractController
{
    public function __construct(
        private readonly ProductRepositoryPort $productRepository,
        private readonly CategoryRepositoryPort $categoryRepository,
    ) {

    }

    #[Route('', methods: ['POST'])]
    #[OA\Post(
        path: '/api/products',
        summary: 'Cria um novo produto',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    required: ['name', 'amount', 'url_img'],
                    properties: [
                        new OA\Property(property: 'name', type: 'string', example: 'X-Burger'),
                        new OA\Property(property: 'description', type: 'string', example: 'Hambúrguer com queijo', nullable: true),
                        new OA\Property(property: 'amount', type: 'number', format: 'float', example: 29.9),
                        new OA\Property(property: 'url_img', type: 'string', example: 'https://cdn.example.com/produtos/x-burger.jpg'),
                        new OA\Property(property: 'customizable', type: 'boolean', example: true),
                        new OA\Property(property: 'available', type: 'boolean', example: true),
                        new OA\Property(property: 'category_id', type: 'integer', example: 3, nullable: true)
                    ],
                    type: 'object'
                )
            )
        ),
        tags: ['Products'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Produto criado com sucesso',
                content: new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(
                        properties: [
                            new OA\Property(property: 'success', type: 'boolean', example: true),
                            new OA\Property(
                                property: 'data',
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 101),
                                    new OA\Property(property: 'name', type: 'string', example: 'X-Burger'),
                                    new OA\Property(property: 'description', type: 'string', example: 'Hambúrguer com queijo', nullable: true),
                                    new OA\Property(property: 'amount', type: 'number', format: 'float', example: 29.9),
                                    new OA\Property(property: 'url_img', type: 'string', example: 'https://cdn.example.com/produtos/x-burger.jpg'),
                                    new OA\Property(property: 'customizable', type: 'boolean', example: true),
                                    new OA\Property(property: 'available', type: 'boolean', example: true),
                                    new OA\Property(
                                        property: 'product_items',
                                        type: 'array',
                                        items: new OA\Items(
                                            properties: [
                                                new OA\Property(property: 'id', type: 'integer', example: 10),
                                                new OA\Property(property: 'itemId', type: 'integer', example: 2),
                                                new OA\Property(property: 'essential', type: 'boolean', example: true),
                                                new OA\Property(property: 'quantity', type: 'integer', example: 2),
                                                new OA\Property(property: 'customizable', type: 'boolean', example: false),
                                                new OA\Property(
                                                    property: 'item',
                                                    type: 'object',
                                                    properties: [
                                                        new OA\Property(property: 'id', type: 'integer', example: 2),
                                                        new OA\Property(property: 'name', type: 'string', example: 'Batata Frita Média'),
                                                    ]
                                                ),
                                                new OA\Property(property: 'created_at', type: 'string', example: '2025-01-01 12:00:00'),
                                                new OA\Property(property: 'updated_at', type: 'string', example: '2025-01-01 12:00:00'),
                                            ],
                                            type: 'object'
                                        )
                                    ),
                                    new OA\Property(
                                        property: 'category',
                                        properties: [
                                            new OA\Property(property: 'id', type: 'integer', example: 3),
                                            new OA\Property(property: 'name', type: 'string', example: 'Lanches'),
                                            new OA\Property(property: 'description', type: 'string', example: 'Itens do cardápio de lanches', nullable: true),
                                            new OA\Property(property: 'created_at', type: 'string', example: '2025-01-01 12:00:00'),
                                            new OA\Property(property: 'updated_at', type: 'string', example: '2025-01-02 14:30:00'),
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
            new OA\Response(response: 400, description: 'Payload inválido'),
            new OA\Response(response: 500, description: 'Erro interno do servidor')
        ]
    )]
    public function create(Request $req, ValidatorInterface $validator): JsonResponse
    {
        try {
            $payload = json_decode($req->getContent() ?? '{}', true) ?: [];
            $useCase = new CreateProductUseCase($this->productRepository, $this->categoryRepository);

            $dto = new CreateProductDto();
            $dto->name = $payload['name'] ?? '';
            $dto->description = $payload['description'] ?? null;
            $dto->amount = (float)($payload['amount'] ?? 0);
            $dto->url_img = $payload['url_img'] ?? '';
            $dto->customizable = (bool)($payload['customizable'] ?? false);
            $dto->available = (bool)($payload['available'] ?? true);
            $dto->category_id = isset($payload['category_id']) ? (int)$payload['category_id'] : null;

            $errors = $validator->validate($dto);

            if (count($errors) > 0) {
                return $this->json(['errors' => (string)$errors], 400);
            }

            $product = $useCase->execute($dto);

            return ApiResponse::success(ProductPresenter::toResponse($product));
        } catch (NotFoundHttpException|ConflictHttpException $e) {
            return ApiResponse::error($e->getMessage());
        } catch (\Throwable $e) {
            return ApiResponse::error('Internal server error: ' . $e->getMessage());
        }
    }

    #[Route('', methods: ['GET'])]
    #[OA\Get(
        path: '/api/products',
        summary: 'Lista produtos com paginação',
        tags: ['Products'],
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
                description: 'Lista paginada de produtos',
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
                                                new OA\Property(property: 'id', type: 'integer', example: 101),
                                                new OA\Property(property: 'name', type: 'string', example: 'X-Burger'),
                                                new OA\Property(property: 'description', type: 'string', example: 'Hambúrguer com queijo', nullable: true),
                                                new OA\Property(property: 'amount', type: 'number', format: 'float', example: 29.9),
                                                new OA\Property(property: 'url_img', type: 'string', example: 'https://cdn.example.com/produtos/x-burger.jpg'),
                                                new OA\Property(property: 'customizable', type: 'boolean', example: true),
                                                new OA\Property(property: 'available', type: 'boolean', example: true),
                                                new OA\Property(
                                                    property: 'product_items',
                                                    type: 'array',
                                                    items: new OA\Items(
                                                        properties: [
                                                            new OA\Property(property: 'id', type: 'integer', example: 10),
                                                            new OA\Property(property: 'itemId', type: 'integer', example: 2),
                                                            new OA\Property(property: 'essential', type: 'boolean', example: true),
                                                            new OA\Property(property: 'quantity', type: 'integer', example: 2),
                                                            new OA\Property(property: 'customizable', type: 'boolean', example: false),
                                                            new OA\Property(
                                                                property: 'item',
                                                                type: 'object',
                                                                properties: [
                                                                    new OA\Property(property: 'id', type: 'integer', example: 2),
                                                                    new OA\Property(property: 'name', type: 'string', example: 'Batata Frita Média'),
                                                                ]
                                                            ),
                                                            new OA\Property(property: 'created_at', type: 'string', example: '2025-01-01 12:00:00'),
                                                            new OA\Property(property: 'updated_at', type: 'string', example: '2025-01-01 12:00:00'),
                                                        ],
                                                        type: 'object'
                                                    )
                                                ),
                                                new OA\Property(
                                                    property: 'category',
                                                    properties: [
                                                        new OA\Property(property: 'id', type: 'integer', example: 3),
                                                        new OA\Property(property: 'name', type: 'string', example: 'Lanches'),
                                                        new OA\Property(property: 'description', type: 'string', example: 'Itens do cardápio de lanches', nullable: true),
                                                        new OA\Property(property: 'created_at', type: 'string', example: '2025-01-01 12:00:00'),
                                                        new OA\Property(property: 'updated_at', type: 'string', example: '2025-01-02 14:30:00'),
                                                    ],
                                                    type: 'object'
                                                )
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
            $useCase = new FindAllProductsUseCase($this->productRepository);

            $result = $useCase->execute($pagination);

            $items = array_map(function (Product $product) {
                return ProductPresenter::toResponse($product);
            }, $result);

            return ApiResponse::success(PaginatorPresenter::toResponse($pagination, $items));
        } catch (\Throwable $e) {
            return ApiResponse::error('Internal server error: ' . $e->getMessage());
        }
    }

    #[Route('/{id}', methods: ['GET'])]
    #[OA\Get(
        path: '/api/products/{id}',
        summary: 'Obtém um produto pelo ID',
        tags: ['Products'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID do produto',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Produto encontrado com sucesso',
                content: new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(
                        properties: [
                            new OA\Property(property: 'success', type: 'boolean', example: true),
                            new OA\Property(
                                property: 'data',
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 101),
                                    new OA\Property(property: 'name', type: 'string', example: 'X-Burger'),
                                    new OA\Property(property: 'description', type: 'string', example: 'Hambúrguer com queijo', nullable: true),
                                    new OA\Property(property: 'amount', type: 'number', format: 'float', example: 29.9),
                                    new OA\Property(property: 'url_img', type: 'string', example: 'https://cdn.example.com/produtos/x-burger.jpg'),
                                    new OA\Property(property: 'customizable', type: 'boolean', example: true),
                                    new OA\Property(property: 'available', type: 'boolean', example: true),
                                    new OA\Property(
                                        property: 'product_items',
                                        type: 'array',
                                        items: new OA\Items(
                                            properties: [
                                                new OA\Property(property: 'id', type: 'integer', example: 10),
                                                new OA\Property(property: 'itemId', type: 'integer', example: 2),
                                                new OA\Property(property: 'essential', type: 'boolean', example: true),
                                                new OA\Property(property: 'quantity', type: 'integer', example: 2),
                                                new OA\Property(property: 'customizable', type: 'boolean', example: false),
                                                new OA\Property(
                                                    property: 'item',
                                                    type: 'object',
                                                    properties: [
                                                        new OA\Property(property: 'id', type: 'integer', example: 2),
                                                        new OA\Property(property: 'name', type: 'string', example: 'Batata Frita Média'),
                                                    ]
                                                ),
                                                new OA\Property(property: 'created_at', type: 'string', example: '2025-01-01 12:00:00'),
                                                new OA\Property(property: 'updated_at', type: 'string', example: '2025-01-01 12:00:00'),
                                            ],
                                            type: 'object'
                                        )
                                    ),
                                    new OA\Property(
                                        property: 'category',
                                        properties: [
                                            new OA\Property(property: 'id', type: 'integer', example: 3),
                                            new OA\Property(property: 'name', type: 'string', example: 'Lanches'),
                                            new OA\Property(property: 'description', type: 'string', example: 'Itens do cardápio de lanches', nullable: true),
                                            new OA\Property(property: 'created_at', type: 'string', example: '2025-01-01 12:00:00'),
                                            new OA\Property(property: 'updated_at', type: 'string', example: '2025-01-02 14:30:00'),
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
            new OA\Response(response: 404, description: 'Produto não encontrado'),
            new OA\Response(response: 500, description: 'Erro interno do servidor')
        ]
    )]
    public function find(Request $req): JsonResponse
    {
        try {
            $id = (int)$req->attributes->get('id');
            $useCase = new FindProductUseCase($this->productRepository);

            $product = $useCase->execute($id);

            return ApiResponse::success(ProductPresenter::toResponse($product));
        } catch (NotFoundHttpException $e) {
            return ApiResponse::error(
                message: $e->getMessage(),
                code: 404
            );
        } catch (\Throwable $e) {
            return ApiResponse::error('Internal server error: ' . $e->getMessage());
        }
    }

    #[Route('/{id}', methods: ['PATCH'])]
    #[OA\Patch(
        path: '/api/products/{id}',
        summary: 'Atualiza um produto existente',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'name', type: 'string', example: 'X-Burger (atualizado)', nullable: true),
                        new OA\Property(property: 'description', type: 'string', example: 'Hambúrguer com queijo e bacon', nullable: true),
                        new OA\Property(property: 'amount', type: 'number', format: 'float', example: 31.9, nullable: true),
                        new OA\Property(property: 'url_img', type: 'string', example: 'https://cdn.example.com/produtos/x-burger-v2.jpg', nullable: true),
                        new OA\Property(property: 'customizable', type: 'boolean', example: true, nullable: true),
                        new OA\Property(property: 'available', type: 'boolean', example: false, nullable: true),
                        new OA\Property(property: 'category_id', type: 'integer', example: 4, nullable: true)
                    ],
                    type: 'object'
                )
            )
        ),
        tags: ['Products'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID do produto',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Produto atualizado com sucesso',
                content: new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(
                        properties: [
                            new OA\Property(property: 'success', type: 'boolean', example: true),
                            new OA\Property(
                                property: 'data',
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 101),
                                    new OA\Property(property: 'name', type: 'string', example: 'X-Burger (atualizado)'),
                                    new OA\Property(property: 'description', type: 'string', example: 'Hambúrguer com queijo e bacon', nullable: true),
                                    new OA\Property(property: 'amount', type: 'number', format: 'float', example: 31.9),
                                    new OA\Property(property: 'url_img', type: 'string', example: 'https://cdn.example.com/produtos/x-burger-v2.jpg'),
                                    new OA\Property(property: 'customizable', type: 'boolean', example: true),
                                    new OA\Property(property: 'available', type: 'boolean', example: false),
                                    new OA\Property(
                                        property: 'category',
                                        properties: [
                                            new OA\Property(property: 'id', type: 'integer', example: 4),
                                            new OA\Property(property: 'name', type: 'string', example: 'Bebidas'),
                                            new OA\Property(property: 'description', type: 'string', example: 'Refrigerantes, sucos e outras bebidas', nullable: true),
                                            new OA\Property(property: 'created_at', type: 'string', example: '2025-01-03 08:00:00'),
                                            new OA\Property(property: 'updated_at', type: 'string', example: '2025-01-05 09:15:00'),
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
            new OA\Response(response: 400, description: 'Payload inválido'),
            new OA\Response(response: 404, description: 'Produto não encontrado'),
            new OA\Response(response: 500, description: 'Erro interno do servidor')
        ]
    )]
    public function update(Request $req, ValidatorInterface $validator): JsonResponse
    {
        try {
            $id = (int)$req->attributes->get('id');
            $payload = json_decode($req->getContent() ?? '{}', true) ?: [];
            $useCase = new UpdateProductUseCase($this->productRepository, $this->categoryRepository);

            $dto = new UpdateProductDto();

            $dto->id = $id;
            $dto->name = $payload['name'] ?? '';
            $dto->description = $payload['description'] ?? null;
            $dto->amount = (float)($payload['amount'] ?? 0);
            $dto->url_img = $payload['url_img'] ?? '';
            $dto->customizable = (bool)($payload['customizable'] ?? false);
            $dto->available = (bool)($payload['available'] ?? true);
            $dto->category_id = isset($payload['category_id']) ? (int)$payload['category_id'] : null;

            $errors = $validator->validate($dto);

            if (count($errors) > 0) {
                return $this->json(['errors' => (string)$errors], 400);
            }

            $product = $useCase->execute($dto);

            return ApiResponse::success(ProductPresenter::toResponse($product));
        } catch (NotFoundHttpException|ConflictHttpException $e) {
            return ApiResponse::error(
                message: $e->getMessage(),
                code: $e->getCode()
            );
        } catch (\Throwable $e) {
            dd($e);
            return ApiResponse::error('Internal server error: ' . $e->getMessage());
        }
    }

    #[Route('/{id}', methods: ['DELETE'])]
    #[OA\Delete(
        path: '/api/products/{id}',
        summary: 'Remove um produto pelo ID',
        tags: ['Products'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID do produto',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Produto removido com sucesso',
                content: new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(
                        properties: [
                            new OA\Property(property: 'success', type: 'boolean', example: true),
                            new OA\Property(property: 'message', type: 'string', example: 'Product deleted successfully')
                        ],
                        type: 'object'
                    )
                )
            ),
            new OA\Response(response: 404, description: 'Produto não encontrado'),
            new OA\Response(response: 500, description: 'Erro interno do servidor')
        ]
    )]
    public function delete(Request $req): JsonResponse
    {
        try {
            $id = (int)$req->attributes->get('id');
            $useCase = new DeleteProductUseCase($this->productRepository);

            $useCase->execute($id);

            return ApiResponse::success(message: 'Product deleted successfully');
        } catch (\Throwable $e) {
            return ApiResponse::error('Internal server error: ' . $e->getMessage());
        }
    }
}
