<?php

namespace App\Application\UseCases\ProductItem;

use App\Application\Domain\Dtos\ProductItem\CreateProductItemDto;
use App\Application\Domain\Entities\ProductItem\Entity\ProductItem;
use App\Application\Port\Output\Repositories\ItemRepositoryPort;
use App\Application\Port\Output\Repositories\ProductItemRepositoryPort;
use App\Application\Port\Output\Repositories\ProductRepositoryPort;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

readonly class CreateProductItemUseCase
{
    public function __construct(
        private ProductItemRepositoryPort $productItemRepository,
        private ProductRepositoryPort $productRepository,
        private ItemRepositoryPort $itemRepository,
    ) {
    }

    public function execute(CreateProductItemDto $dto): ProductItem
    {
        $product = $this->productRepository->findById($dto->productId);
        if (!$product) {
            throw new NotFoundHttpException(message: 'Produto não encontrado.', code: 404);
        }

        $item = $this->itemRepository->findById($dto->itemId);
        if (!$item) {
            throw new NotFoundHttpException(message: 'Item não encontrado.', code: 404);
        }

        $existing = $this->productItemRepository->findByProductAndItem($dto->productId, $dto->itemId);
        if ($existing) {
            throw new ConflictHttpException(message: 'A relação produto-item já existe.', code: 409);
        }

        return $this->productItemRepository->create($dto);
    }
}
