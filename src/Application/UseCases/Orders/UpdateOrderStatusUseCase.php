<?php

namespace App\Application\UseCases\Orders;

use App\Application\Domain\Dtos\Orders\UpdateOrderStatusDto;
use App\Application\Domain\Entities\Orders\Entity\Order;
use App\Application\Port\Output\Repositories\OrderRepositoryPort;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

readonly class UpdateOrderStatusUseCase
{
    public function __construct(private OrderRepositoryPort $orderRepository)
    {
    }

    public function execute(UpdateOrderStatusDto $dto): Order
    {
        $order = $this->orderRepository->findById($dto->id);

        if (!$order) {
            throw new NotFoundHttpException('Order not found');
        }

        return $this->orderRepository->updateStatus($dto->id, $dto->status);
    }
}
