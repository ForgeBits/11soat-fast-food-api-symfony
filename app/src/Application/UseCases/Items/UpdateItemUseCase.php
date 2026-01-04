<?php

namespace App\Application\UseCases\Items;

use App\Application\Domain\Dtos\Items\UpdateItemDto;
use App\Application\Domain\Entities\Items\Entity\Item;
use App\Application\Port\Output\Repositories\ItemRepositoryPort;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

readonly class UpdateItemUseCase
{
    public function __construct(
        public ItemRepositoryPort $itemRepository,
    ) {

    }

    public function execute(UpdateItemDto $dto): Item
    {
        $existing = $this->itemRepository->findById($dto->id);
        if (!$existing) {
            throw new NotFoundHttpException(message: 'O item especificado não existe.', code: 404);
        }

        $byName = $this->itemRepository->findByName($dto->name);

        if ($byName && $byName->getId() !== $dto->id) {
            throw new ConflictHttpException(message: 'Um item com esse nome já existe.', code: 409);
        }

        return $this->itemRepository->update($dto);
    }
}
