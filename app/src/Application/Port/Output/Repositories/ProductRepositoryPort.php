<?php

namespace App\Application\Port\Output\Repositories;

use App\Application\Domain\Dtos\Products\CreateProductDto;
use App\Application\Domain\Dtos\Products\UpdateProductDto;
use App\Application\Domain\Entities\Products\Entity\Product;

interface ProductRepositoryPort
{
    public function create(CreateProductDto $dto): Product;
    public function paginate(array $filters, int $page, int $perPage);
    public function update(UpdateProductDto $dto): Product;
    public function findByName(string $name): ?Product;
}
