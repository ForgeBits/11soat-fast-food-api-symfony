<?php

namespace App\Infrastructure\Test\Doubles;

use App\Application\Domain\Dtos\Categories\CreateCategoryDto;
use App\Application\Domain\Entities\Categories\Entity\Category;
use App\Application\Port\Output\Repositories\CategoryRepositoryPort;

class InMemoryCategoryRepository implements CategoryRepositoryPort
{
    /** @var array<int, Category> */
    private array $items = [];
    private int $nextId = 1;

    public function reset(): void
    {
        $this->items = [];
        $this->nextId = 1;
    }

    public function seed(Category $category): void
    {
        $ref = new \ReflectionProperty(Category::class, 'id');
        $ref->setAccessible(true);
        if ($category->getId() === null) {
            $ref->setValue($category, $this->nextId++);
        }
        $this->items[$category->getId()] = $category;
    }

    public function create(CreateCategoryDto $dto): Category
    {
        $category = new Category();
        $category->setName($dto->name);
        $category->setDescription($dto->description);

        $ref = new \ReflectionProperty(Category::class, 'id');
        $ref->setAccessible(true);
        $ref->setValue($category, $this->nextId++);
        $this->items[$category->getId()] = $category;
        return $category;
    }

    public function findById(int $id): ?Category
    {
        return $this->items[$id] ?? null;
    }

    public function findByName(string $name): ?Category
    {
        foreach ($this->items as $cat) {
            if ($cat->getName() === $name) {
                return $cat;
            }
        }
        return null;
    }
}
