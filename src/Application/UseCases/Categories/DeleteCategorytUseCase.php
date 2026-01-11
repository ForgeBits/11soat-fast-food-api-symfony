<?php

namespace App\Application\UseCases\Categories;

use App\Application\Port\Output\Repositories\CategoryRepositoryPort;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

readonly class DeleteCategorytUseCase
{
    public function __construct(
        public CategoryRepositoryPort $categoryRepository,
    ) {

    }

    public function execute(int $id): void
    {
        $category = $this->categoryRepository->findById($id);

        if (!$category) {
            throw new NotFoundHttpException('Category not found.');
        }

        $this->categoryRepository->delete($category);
    }
}
