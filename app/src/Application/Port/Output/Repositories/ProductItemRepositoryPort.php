<?php

namespace App\Application\Port\Output\Repositories;

use App\Application\Domain\Dtos\ProductItem\CreateProductItemDto;
use App\Application\Domain\Entities\ProductItem\Entity\ProductItem;

interface ProductItemRepositoryPort
{
    public function create(CreateProductItemDto $dto): ProductItem;

    public function findByProductAndItem(int $productId, int $itemId): ?ProductItem;
}
