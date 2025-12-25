<?php

namespace App\Application\UseCases\Categories;

use App\Application\Port\Output\Repositories\CategoryRepositoryPort;
use App\Domain\Categories\DTO\CreateCategoryDto;
use App\Domain\Categories\Entity\Category;

readonly class CreateCategoryUseCase
{
    public function __construct(
        public CategoryRepositoryPort $categoryRepository,
    ) {

    }

    public function execute(CreateCategoryDto $dto): Category
    {
        return $this->categoryRepository->create($dto);
    }
}
