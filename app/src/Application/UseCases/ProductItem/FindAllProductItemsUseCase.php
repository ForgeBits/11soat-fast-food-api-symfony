<?php

namespace App\Application\UseCases\ProductItem;

use App\Application\Domain\Dtos\Commons\PaginationQueryDto;
use App\Application\Port\Output\Repositories\ProductItemRepositoryPort;

readonly class FindAllProductItemsUseCase
{
    public function __construct(
        private ProductItemRepositoryPort $repository,
    ) {
    }

    public function execute(PaginationQueryDto $dto): array
    {
        $filters = [];
        return $this->repository->findAllPaginated($filters, $dto->page, $dto->perPage);
    }
}
