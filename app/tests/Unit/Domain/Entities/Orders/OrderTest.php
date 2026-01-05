<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Entities\Orders;

use App\Application\Domain\Entities\Orders\Entity\Order;
use App\Application\Domain\Entities\Orders\Entity\OrderItem;
use App\Application\Domain\Entities\Orders\Enum\OrderStatus;
use App\Application\Domain\Entities\Products\Entity\Product;
use PHPUnit\Framework\TestCase;

class OrderTest extends TestCase
{
    private function makeProduct(string $name): Product
    {
        return (new Product())
            ->setName($name)
            ->setDescription(null)
            ->setAmount(19.9)
            ->setUrlImg('p.png')
            ->setAvailable(true)
            ->setCustomizable(false);
    }

    private function makeOrderItem(string $title, Product $product, Order $order): OrderItem
    {
        return (new OrderItem())
            ->setOrder($order)
            ->setProduct($product)
            ->setTitle($title)
            ->setQuantity(2)
            ->setPrice(9.95);
    }

    public function testSettersCollectionsTouchAndToArray(): void
    {
        $order = new Order();
        $order->setClientId(123)
            ->setStatus(OrderStatus::PENDING)
            ->setAmount(0)
            ->setTransactionId('tx-123')
            ->setIsRandomClient(true)
            ->setCodeClientRandom(42)
            ->setObservation('Sem gelo');

        $this->assertSame(123, $order->getClientId());
        $this->assertSame(OrderStatus::PENDING, $order->getStatus());
        $this->assertSame(0.0, $order->getAmount());
        $this->assertSame('tx-123', $order->getTransactionId());
        $this->assertTrue($order->isRandomClient());
        $this->assertSame(42, $order->getCodeClientRandom());
        $this->assertSame('Sem gelo', $order->getObservation());

        // Products collection
        $p1 = $this->makeProduct('Produto A');
        $p2 = $this->makeProduct('Produto B');
        $order->addProduct($p1)->addProduct($p2);
        $this->assertCount(2, $order->getProducts());
        $order->removeProduct($p1);
        $this->assertCount(1, $order->getProducts());

        // Items collection
        $i1 = $this->makeOrderItem('Item A', $p2, $order);
        $order->addItem($i1);
        $this->assertCount(1, $order->getItems());
        $this->assertSame($order, $i1->getOrder());
        $order->removeItem($i1);
        $this->assertCount(0, $order->getItems());

        // touch()
        $updatedAt = $order->getUpdatedAt();
        usleep(1000);
        $order->touch();
        // Permitir igualdade de timestamp no mesmo segundo
        $this->assertGreaterThanOrEqual($updatedAt->getTimestamp(), $order->getUpdatedAt()->getTimestamp());

        // toArray()
        $order->addProduct($p2)->addItem($this->makeOrderItem('Item B', $p2, $order));
        $arr = $order->toArray();
        $this->assertSame(123, $arr['client_id']);
        $this->assertSame(OrderStatus::PENDING->value, $arr['status']);
        $this->assertIsArray($arr['products']);
        $this->assertIsArray($arr['items']);
        $this->assertArrayHasKey('created_at', $arr);
        $this->assertArrayHasKey('updated_at', $arr);
    }
}
