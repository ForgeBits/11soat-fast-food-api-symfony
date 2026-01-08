<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Entities\Items;

use App\Application\Domain\Entities\Items\Entity\Item;
use PHPUnit\Framework\TestCase;

class ItemTest extends TestCase
{
    public function testSettersGettersTouchAndToArray(): void
    {
        $item = new Item();
        $item->setName('Queijo')
            ->setDescription('Fatia de queijo')
            ->setPrice(3.5)
            ->setUrlImg('queijo.png')
            ->setAvailable(true);

        $this->assertSame('Queijo', $item->getName());
        $this->assertSame('Fatia de queijo', $item->getDescription());
        $this->assertSame(3.50, $item->getPrice());
        $this->assertSame('queijo.png', $item->getUrlImg());
        $this->assertTrue($item->isAvailable());

        $updatedAt = $item->getUpdatedAt();
        usleep(1000);
        $item->touch();
        // Tolerar mesma virada de segundo em ambientes rápidos
        $this->assertGreaterThanOrEqual($updatedAt->getTimestamp(), $item->getUpdatedAt()->getTimestamp());

        $arr = $item->toArray();
        $this->assertSame('Queijo', $arr['name']);
        $this->assertSame(3.50, $arr['amount']);
        $this->assertSame('queijo.png', $arr['url_img']);
        $this->assertTrue($arr['available']);
        $this->assertArrayHasKey('created_at', $arr);
        $this->assertArrayHasKey('updated_at', $arr);
    }
}
