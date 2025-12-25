<?php

namespace App\Application\Presenters\Category;

use App\Domain\Categories\Entity\Category;

class CategoryPresenter
{
    public static function toResponse(Category $category): array
    {
        return [
            'id' => $category->getId(),
            'name' => $category->getName(),
            'description' => $category->getDescription(),
            'created_at' => $category->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $category->getUpdatedAt()->format('Y-m-d H:i:s'),
        ];
    }

}
