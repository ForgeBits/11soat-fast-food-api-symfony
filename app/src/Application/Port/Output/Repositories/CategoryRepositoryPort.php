<?php

namespace App\Application\Port\Output\Repositories;

use App\Application\Domain\Dtos\Categories\CreateCategoryDto;
use App\Application\Domain\Entities\Categories\Entity\Category;

interface CategoryRepositoryPort
{
    public function create(CreateCategoryDto $dto): Category;
    public function findById(int $id): ?Category;
    public function findByName(string $name): ?Category;
}
