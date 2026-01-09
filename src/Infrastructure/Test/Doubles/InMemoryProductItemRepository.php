<?php

namespace App\Infrastructure\Test\Doubles;

use App\Application\Domain\Dtos\ProductItem\CreateProductItemDto;
use App\Application\Domain\Dtos\ProductItem\UpdateProductItemDto;
use App\Application\Domain\Entities\Items\Entity\Item;
use App\Application\Domain\Entities\ProductItem\Entity\ProductItem;
use App\Application\Domain\Entities\Products\Entity\Product;
use App\Application\Port\Output\Repositories\ProductItemRepositoryPort;

class InMemoryProductItemRepository implements ProductItemRepositoryPort
{
    /** @var array<int, ProductItem> */
    private array $storage = [];
    private int $autoId = 1;

    public function __construct(
        private readonly InMemoryProductRepository $productRepo,
        private readonly InMemoryItemRepository $itemRepo,
    ) {
    }

    public function reset(): void
    {
        $this->storage = [];
        $this->autoId = 1;
    }

    public function create(CreateProductItemDto $dto): ProductItem
    {
        $product = $this->productRepo->findById($dto->productId);
        $item = $this->itemRepo->findById($dto->itemId);

        if (!$product) {
            $product = new Product();
            // set private id via reflection for presentational purposes
            $ref = new \ReflectionProperty(Product::class, 'id');
            $ref->setAccessible(true);
            $ref->setValue($product, $dto->productId);
            $product->setName('Product #'.$dto->productId);
        }
        if (!$item) {
            $item = new Item();
            $ref = new \ReflectionProperty(Item::class, 'id');
            $ref->setAccessible(true);
            $ref->setValue($item, $dto->itemId);
            $item->setName('Item #'.$dto->itemId)->setPrice(0)->setUrlImg('')->setAvailable(true);
        }

        $entity = new ProductItem($product, $item);
        $entity->setEssential($dto->essential);
        $entity->setQuantity($dto->quantity);
        $entity->setCustomizable($dto->customizable);

        $this->assignId($entity);
        $this->storage[$entity->getId()] = $entity;
        return $entity;
    }

    public function findByProductAndItem(int $productId, int $itemId): ?ProductItem
    {
        foreach ($this->storage as $pi) {
            if ($pi->getProductId() === $productId && $pi->getItemId() === $itemId) {
                return $pi;
            }
        }
        return null;
    }

    public function findById(int $id): ?ProductItem
    {
        return $this->storage[$id] ?? null;
    }

    public function findAllPaginated(array $filters, int $page, int $perPage): array
    {
        return array_values($this->storage);
    }

    public function findByProductId(int $productId): array
    {
        return array_values(array_filter($this->storage, fn(ProductItem $pi) => $pi->getProductId() === $productId));
    }

    public function update(UpdateProductItemDto $dto): ProductItem
    {
        $entity = $this->storage[$dto->id] ?? null;
        if (!$entity) {
            throw new \RuntimeException('ProductItem not found');
        }
        $entity->setEssential($dto->essential);
        $entity->setQuantity($dto->quantity);
        $entity->setCustomizable($dto->customizable);
        $entity->touch();
        return $entity;
    }

    public function delete(ProductItem $entity): void
    {
        unset($this->storage[$entity->getId()]);
    }

    private function assignId(ProductItem $entity): void
    {
        $ref = new \ReflectionProperty(ProductItem::class, 'id');
        $ref->setAccessible(true);
        $ref->setValue($entity, $this->autoId++);
    }
}
