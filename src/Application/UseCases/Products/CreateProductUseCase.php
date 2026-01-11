<?php

namespace App\Application\UseCases\Products;

use App\Application\Domain\Dtos\Products\CreateProductDto;
use App\Application\Domain\Entities\Products\Entity\Product;
use App\Application\Port\Output\Repositories\CategoryRepositoryPort;
use App\Application\Port\Output\Repositories\ProductRepositoryPort;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

readonly class CreateProductUseCase
{
    public function __construct(
        public ProductRepositoryPort $productRepository,
        public CategoryRepositoryPort $categoryRepository,
    ) {

    }

    public function execute(CreateProductDto $dto): Product
    {
        $existingProduct = $this->productRepository->findByName($dto->name);

        if ($existingProduct) {
            throw new ConflictHttpException('Um produto com esse nome já existe.');
        }

        $category = $this->categoryRepository->findById($dto->category_id);

        if (!$category) {
            throw new NotFoundHttpException('A categoria especificada não existe.');
        }

        return $this->productRepository->create($dto);
    }
}
