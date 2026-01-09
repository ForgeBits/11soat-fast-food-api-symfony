<?php

namespace App\Application\Port\Output\Repositories;

use App\Application\Domain\Dtos\Items\CreateItemDto;
use App\Application\Domain\Dtos\Items\UpdateItemDto;
use App\Application\Domain\Entities\Items\Entity\Item;

interface ItemRepositoryPort
{
    public function create(CreateItemDto $dto): Item;
    public function findAllPaginated(array $filters, int $page, int $perPage): array;
    public function update(UpdateItemDto $dto): Item;
    public function findByName(string $name): ?Item;
    public function findById(int $id): ?Item;
    public function delete(Item $item): void;
}
