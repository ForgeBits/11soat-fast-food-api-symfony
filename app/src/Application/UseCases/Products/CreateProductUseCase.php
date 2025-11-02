<?php

namespace App\Application\UseCases\Products;

use App\Application\Port\Output\Repositories\ProductRepositoryPort;
use App\Domain\Products\DTO\CreateProductDto;
use App\Domain\Products\Entity\Product;

readonly class CreateProductUseCase
{
    public function __construct(
        public ProductRepositoryPort $productRepository,
    ) {

    }

    public function execute(CreateProductDto $dto): Product
    {
        return $this->productRepository->create($dto);
    }
}
