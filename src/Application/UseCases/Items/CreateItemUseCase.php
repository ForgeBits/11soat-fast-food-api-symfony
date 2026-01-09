<?php

namespace App\Application\UseCases\Items;

use App\Application\Domain\Dtos\Items\CreateItemDto;
use App\Application\Domain\Entities\Items\Entity\Item;
use App\Application\Port\Output\Repositories\ItemRepositoryPort;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

readonly class CreateItemUseCase
{
    public function __construct(
        public ItemRepositoryPort $itemRepository,
    ) {

    }

    public function execute(CreateItemDto $dto): Item
    {
        $existingItem = $this->itemRepository->findByName($dto->name);

        if ($existingItem) {
            throw new ConflictHttpException('Um item com esse nome já existe.', null, 409);
        }

        return $this->itemRepository->create($dto);
    }
}
