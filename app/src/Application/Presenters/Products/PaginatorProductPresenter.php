<?php

namespace App\Application\Presenters\Products;

use App\Application\Domain\Dtos\Commons\PaginationQueryDto;

class PaginatorProductPresenter
{
    public static function toResponse(PaginationQueryDto $paginationQueryDto, array $items): array
    {
        return [
            'items' => $items,
            'meta' => [
                'page' => $paginationQueryDto->page,
                'limit' => $paginationQueryDto->limit,
                'total' => count($items),
            ],
        ];
    }

}
