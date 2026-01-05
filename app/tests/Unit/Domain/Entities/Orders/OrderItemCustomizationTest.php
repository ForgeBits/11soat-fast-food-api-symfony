<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Entities\Orders;

use App\Application\Domain\Entities\Items\Entity\Item;
use App\Application\Domain\Entities\Orders\Entity\OrderItem;
use App\Application\Domain\Entities\Orders\Entity\OrderItemCustomization;
use App\Application\Domain\Entities\Products\Entity\Product;
use PHPUnit\Framework\TestCase;

class OrderItemCustomizationTest extends TestCase
{
    private function makeOrderItem(): OrderItem
    {
        $product = (new Product())
            ->setName('Produto')
            ->setAmount(10.0)
            ->setUrlImg('p.png')
            ->setAvailable(true)
            ->setCustomizable(true);

        $orderItem = new OrderItem();
        $orderItem->setProduct($product)
            ->setTitle('Item do Pedido')
            ->setQuantity(1)
            ->setPrice(10.0);

        return $orderItem;
    }

    private function makeItem(): Item
    {
        return (new Item())
            ->setName('Adicional')
            ->setDescription('Extra')
            ->setPrice(2.75)
            ->setUrlImg('i.png')
            ->setAvailable(true);
    }

    public function testSettersGettersTouchAndToArray(): void
    {
        $c = new OrderItemCustomization();
        $c->setOrderItem($this->makeOrderItem())
            ->setItem($this->makeItem())
            ->setTitle('Sem cebola')
            ->setQuantity(2)
            ->setPrice(1.25)
            ->setObservation('Observação');

        $this->assertSame('Sem cebola', $c->getTitle());
        $this->assertSame(2, $c->getQuantity());
        $this->assertSame(1.25, $c->getPrice());
        $this->assertSame('Observação', $c->getObservation());

        $updatedAt = $c->getUpdatedAt();
        usleep(1000);
        $c->touch();
        // Permitir igualdade de timestamp no mesmo segundo
        $this->assertGreaterThanOrEqual($updatedAt->getTimestamp(), $c->getUpdatedAt()->getTimestamp());

        $arr = $c->toArray();
        $this->assertSame('Sem cebola', $arr['title']);
        $this->assertSame(2, $arr['quantity']);
        $this->assertSame(1.25, $arr['price']);
        $this->assertArrayHasKey('item_id', $arr);
        $this->assertArrayHasKey('created_at', $arr);
        $this->assertArrayHasKey('updated_at', $arr);
    }
}
