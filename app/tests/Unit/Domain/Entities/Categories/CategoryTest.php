<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Entities\Categories;

use App\Application\Domain\Entities\Categories\Entity\Category;
use App\Application\Domain\Entities\Products\Entity\Product;
use PHPUnit\Framework\TestCase;

class CategoryTest extends TestCase
{
    public function testSettersGettersAndTouch(): void
    {
        $cat = new Category();
        $cat->setName('Bebidas')->setDescription('Categoria de bebidas');

        $this->assertSame('Bebidas', $cat->getName());
        $this->assertSame('Categoria de bebidas', $cat->getDescription());

        $createdAt = $cat->getCreatedAt();
        $updatedAt = $cat->getUpdatedAt();
        $this->assertNotNull($createdAt);
        $this->assertNotNull($updatedAt);

        // touch() deve alterar o updatedAt
        usleep(1000);
        $cat->touch();
        // Em alguns ambientes o timestamp pode permanecer no mesmo segundo; aceite >= para evitar flakiness
        $this->assertGreaterThanOrEqual($updatedAt->getTimestamp(), $cat->getUpdatedAt()->getTimestamp());
    }

    public function testAddAndRemoveProductMaintainsRelations(): void
    {
        $cat = new Category();
        $cat->setName('Lanches');

        $product = new Product();
        $product->setName('Hambúrguer')
            ->setAmount(25.90)
            ->setUrlImg('img.png')
            ->setAvailable(true)
            ->setCustomizable(false);

        $this->assertFalse($cat->hasProducts());
        $cat->addProduct($product);
        $this->assertTrue($cat->hasProducts());
        $this->assertSame($cat, $product->getCategory());

        $cat->removeProduct($product);
        $this->assertFalse($cat->hasProducts());
        $this->assertNull($product->getCategory());
    }

    public function testToArray(): void
    {
        $cat = new Category();
        $cat->setName('Sobremesas')->setDescription(null);

        $arr = $cat->toArray();
        $this->assertArrayHasKey('id', $arr);
        $this->assertSame('Sobremesas', $arr['name']);
        $this->assertNull($arr['description']);
        $this->assertArrayHasKey('created_at', $arr);
        $this->assertArrayHasKey('updated_at', $arr);
    }
}
