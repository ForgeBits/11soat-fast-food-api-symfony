<?php

namespace App\Application\Port\Output\Repositories;

use App\Application\Domain\Dtos\ProductItem\CreateProductItemDto;
use App\Application\Domain\Dtos\ProductItem\UpdateProductItemDto;
use App\Application\Domain\Entities\ProductItem\Entity\ProductItem;

interface ProductItemRepositoryPort
{
    public function create(CreateProductItemDto $dto): ProductItem;

    public function findByProductAndItem(int $productId, int $itemId): ?ProductItem;

    public function findById(int $id): ?ProductItem;

    public function findAllPaginated(array $filters, int $page, int $perPage): array;

    /**
     * @return ProductItem[]
     */
    public function findByProductId(int $productId): array;

    public function update(UpdateProductItemDto $dto): ProductItem;

    public function delete(ProductItem $entity): void;
}
