<?php

namespace App\Application\UseCases\Products;

use App\Application\Domain\Entities\Products\Entity\Product;
use App\Application\Port\Output\Repositories\ProductRepositoryPort;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

readonly class FindProductUseCase
{
    public function __construct(
        public ProductRepositoryPort $productRepository,
    ) {

    }

    public function execute(int $id): Product
    {
        $product = $this->productRepository->findById($id);

        if (!$product) {
            throw new NotFoundHttpException('Product not found');
        }

        return $product;
    }
}
