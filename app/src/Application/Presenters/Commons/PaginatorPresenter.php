<?php

namespace App\Application\Presenters\Commons;

use App\Application\Domain\Dtos\Commons\PaginationQueryDto;

class PaginatorPresenter
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
