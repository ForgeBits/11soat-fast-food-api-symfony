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

    #[Route('', methods: ['PATCH'])]
    public function update(Request $req): JsonResponse
    {
        return $this->json(['message' => 'Not implemented'], 501);
    }
}
