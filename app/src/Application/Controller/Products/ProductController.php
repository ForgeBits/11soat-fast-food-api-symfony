<?php

namespace App\Application\Controller\Products;

use App\Application\Domain\Dtos\Products\CreateProductDto;
use App\Application\Domain\Dtos\Products\UpdateProductDto;
use App\Application\Helpers\ApiResponse;
use App\Application\Port\Output\Repositories\CategoryRepositoryPort;
use App\Application\Port\Output\Repositories\ProductRepositoryPort;
use App\Application\Presenters\Products\ProductPresenter;
use App\Application\UseCases\Products\CreateProductUseCase;
use App\Application\UseCases\Products\UpdateProductUseCase;
use Doctrine\ORM\Tools\Pagination\Paginator;
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
                                    new OA\Property(property: 'category_id', type: 'integer', example: 3, nullable: true)
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
    public function findAll(Request $req): JsonResponse
    {
        try {
            /*$page = max(1, (int) $req->query->get('page', 1));
            $limit = (int) $req->query->get('limit', 10);
            $limit = min(max($limit, 1), 100);
            $offset = ($page - 1) * $limit;*/

            $qb = $this->productRepository->createPaginatedQueryBuilder(); // returns QueryBuilder
            $qb->setFirstResult($offset)->setMaxResults($limit);

            $paginator = new Paginator($qb->getQuery(), true);
            $total = count($paginator);

            $items = [];
            foreach ($paginator as $product) {
                $items[] = ProductPresenter::toResponse($product);
            }

            return ApiResponse::success([
                'items' => $items,
                'meta' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'pages' => (int) ceil($total / $limit),
                ],
            ]);
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
                                    new OA\Property(property: 'category_id', type: 'integer', example: 4, nullable: true)
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
            return ApiResponse::error($e->getMessage());
        } catch (\Throwable $e) {
            return ApiResponse::error('Internal server error: ' . $e->getMessage());
        }
    }
}
