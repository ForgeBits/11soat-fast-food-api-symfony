<?php

namespace App\Infrastructure\Test\Doubles;

use App\Application\Domain\Dtos\Products\CreateProductDto;
use App\Application\Domain\Dtos\Products\UpdateProductDto;
use App\Application\Domain\Entities\Products\Entity\Product;
use App\Application\Port\Output\Repositories\ProductRepositoryPort;

class InMemoryProductRepository implements ProductRepositoryPort
{
    /** @var array<int, Product> */
    private array $items = [];
    private int $nextId = 1;

    public function reset(): void
    {
        $this->items = [];
        $this->nextId = 1;
    }

    public function seed(Product $product): void
    {
        // Assign an ID if none
        $ref = new \ReflectionProperty(Product::class, 'id');
        $ref->setAccessible(true);
        if ($product->getId() === null) {
            $ref->setValue($product, $this->nextId++);
        }
        $this->items[$product->getId()] = $product;
    }

    public function create(CreateProductDto $dto): Product
    {
        $product = new Product();
        $product
            ->setName($dto->name)
            ->setDescription($dto->description)
            ->setAmount($dto->amount)
            ->setUrlImg($dto->url_img)
            ->setCustomizable($dto->customizable)
            ->setAvailable($dto->available);

        // Simulate setting category by reference when category_id provided
        if ($dto->category_id !== null) {
            $category = new \App\Application\Domain\Entities\Categories\Entity\Category();
            $refCat = new \ReflectionProperty(\App\Application\Domain\Entities\Categories\Entity\Category::class, 'id');
            $refCat->setAccessible(true);
            $refCat->setValue($category, $dto->category_id);
            // set a dummy name to satisfy presenter
            $category->setName('Category #'.$dto->category_id);
            $product->setCategory($category);
        }

        // Set ID via reflection
        $ref = new \ReflectionProperty(Product::class, 'id');
        $ref->setAccessible(true);
        $ref->setValue($product, $this->nextId++);

        $this->items[$product->getId()] = $product;
        return $product;
    }

    public function findAllPaginated(array $filters, int $page, int $perPage): array
    {
        return array_values($this->items);
    }

    public function update(UpdateProductDto $dto): Product
    {
        $product = $this->findById($dto->id);
        if (!$product) {
            throw new \RuntimeException('Product not found');
        }
        $product
            ->setName($dto->name)
            ->setDescription($dto->description)
            ->setAmount($dto->amount)
            ->setUrlImg($dto->url_img)
            ->setCustomizable($dto->customizable)
            ->setAvailable($dto->available);
        $product->touch();
        return $product;
    }

    public function findByName(string $name): ?Product
    {
        foreach ($this->items as $product) {
            if ($product->getName() === $name) {
                return $product;
            }
        }
        return null;
    }

    public function findById(int $id): ?Product
    {
        return $this->items[$id] ?? null;
    }

    public function delete(Product $product): void
    {
        $id = $product->getId();
        if ($id !== null && isset($this->items[$id])) {
            unset($this->items[$id]);
        }
    }
}
