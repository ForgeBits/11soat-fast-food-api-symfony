<?php

namespace App\Application\Presenters\ProductItem;

use App\Application\Domain\Entities\ProductItem\Entity\ProductItem;

class ProductItemPresenter
{
    public static function toResponse(ProductItem $pi): array
    {
        return [
            'id' => $pi->getId(),
            'productId' => $pi->getProductId(),
            'itemId' => $pi->getItemId(),
            'essential' => $pi->isEssential(),
            'quantity' => $pi->getQuantity(),
            'customizable' => $pi->isCustomizable(),
            'product' => [
                'id' => $pi->getProduct()->getId(),
                'name' => $pi->getProduct()->getName(),
            ],
            'item' => [
                'id' => $pi->getItem()->getId(),
                'name' => $pi->getItem()->getName(),
            ],
            'created_at' => $pi->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $pi->getUpdatedAt()->format('Y-m-d H:i:s'),
        ];
    }
}
