<?php

namespace App\Application\UseCases\Categories;

use App\Application\Domain\Dtos\Categories\UpdateCategoryDto;
use App\Application\Domain\Entities\Categories\Entity\Category;
use App\Application\Port\Output\Repositories\CategoryRepositoryPort;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

readonly class UpdateCategoryUseCase
{
    public function __construct(
        public CategoryRepositoryPort $categoryRepository,
    ) {

    }

    public function execute(UpdateCategoryDto $dto): Category
    {
        $category = $this->categoryRepository->findById($dto->id);

        if (!$category) {
            throw new NotFoundHttpException('A categoria especificada não existe.');
        }

        $existingCategory = $this->categoryRepository->findByName($dto->name);

        if ($existingCategory && $existingCategory->getId() !== $dto->id) {
            throw new ConflictHttpException('Uma categoria com esse nome já existe.');
        }

        return $this->categoryRepository->update($dto);
    }
}
