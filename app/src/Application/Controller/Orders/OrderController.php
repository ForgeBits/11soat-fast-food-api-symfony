<?php

namespace App\Application\Controller\Orders;

use App\Application\Domain\Dtos\Orders\CreateOrderDto;
use App\Application\Domain\Dtos\Orders\CreateOrderItemDto;
use App\Application\Domain\Dtos\Orders\CreateOrderItemCustomizationDto;
use App\Application\Domain\Dtos\Commons\PaginationQueryDto;
use App\Application\Domain\Entities\Orders\Enum\OrderStatus;
use App\Application\Helpers\ApiResponse;
use App\Application\Port\Output\Repositories\ItemRepositoryPort;
use App\Application\Port\Output\Repositories\OrderRepositoryPort;
use App\Application\Port\Output\Repositories\ProductItemRepositoryPort;
use App\Application\Port\Output\Repositories\ProductRepositoryPort;
use App\Application\Presenters\Commons\PaginatorPresenter;
use App\Application\Presenters\Orders\OrderPresenter;
use App\Application\UseCases\Orders\CreateOrderUseCase;
use App\Application\UseCases\Orders\FindAllOrdersUseCase;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/orders')]
class OrderController extends AbstractController
{
    public function __construct(
        private readonly OrderRepositoryPort $orderRepository,
        private readonly ProductRepositoryPort $productRepository,
        private readonly ItemRepositoryPort $itemRepository,
        private readonly ProductItemRepositoryPort $productItemRepository,
    ) {
    }

