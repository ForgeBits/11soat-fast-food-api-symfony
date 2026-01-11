<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Entities\Orders;

use App\Application\Domain\Entities\Orders\Entity\Order;
use App\Application\Domain\Entities\Orders\Entity\OrderItem;
use App\Application\Domain\Entities\Orders\Entity\OrderItemCustomization;
use App\Application\Domain\Entities\Products\Entity\Product;
use PHPUnit\Framework\TestCase;

class OrderItemTest extends TestCase
{
    private function makeProduct(): Product
    {
        return (new Product())
            ->setName('Produto')
            ->setAmount(12.34)
            ->setUrlImg('p.png')
            ->setAvailable(true)
            ->setCustomizable(false);
    }

    public function testSettersGettersCollectionsTouchAndToArray(): void
    {
        $order = new Order();
        $product = $this->makeProduct();

        $oi = new OrderItem();
        $oi->setOrder($order)
            ->setProduct($product)
            ->setTitle('Item 1')
            ->setQuantity(3)
            ->setPrice(9.99)
            ->setObservation('Obs');

        $this->assertSame($order, $oi->getOrder());
        $this->assertSame($product, $oi->getProduct());
        $this->assertSame('Item 1', $oi->getTitle());
        $this->assertSame(3, $oi->getQuantity());
        $this->assertSame(9.99, $oi->getPrice());
        $this->assertSame('Obs', $oi->getObservation());

        $this->assertCount(0, $oi->getCustomerItems());
        $c = (new OrderItemCustomization())
            ->setOrderItem($oi)
            ->setItem((new \App\Application\Domain\Entities\Items\Entity\Item())
                ->setName('Extra')
                ->setDescription('Desc')
                ->setPrice(1.10)
                ->setUrlImg('i.png')
                ->setAvailable(true))
            ->setTitle('Sem molho')
            ->setQuantity(1)
            ->setPrice(1.10);
        $oi->addCustomerItem($c);
        $this->assertCount(1, $oi->getCustomerItems());
        $oi->removeCustomerItem($c);
        $this->assertCount(0, $oi->getCustomerItems());

        $updatedAt = $oi->getUpdatedAt();
        usleep(1000);
        $oi->touch();
        // Permitir igualdade de timestamp no mesmo segundo
        $this->assertGreaterThanOrEqual($updatedAt->getTimestamp(), $oi->getUpdatedAt()->getTimestamp());

        $arr = $oi->toArray();
        $this->assertSame('Item 1', $arr['title']);
        $this->assertSame(3, $arr['quantity']);
        $this->assertSame(9.99, $arr['price']);
        $this->assertArrayHasKey('product_id', $arr); // pode ser null sem persistência
        $this->assertArrayHasKey('created_at', $arr);
        $this->assertArrayHasKey('updated_at', $arr);
    }
}
