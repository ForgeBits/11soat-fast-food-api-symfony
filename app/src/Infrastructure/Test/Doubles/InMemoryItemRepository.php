<?php

namespace App\Infrastructure\Test\Doubles;

use App\Application\Domain\Dtos\Items\CreateItemDto;
use App\Application\Domain\Dtos\Items\UpdateItemDto;
use App\Application\Domain\Entities\Items\Entity\Item;
use App\Application\Port\Output\Repositories\ItemRepositoryPort;

class InMemoryItemRepository implements ItemRepositoryPort
{
    /** @var array<int, Item> */
    private array $items = [];
    private int $autoId = 1;

    public function reset(): void
    {
        $this->items = [];
        $this->autoId = 1;
    }

    public function seed(Item $item): Item
    {
        $ref = new \ReflectionProperty(Item::class, 'id');
        $ref->setAccessible(true);
        $ref->setValue($item, $this->autoId++);
        $this->items[$item->getId()] = $item;
        return $item;
    }

    public function create(CreateItemDto $dto): Item
    {
        $item = new Item();
        $item->setName($dto->name)
            ->setDescription($dto->description)
            ->setPrice($dto->price)
            ->setUrlImg($dto->url_img)
            ->setAvailable($dto->available);

        $this->seed($item);
        return $item;
    }

    public function findAllPaginated(array $filters, int $page, int $perPage): array
    {
        return array_values($this->items);
    }

    public function update(UpdateItemDto $dto): Item
    {
        $item = $this->items[$dto->id] ?? null;
        if (!$item) {
            throw new \RuntimeException('Item not found');
        }
        $item->setName($dto->name)
            ->setDescription($dto->description)
            ->setPrice($dto->price)
            ->setUrlImg($dto->url_img)
            ->setAvailable($dto->available);
        $item->touch();
        $this->items[$item->getId()] = $item;
        return $item;
    }

    public function findByName(string $name): ?Item
    {
        foreach ($this->items as $it) {
            if ($it->getName() === $name) {
                return $it;
            }
        }
        return null;
    }

    public function findById(int $id): ?Item
    {
        return $this->items[$id] ?? null;
    }

    public function delete(Item $item): void
    {
        unset($this->items[$item->getId()]);
    }
}