    #[Route('', methods: ['POST'])]
    #[OA\Post(
        path: '/api/orders',
        summary: 'Cria um novo pedido com itens e customizações',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    required: ['amount', 'items'],
                    properties: [
                        new OA\Property(property: 'clientId', type: 'integer', example: 123, nullable: true),
                        new OA\Property(property: 'codeClientRandom', type: 'integer', example: 456, nullable: true),
                        new OA\Property(property: 'isRandomClient', type: 'boolean', example: true),
                        new OA\Property(property: 'amount', type: 'number', format: 'float', example: 59.8),
                        new OA\Property(property: 'observation', type: 'string', example: 'Sem cebola', nullable: true),
                        new OA\Property(property: 'items', type: 'array', items: new OA\Items(
                            properties: [
                                new OA\Property(property: 'productId', type: 'integer', example: 1),
                                new OA\Property(property: 'title', type: 'string', example: 'X-Bacon'),
                                new OA\Property(property: 'quantity', type: 'integer', example: 1),
                                new OA\Property(property: 'price', type: 'number', format: 'float', example: 29.9),
                                new OA\Property(property: 'observation', type: 'string', example: 'Bem passado', nullable: true),
                                new OA\Property(property: 'customerItems', type: 'array', items: new OA\Items(
                                    properties: [
                                        new OA\Property(property: 'itemId', type: 'integer', example: 2),
                                        new OA\Property(property: 'title', type: 'string', example: 'Alface extra'),
                                        new OA\Property(property: 'quantity', type: 'integer', example: 1),
                                        new OA\Property(property: 'price', type: 'number', format: 'float', example: 2.5),
                                        new OA\Property(property: 'observation', type: 'string', example: 'sem talo', nullable: true),
                                    ], type: 'object'
                                )),
                            ], type: 'object'
                        )),
                    ],
                    type: 'object'
                )
            )
        ),
        tags: ['Orders'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Pedido criado com sucesso',
                content: new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(
                        properties: [
                            new OA\Property(property: 'success', type: 'boolean', example: true),
                            new OA\Property(property: 'data', type: 'object')
                        ]
                    )
                )
            ),
            new OA\Response(response: 400, description: 'Payload inválido'),
            new OA\Response(response: 404, description: 'Produto/Item não encontrado'),
            new OA\Response(response: 500, description: 'Erro interno do servidor'),
        ]
    )]
    public function create(Request $req, ValidatorInterface $validator): JsonResponse
    {
        try {
            $payload = json_decode($req->getContent() ?? '{}', true) ?: [];

            $dto = new CreateOrderDto();
            $dto->clientId = isset($payload['clientId']) ? (int)$payload['clientId'] : null;
            $dto->status = OrderStatus::PENDING; // status inicial
            $dto->amount = (float)($payload['amount'] ?? 0);
            $dto->transactionId = $payload['transactionId'] ?? null;
            $dto->isRandomClient = (bool)($payload['isRandomClient'] ?? false);
            $dto->codeClientRandom = isset($payload['codeClientRandom']) ? (int)$payload['codeClientRandom'] : null;
            $dto->observation = $payload['observation'] ?? null;

            $dto->productIds = []; // legado não usado neste fluxo

            // map items
            $dto->items = [];
            foreach (($payload['items'] ?? []) as $it) {
                $itDto = new CreateOrderItemDto();
                $itDto->productId = (int)($it['productId'] ?? 0);
                $itDto->title = (string)($it['title'] ?? '');
                $itDto->quantity = (int)($it['quantity'] ?? 0);
                $itDto->price = (float)($it['price'] ?? 0);
                $itDto->observation = $it['observation'] ?? null;

                $itDto->customerItems = [];
                foreach (($it['customerItems'] ?? []) as $ci) {
                    $ciDto = new CreateOrderItemCustomizationDto();
                    $ciDto->itemId = (int)($ci['itemId'] ?? 0);
                    $ciDto->title = (string)($ci['title'] ?? '');
                    $ciDto->quantity = (int)($ci['quantity'] ?? 0);
                    $ciDto->price = (float)($ci['price'] ?? 0);
                    $ciDto->observation = $ci['observation'] ?? null;
                    $itDto->customerItems[] = $ciDto;
                }

                $dto->items[] = $itDto;
            }

            $errors = $validator->validate($dto);
            if (count($errors) > 0) {
                return $this->json(['errors' => (string)$errors], 400);
            }

            $useCase = new CreateOrderUseCase(
                $this->orderRepository,
                $this->productRepository,
                $this->itemRepository,
                $this->productItemRepository,
            );

            $order = $useCase->execute($dto);
            return ApiResponse::success(OrderPresenter::toResponse($order));
        } catch (HttpExceptionInterface $e) {
            return ApiResponse::error(message: $e->getMessage(), code: $e->getStatusCode());
        } catch (\Throwable $e) {
            return ApiResponse::error('Internal server error: ' . $e->getMessage());
        }
    }

    #[Route('', methods: ['GET'])]
    #[OA\Get(
        path: '/api/orders',
        summary: 'Lista pedidos com paginação',
        tags: ['Orders'],
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
                description: 'Lista paginada de pedidos',
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
                                                new OA\Property(property: 'id', type: 'integer', example: 5001),
                                                new OA\Property(property: 'client_id', type: 'integer', example: 123, nullable: true),
                                                new OA\Property(property: 'status', type: 'string', example: 'PENDING'),
                                                new OA\Property(property: 'amount', type: 'number', format: 'float', example: 59.8),
                                                new OA\Property(property: 'transaction_id', type: 'string', example: 'f9a2d0e4-2a7c-4f56-9f21-0f5b3c1d2e4a', nullable: true),
                                                new OA\Property(property: 'is_random_client', type: 'boolean', example: true),
                                                new OA\Property(property: 'code_client_random', type: 'integer', example: 456, nullable: true),
                                                new OA\Property(property: 'observation', type: 'string', example: 'Sem cebola', nullable: true),
                                                new OA\Property(
                                                    property: 'items',
                                                    type: 'array',
                                                    items: new OA\Items(
                                                        properties: [
                                                            new OA\Property(property: 'id', type: 'integer', example: 9001),
                                                            new OA\Property(property: 'productId', type: 'integer', example: 1),
                                                            new OA\Property(property: 'title', type: 'string', example: 'X-Bacon'),
                                                            new OA\Property(property: 'quantity', type: 'integer', example: 1),
                                                            new OA\Property(property: 'price', type: 'number', format: 'float', example: 29.9),
                                                            new OA\Property(property: 'observation', type: 'string', example: 'Bem passado', nullable: true),
                                                            new OA\Property(
                                                                property: 'customerItems',
                                                                type: 'array',
                                                                items: new OA\Items(
                                                                    properties: [
                                                                        new OA\Property(property: 'id', type: 'integer', example: 9101),
                                                                        new OA\Property(property: 'itemId', type: 'integer', example: 2),
                                                                        new OA\Property(property: 'title', type: 'string', example: 'Alface extra'),
                                                                        new OA\Property(property: 'quantity', type: 'integer', example: 1),
                                                                        new OA\Property(property: 'price', type: 'number', format: 'float', example: 2.5),
                                                                        new OA\Property(property: 'observation', type: 'string', example: 'sem talo', nullable: true),
                                                                        new OA\Property(property: 'created_at', type: 'string', example: '2025-01-01 12:00:00'),
                                                                        new OA\Property(property: 'updated_at', type: 'string', example: '2025-01-01 12:00:00'),
                                                                    ], type: 'object'
                                                                )
                                                            ),
                                                            new OA\Property(property: 'created_at', type: 'string', example: '2025-01-01 12:00:00'),
                                                            new OA\Property(property: 'updated_at', type: 'string', example: '2025-01-01 12:00:00'),
                                                        ], type: 'object'
                                                    )
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
            $useCase = new FindAllOrdersUseCase($this->orderRepository);

            $result = $useCase->execute($pagination);

            $items = array_map(function ($order) {
                return OrderPresenter::toResponse($order);
            }, $result);

            return ApiResponse::success(PaginatorPresenter::toResponse($pagination, $items));
        } catch (\Throwable $e) {
            return ApiResponse::error('Internal server error: ' . $e->getMessage());
        }
    }
}
