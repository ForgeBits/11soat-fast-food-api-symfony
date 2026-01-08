<?php

namespace App\Application\Port\Output\Repositories;

use App\Application\Domain\Dtos\Orders\CreateOrderDto;
use App\Application\Domain\Dtos\Orders\UpdateOrderDto;
use App\Application\Domain\Entities\Orders\Enum\OrderStatus;
use App\Application\Domain\Entities\Orders\Entity\Order;

interface OrderRepositoryPort
{
    public function create(CreateOrderDto $dto): Order;

    public function update(UpdateOrderDto $dto): Order;

    public function updateStatus(int $id, OrderStatus $status): Order;

    public function findById(int $id): ?Order;

    public function findAllPaginated(array $filters, int $page, int $perPage): array;

    public function delete(Order $order): void;
}
