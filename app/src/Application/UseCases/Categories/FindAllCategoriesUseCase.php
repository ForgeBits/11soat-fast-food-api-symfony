<?php

namespace App\Application\UseCases\Categories;

use App\Application\Domain\Dtos\Commons\PaginationQueryDto;
use App\Application\Port\Output\Repositories\CategoryRepositoryPort;

readonly class FindAllCategoriesUseCase
{
    public function __construct(
        public CategoryRepositoryPort $categoryRepositoryPort,
    ) {

    }

    public function execute(PaginationQueryDto $dto): array
    {
        $filters = [];
        return $this->categoryRepositoryPort->findAllPaginated($filters, $dto->page, $dto->perPage);
    }
}
