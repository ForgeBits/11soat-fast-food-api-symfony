<?php

namespace App\Application\UseCases\ProductItem;

use App\Application\Port\Output\Repositories\ProductItemRepositoryPort;
use App\Application\Port\Output\Repositories\ProductRepositoryPort;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

readonly class FindProductItemsByProductUseCase
{
    public function __construct(
        private ProductItemRepositoryPort $productItemRepository,
        private ProductRepositoryPort $productRepository,
    ) {
    }

    public function execute(int $productId): array
    {
        $product = $this->productRepository->findById($productId);
        if (!$product) {
            throw new NotFoundHttpException(message: 'Produto não encontrado.', code: 404);
        }

        return $this->productItemRepository->findByProductId($productId);
    }
}
