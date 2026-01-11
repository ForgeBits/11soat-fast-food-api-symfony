<?php

namespace App\Application\UseCases\Items;

use App\Application\Domain\Dtos\Commons\PaginationQueryDto;
use App\Application\Port\Output\Repositories\ItemRepositoryPort;

readonly class FindAllItemsUseCase
{
    public function __construct(
        public ItemRepositoryPort $itemRepository,
    ) {

    }

    public function execute(PaginationQueryDto $dto): array
    {
        $filters = [];
        return $this->itemRepository->findAllPaginated($filters, $dto->page, $dto->perPage);
    }
}
