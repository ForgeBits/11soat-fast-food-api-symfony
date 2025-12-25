<?php

namespace App\Application\Port\Output\Repositories;

use App\Domain\Products\DTO\CreateProductDto;
use App\Domain\Products\DTO\UpdateProductDto;
use App\Domain\Products\Entity\Product;

interface ProductRepositoryPort
{
    public function create(CreateProductDto $dto): Product;
    public function update(UpdateProductDto $dto): Product;
    public function findByName(string $name): ?Product;
}
