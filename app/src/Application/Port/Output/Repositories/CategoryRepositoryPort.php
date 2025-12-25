<?php

namespace App\Application\Port\Output\Repositories;

use App\Domain\Categories\DTO\CreateCategoryDto;
use App\Domain\Categories\Entity\Category;

interface CategoryRepositoryPort
{
    public function create(CreateCategoryDto $dto): Category;
    public function findById(int $id): ?Category;
    public function findByName(string $name): ?Category;
}
