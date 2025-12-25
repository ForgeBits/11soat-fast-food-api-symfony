<?php

namespace App\Application\Presenters\Products;

use App\Domain\Products\Entity\Product;

class ProductPresenter
{
    public static function toResponse(Product $product): array
    {
        return [
            'id' => $product->getId(),
            'name' => $product->getName(),
            'description' => $product->getDescription(),
            'amount' => $product->getAmount(),
            'url_img' => $product->getUrlImg(),
            'customizable' => $product->isCustomizable(),
            'available' => $product->isAvailable(),
            'category_id' => $product->getCategoryId(),
            'created_at' => $product->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $product->getUpdatedAt()->format('Y-m-d H:i:s'),
        ];
    }

}
