<?php

namespace App\Application\Controller\Categories;

use App\Application\Helpers\ApiResponse;
use App\Application\Port\Output\Repositories\CategoryRepositoryPort;
use App\Application\Presenters\Category\CategoryPresenter;
use App\Application\UseCases\Categories\CreateCategoryUseCase;
use App\Domain\Categories\DTO\CreateCategoryDto;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use OpenApi\Attributes as OA;

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
                        new OA\Property(property: 'message', type: 'string', example: 'Internal server error: ...'),
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
            return ApiResponse::error('Internal server error: ' . $e->getMessage());
        }
    }
}
