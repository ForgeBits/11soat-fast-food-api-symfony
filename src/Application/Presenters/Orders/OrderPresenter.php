<?php

namespace App\Application\Presenters\Orders;

use App\Application\Domain\Entities\Orders\Entity\Order;
use App\Application\Domain\Entities\Orders\Entity\OrderItem;
use App\Application\Domain\Entities\Orders\Entity\OrderItemCustomization;

class OrderPresenter
{
    public static function toResponse(Order $order): array
    {
        $items = [];
        /** @var OrderItem $it */
        foreach ($order->getItems() as $it) {
            $customerItems = [];
            /** @var OrderItemCustomization $ci */
            foreach ($it->getCustomerItems() as $ci) {
                $customerItems[] = [
                    'id' => $ci->getId(),
                    'itemId' => $ci->getItem()->getId(),
                    'title' => $ci->getTitle(),
                    'quantity' => $ci->getQuantity(),
                    'price' => $ci->getPrice(),
                    'observation' => $ci->getObservation(),
                    'created_at' => $ci->getCreatedAt()->format('Y-m-d H:i:s'),
                    'updated_at' => $ci->getUpdatedAt()->format('Y-m-d H:i:s'),
                ];
            }

            $items[] = [
                'id' => $it->getId(),
                'productId' => $it->getProduct()->getId(),
                'title' => $it->getTitle(),
                'quantity' => $it->getQuantity(),
                'price' => $it->getPrice(),
                'observation' => $it->getObservation(),
                'customerItems' => $customerItems,
                'created_at' => $it->getCreatedAt()->format('Y-m-d H:i:s'),
                'updated_at' => $it->getUpdatedAt()->format('Y-m-d H:i:s'),
            ];
        }

        return [
            'id' => $order->getId(),
            'client_id' => $order->getClientId(),
            'status' => $order->getStatus()->value,
            'amount' => $order->getAmount(),
            'transaction_id' => $order->getTransactionId(),
            'is_random_client' => $order->isRandomClient(),
            'code_client_random' => $order->getCodeClientRandom(),
            'observation' => $order->getObservation(),
            'items' => $items,
            'created_at' => $order->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $order->getUpdatedAt()->format('Y-m-d H:i:s'),
        ];
    }
}
