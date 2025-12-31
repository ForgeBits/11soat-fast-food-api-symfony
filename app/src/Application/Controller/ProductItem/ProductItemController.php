<?php

namespace App\Application\Controller\ProductItem;

use App\Application\Domain\Dtos\ProductItem\CreateProductItemDto;
use App\Application\Helpers\ApiResponse;
use App\Application\Port\Output\Repositories\ItemRepositoryPort;
use App\Application\Port\Output\Repositories\ProductItemRepositoryPort;
use App\Application\Port\Output\Repositories\ProductRepositoryPort;
use App\Application\Presenters\ProductItem\ProductItemPresenter;
use App\Application\UseCases\ProductItem\CreateProductItemUseCase;
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
}
