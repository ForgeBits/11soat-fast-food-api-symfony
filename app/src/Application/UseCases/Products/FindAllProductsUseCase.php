<?php

namespace App\Application\UseCases\Products;

use App\Application\Domain\Dtos\Commons\PaginationQueryDto;
use App\Application\Port\Output\Repositories\ProductRepositoryPort;

readonly class FindAllProductsUseCase
{
    public function __construct(
        public ProductRepositoryPort $productRepository,
    ) {

    }

    public function execute(PaginationQueryDto $dto)
    {
        $filters = [];
        return $this->productRepository->findAllPaginated($filters, $dto->page, $dto->perPage);
    }
}
