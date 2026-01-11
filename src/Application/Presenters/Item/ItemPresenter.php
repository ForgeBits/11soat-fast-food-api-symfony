<?php

namespace App\Application\Presenters\Item;

use App\Application\Domain\Entities\Items\Entity\Item;

class ItemPresenter
{
    public static function toResponse(Item $item): array
    {
        return [
            'id' => $item->getId(),
            'name' => $item->getName(),
            'description' => $item->getDescription(),
            'price' => $item->getPrice(),
            'url_img' => $item->getUrlImg(),
            'available' => $item->isAvailable(),
            'created_at' => $item->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $item->getUpdatedAt()->format('Y-m-d H:i:s'),
        ];
    }

}
