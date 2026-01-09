<?php

namespace App\Application\UseCases\Orders;

use App\Application\Port\Output\Repositories\OrderRepositoryPort;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

readonly class DeleteOrderUseCase
{
    public function __construct(private OrderRepositoryPort $orderRepository)
    {
    }

    public function execute(int $id): void
    {
        $order = $this->orderRepository->findById($id);
        if (!$order) {
            throw new NotFoundHttpException('Order not found');
        }

        $this->orderRepository->delete($order);
    }
}
