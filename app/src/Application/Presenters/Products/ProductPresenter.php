<?php

namespace App\Application\Presenters\Products;

use App\Application\Domain\Entities\Products\Entity\Product;
use App\Application\Presenters\Category\CategoryPresenter;
use App\Application\Domain\Entities\ProductItem\Entity\ProductItem;

class ProductPresenter
{
    public static function toResponse(Product $product): array
    {
        $productItems = [];
        /** @var ProductItem $pi */
        foreach ($product->getProductItems() as $pi) {
            $productItems[] = [
                'id' => $pi->getId(),
                'itemId' => $pi->getItemId(),
                'essential' => $pi->isEssential(),
                'quantity' => $pi->getQuantity(),
                'customizable' => $pi->isCustomizable(),
                'item' => [
                    'id' => $pi->getItem()->getId(),
                    'name' => $pi->getItem()->getName(),
                ],
                'created_at' => $pi->getCreatedAt()->format('Y-m-d H:i:s'),
                'updated_at' => $pi->getUpdatedAt()->format('Y-m-d H:i:s'),
            ];
        }

        return [
            'id' => $product->getId(),
            'name' => $product->getName(),
            'description' => $product->getDescription(),
            'amount' => $product->getAmount(),
            'url_img' => $product->getUrlImg(),
            'customizable' => $product->isCustomizable(),
            'available' => $product->isAvailable(),
            'category' => CategoryPresenter::toResponse($product->getCategory()),
            'items' => $productItems,
            'created_at' => $product->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $product->getUpdatedAt()->format('Y-m-d H:i:s'),
        ];
    }

}
