<?php

namespace App\Application\Port\Output\Repositories;

use App\Domain\Products\DTO\CreateProductDto;
use App\Domain\Products\Entity\Product;

interface ProductRepositoryPort
{
    public function create(CreateProductDto $dto): Product;
}
