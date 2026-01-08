<?php

namespace App\Application\Controller\Items;

use App\Application\Domain\Dtos\Commons\PaginationQueryDto;
use App\Application\Domain\Dtos\Items\CreateItemDto;
use App\Application\Domain\Entities\Items\Entity\Item;
use App\Application\Helpers\ApiResponse;
use App\Application\Port\Output\Repositories\ItemRepositoryPort;
use App\Application\Presenters\Commons\PaginatorPresenter;
use App\Application\Presenters\Item\ItemPresenter;
use App\Application\UseCases\Items\CreateItemUseCase;
use App\Application\UseCases\Items\FindAllItemsUseCase;
use App\Application\UseCases\Items\FindItemUseCase;
use App\Application\UseCases\Items\UpdateItemUseCase;
use App\Application\Domain\Dtos\Items\UpdateItemDto;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/items')]
class ItemsController extends AbstractController
{
    public function __construct(
        private readonly ItemRepositoryPort $itemRepository,
    ) {

    }

    #[Route('', methods: ['POST'])]
    #[OA\Post(
        path: '/api/items',
        summary: 'Cria um novo item do cardápio',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    required: ['name', 'price', 'url_img'],
                    properties: [
                        new OA\Property(property: 'name', type: 'string', example: 'Batata Frita Média'),
                        new OA\Property(property: 'description', type: 'string', example: 'Porção de batata frita crocante', nullable: true),
                        new OA\Property(property: 'price', type: 'number', format: 'float', example: 14.9),
                        new OA\Property(property: 'url_img', type: 'string', example: 'https://cdn.example.com/itens/batata-media.jpg'),
                        new OA\Property(property: 'available', type: 'boolean', example: true)
                    ],
                    type: 'object'
                )
            )
        ),
        tags: ['Items'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Item criado com sucesso',
                content: new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(
                        properties: [
                            new OA\Property(property: 'success', type: 'boolean', example: true),
                            new OA\Property(
                                property: 'data',
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 101),
                                    new OA\Property(property: 'name', type: 'string', example: 'Batata Frita Média'),
                                    new OA\Property(property: 'description', type: 'string', example: 'Porção de batata frita crocante', nullable: true),
                                    new OA\Property(property: 'price', type: 'number', format: 'float', example: 14.9),
                                    new OA\Property(property: 'url_img', type: 'string', example: 'https://cdn.example.com/itens/batata-media.jpg'),
                                    new OA\Property(property: 'available', type: 'boolean', example: true),
                                    new OA\Property(property: 'created_at', type: 'string', example: '2025-01-01 12:00:00'),
                                    new OA\Property(property: 'updated_at', type: 'string', example: '2025-01-02 14:30:00'),
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
            $useCase = new CreateItemUseCase($this->itemRepository);

            $dto = new CreateItemDto();
            $dto->name = $payload['name'] ?? '';
            $dto->description = $payload['description'] ?? null;
            $dto->price = (float)($payload['price'] ?? 0);
            $dto->url_img = $payload['url_img'] ?? '';
            $dto->available = (bool)($payload['available'] ?? true);

            $errors = $validator->validate($dto);

            if (count($errors) > 0) {
                return $this->json(['errors' => (string)$errors], 400);
            }

            $item = $useCase->execute($dto);

            return ApiResponse::success(ItemPresenter::toResponse($item));
        } catch (NotFoundHttpException|ConflictHttpException $e) {
            return ApiResponse::error(
                message: $e->getMessage(),
                code: $e->getCode()
            );
        } catch (\Throwable $e) {
            return ApiResponse::error('Internal server error: ' . $e->getMessage());
        }
    }

    #[Route('', methods: ['GET'])]
    #[OA\Get(
        path: '/api/items',
        summary: 'Lista itens com paginação',
        tags: ['Items'],
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
                description: 'Lista paginada de itens',
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
                                                new OA\Property(property: 'name', type: 'string', example: 'Batata Frita Média'),
                                                new OA\Property(property: 'description', type: 'string', example: 'Porção de batata frita crocante', nullable: true),
                                                new OA\Property(property: 'price', type: 'number', format: 'float', example: 14.9),
                                                new OA\Property(property: 'url_img', type: 'string', example: 'https://cdn.example.com/itens/batata-media.jpg'),
                                                new OA\Property(property: 'available', type: 'boolean', example: true),
                                                new OA\Property(property: 'created_at', type: 'string', example: '2025-01-01 12:00:00'),
                                                new OA\Property(property: 'updated_at', type: 'string', example: '2025-01-02 14:30:00'),
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
            $useCase = new FindAllItemsUseCase($this->itemRepository);

            $result = $useCase->execute($pagination);

            $items = array_map(function (Item $item) {
                return ItemPresenter::toResponse($item);
            }, $result);

            return ApiResponse::success(PaginatorPresenter::toResponse($pagination, $items));
        } catch (\Throwable $e) {
            return ApiResponse::error('Internal server error: ' . $e->getMessage());
        }
    }

    #[Route('/{id}', methods: ['GET'])]
    #[OA\Get(
        path: '/api/items/{id}',
        summary: 'Obtém um item pelo ID',
        tags: ['Items'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID do item',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Item encontrado com sucesso',
                content: new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(
                        properties: [
                            new OA\Property(property: 'success', type: 'boolean', example: true),
                            new OA\Property(
                                property: 'data',
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 101),
                                    new OA\Property(property: 'name', type: 'string', example: 'Batata Frita Média'),
                                    new OA\Property(property: 'description', type: 'string', example: 'Porção de batata frita crocante', nullable: true),
                                    new OA\Property(property: 'price', type: 'number', format: 'float', example: 14.9),
                                    new OA\Property(property: 'url_img', type: 'string', example: 'https://cdn.example.com/itens/batata-media.jpg'),
                                    new OA\Property(property: 'available', type: 'boolean', example: true),
                                    new OA\Property(property: 'created_at', type: 'string', example: '2025-01-01 12:00:00'),
                                    new OA\Property(property: 'updated_at', type: 'string', example: '2025-01-02 14:30:00'),
                                ],
                                type: 'object'
                            )
                        ],
                        type: 'object'
                    )
                )
            ),
            new OA\Response(response: 404, description: 'Item não encontrado'),
            new OA\Response(response: 500, description: 'Erro interno do servidor')
        ]
    )]
    public function find(Request $req): JsonResponse
    {
        try {
            $id = (int)$req->attributes->get('id');
            $useCase = new FindItemUseCase($this->itemRepository);

            $item = $useCase->execute($id);

            return ApiResponse::success(ItemPresenter::toResponse($item));
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
        path: '/api/items/{id}',
        summary: 'Atualiza um item do cardápio',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'name', type: 'string', example: 'Batata Frita Média (atualizada)', nullable: true),
                        new OA\Property(property: 'description', type: 'string', example: 'Porção de batata frita crocante', nullable: true),
                        new OA\Property(property: 'price', type: 'number', format: 'float', example: 15.9, nullable: true),
                        new OA\Property(property: 'url_img', type: 'string', example: 'https://cdn.example.com/itens/batata-media-v2.jpg', nullable: true),
                        new OA\Property(property: 'available', type: 'boolean', example: false, nullable: true)
                    ],
                    type: 'object'
                )
            )
        ),
        tags: ['Items'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID do item',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Item atualizado com sucesso',
                content: new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(
                        properties: [
                            new OA\Property(property: 'success', type: 'boolean', example: true),
                            new OA\Property(
                                property: 'data',
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 101),
                                    new OA\Property(property: 'name', type: 'string', example: 'Batata Frita Média (atualizada)'),
                                    new OA\Property(property: 'description', type: 'string', example: 'Porção de batata frita crocante', nullable: true),
                                    new OA\Property(property: 'price', type: 'number', format: 'float', example: 15.9),
                                    new OA\Property(property: 'url_img', type: 'string', example: 'https://cdn.example.com/itens/batata-media-v2.jpg'),
                                    new OA\Property(property: 'available', type: 'boolean', example: false),
                                    new OA\Property(property: 'created_at', type: 'string', example: '2025-01-01 12:00:00'),
                                    new OA\Property(property: 'updated_at', type: 'string', example: '2025-01-02 14:30:00'),
                                ],
                                type: 'object'
                            )
                        ],
                        type: 'object'
                    )
                )
            ),
            new OA\Response(response: 400, description: 'Payload inválido'),
            new OA\Response(response: 404, description: 'Item não encontrado'),
            new OA\Response(response: 500, description: 'Erro interno do servidor')
        ]
    )]
    public function update(Request $req, ValidatorInterface $validator): JsonResponse
    {
        try {
            $id = (int)$req->attributes->get('id');
            $payload = json_decode($req->getContent() ?? '{}', true) ?: [];
            $useCase = new UpdateItemUseCase($this->itemRepository);

            $dto = new UpdateItemDto();

            $dto->id = $id;
            $dto->name = $payload['name'] ?? '';
            $dto->description = $payload['description'] ?? null;
            $dto->price = (float)($payload['price'] ?? 0);
            $dto->url_img = $payload['url_img'] ?? '';
            $dto->available = (bool)($payload['available'] ?? true);

            $errors = $validator->validate($dto);

            if (count($errors) > 0) {
                return $this->json(['errors' => (string)$errors], 400);
            }

            $item = $useCase->execute($dto);

            return ApiResponse::success(ItemPresenter::toResponse($item));
        } catch (NotFoundHttpException|ConflictHttpException $e) {
            return ApiResponse::error(
                message: $e->getMessage(),
                code: $e->getCode()
            );
        } catch (\Throwable $e) {
            return ApiResponse::error('Internal server error: ' . $e->getMessage());
        }
    }
}
