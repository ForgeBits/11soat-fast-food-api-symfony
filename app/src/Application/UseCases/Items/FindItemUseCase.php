<?php

namespace App\Application\UseCases\Items;

use App\Application\Domain\Entities\Items\Entity\Item;
use App\Application\Port\Output\Repositories\ItemRepositoryPort;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

readonly class FindItemUseCase
{
    public function __construct(
        public ItemRepositoryPort $itemRepository,
    ) {

    }

    public function execute(int $id): Item
    {
        $item = $this->itemRepository->findById($id);
        if (!$item) {
            throw new NotFoundHttpException(message: 'Item não encontrado.', code: 404);
        }

        return $item;
    }
}
