<?php

namespace App\Application\UseCases\Categories;

use App\Application\Domain\Dtos\Categories\CreateCategoryDto;
use App\Application\Domain\Entities\Categories\Entity\Category;
use App\Application\Port\Output\Repositories\CategoryRepositoryPort;

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
