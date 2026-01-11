<?php

namespace App\Application\UseCases\Categories;

use App\Application\Domain\Entities\Categories\Entity\Category;
use App\Application\Port\Output\Repositories\CategoryRepositoryPort;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

readonly class FindCategoryUseCase
{
    public function __construct(
        public CategoryRepositoryPort $categoryRepository,
    ) {

    }

    public function execute(int $id): Category
    {
        $category = $this->categoryRepository->findById($id);

        if (!$category) {
            throw new NotFoundHttpException('Product not found');
        }

        return $category;
    }
}
