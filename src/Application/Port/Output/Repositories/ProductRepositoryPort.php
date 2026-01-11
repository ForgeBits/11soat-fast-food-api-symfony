<?php

namespace App\Application\Port\Output\Repositories;

use App\Application\Domain\Dtos\Products\CreateProductDto;
use App\Application\Domain\Dtos\Products\UpdateProductDto;
use App\Application\Domain\Entities\Products\Entity\Product;

interface ProductRepositoryPort
{
    public function create(CreateProductDto $dto): Product;

    public function findAllPaginated(array $filters, int $page, int $perPage): array;

    public function update(UpdateProductDto $dto): Product;

    public function findByName(string $name): ?Product;

    public function findById(int $id): ?Product;

    public function delete(Product $product): void;
}
