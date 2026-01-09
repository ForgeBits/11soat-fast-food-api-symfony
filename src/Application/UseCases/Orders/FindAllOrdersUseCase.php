<?php

namespace App\Application\UseCases\Orders;

use App\Application\Domain\Dtos\Commons\PaginationQueryDto;
use App\Application\Port\Output\Repositories\OrderRepositoryPort;

readonly class FindAllOrdersUseCase
{
    public function __construct(
        public OrderRepositoryPort $orderRepository,
    ) {
    }

    public function execute(PaginationQueryDto $dto): array
    {
        $filters = [];
        return $this->orderRepository->findAllPaginated($filters, $dto->page, $dto->perPage);
    }
}
