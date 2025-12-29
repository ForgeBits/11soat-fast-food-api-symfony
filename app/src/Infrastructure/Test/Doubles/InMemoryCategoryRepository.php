<?php

namespace App\Infrastructure\Test\Doubles;

use App\Application\Domain\Dtos\Categories\CreateCategoryDto;
use App\Application\Domain\Dtos\Categories\UpdateCategoryDto;
use App\Application\Domain\Entities\Categories\Entity\Category;
use App\Application\Port\Output\Repositories\CategoryRepositoryPort;
use Doctrine\DBAL\Driver\PgSQL\Exception;

class InMemoryCategoryRepository implements CategoryRepositoryPort
{
    /** @var array<int, Category> */
    private array $items = [];
    private int $nextId = 1;
    /** @var array<int,bool> */
    private array $fkProtected = [];

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

    public function findAllPaginated(array $filters, int $page, int $perPage): array
    {
        return array_values($this->items);
    }

    public function update(UpdateCategoryDto $dto): Category
    {
        $category = $this->findById($dto->id);
        if (!$category) {
            throw new \RuntimeException('Category not found');
        }
        $category->setName($dto->name);
        $category->setDescription($dto->description);
        $category->touch();
        return $category;
    }

    public function delete(Category $category): void
    {
        $id = $category->getId();
        if ($id === null) {
            return;
        }
        if (($this->fkProtected[$id] ?? false) === true) {
            // Simulate a FK violation similar to DB layer
            // Use a generic \RuntimeException here; controller in feature tests may not rely on specific type
            throw new \Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException(new Exception('Dbal error'), null);
        }
        unset($this->items[$id]);
    }

    // Test helper to simulate FK constraint on delete
    public function protectWithForeignKey(int $id): void
    {
        $this->fkProtected[$id] = true;
    }
}
