<?php

namespace App\Application\UseCases\Orders;

use App\Application\Port\Output\Repositories\OrderRepositoryPort;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

readonly class FindOrderUseCase
{
    public function __construct(
        private OrderRepositoryPort $orderRepository,
    ) {
    }

    public function execute(int $id)
    {
        $order = $this->orderRepository->findById($id);
        if (!$order) {
            throw new NotFoundHttpException('Order not found');
        }

        return $order;
    }
}
