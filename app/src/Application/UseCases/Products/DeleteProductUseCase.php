<?php

namespace App\Application\UseCases\Products;

use App\Application\Port\Output\Repositories\ProductRepositoryPort;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

readonly class DeleteProductUseCase
{
    public function __construct(
        public ProductRepositoryPort $productRepository,
    ) {

    }

    public function execute(int $id): void
    {
        $product = $this->productRepository->findById($id);

        if (!$product) {
            throw new NotFoundHttpException('Product not found');
        }

        $this->productRepository->delete($product);
    }
}
