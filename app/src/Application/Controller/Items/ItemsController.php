<?php

namespace App\Application\Controller\Items;

use App\Application\Domain\Dtos\Items\CreateItemDto;
use App\Application\Helpers\ApiResponse;
use App\Application\Port\Output\Repositories\ItemRepositoryPort;
use App\Application\Presenters\Item\ItemPresenter;
use App\Application\UseCases\Items\CreateItemUseCase;
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
}
