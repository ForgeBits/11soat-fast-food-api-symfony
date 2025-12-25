<?php

namespace App\Application\Controller\Products;

use App\Application\Helpers\ApiResponse;
use App\Application\Port\Output\Repositories\ProductRepositoryPort;
use App\Application\UseCases\Products\CreateProductUseCase;
use App\Domain\Products\DTO\CreateProductDto;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/products')]
class ProductController extends AbstractController
{
    public function __construct(
        private readonly ProductRepositoryPort $productRepository,
    ) {

    }

    #[Route('', methods: ['POST'])]
    public function create(Request $req, ValidatorInterface $validator): JsonResponse
    {
        try {
            $payload = json_decode($req->getContent() ?? '{}', true) ?: [];
            $useCase = new CreateProductUseCase($this->productRepository);

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

            return ApiResponse::success($product->toArray());
        } catch (\Throwable $e) {
            return ApiResponse::error('Internal server error: ' . $e->getMessage());
        }
    }

    #[Route('', methods: ['PATCH'])]
    public function update(Request $req): JsonResponse
    {

    }
}
