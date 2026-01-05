<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Entities\ProductItem;

use App\Application\Domain\Entities\Items\Entity\Item;
use App\Application\Domain\Entities\ProductItem\Entity\ProductItem;
use App\Application\Domain\Entities\Products\Entity\Product;
use PHPUnit\Framework\TestCase;

class ProductItemTest extends TestCase
{
    public function testConstructorSettersGettersTouchAndToArray(): void
    {
        $product = (new Product())
            ->setName('Combo X')
            ->setDescription('Descrição')
            ->setAmount(30.00)
            ->setUrlImg('combo.png')
            ->setAvailable(true)
            ->setCustomizable(true);

        $item = (new Item())
            ->setName('Batata')
            ->setDescription('Batata média')
            ->setPrice(9.5)
            ->setUrlImg('batata.png')
            ->setAvailable(true);

        $pi = new ProductItem($product, $item);
        $this->assertSame($product, $pi->getProduct());
        $this->assertSame($item, $pi->getItem());

        $pi->setEssential(true)->setQuantity(2)->setCustomizable(false);
        $this->assertTrue($pi->isEssential());
        $this->assertSame(2, $pi->getQuantity());
        $this->assertFalse($pi->isCustomizable());

        $updatedAt = $pi->getUpdatedAt();
        usleep(1000);
        $pi->touch();
        // Permitir igualdade de timestamp no mesmo segundo
        $this->assertGreaterThanOrEqual($updatedAt->getTimestamp(), $pi->getUpdatedAt()->getTimestamp());

        $arr = $pi->toArray();
        $this->assertSame($pi->isEssential(), $arr['essential']);
        $this->assertSame(2, $arr['quantity']);
        $this->assertSame('Combo X', $arr['product']['name']);
        $this->assertSame('Batata', $arr['item']['name']);
        $this->assertArrayHasKey('created_at', $arr);
        $this->assertArrayHasKey('updated_at', $arr);
    }
}
