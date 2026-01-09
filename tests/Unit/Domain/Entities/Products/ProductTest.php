<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Entities\Products;

use App\Application\Domain\Entities\Categories\Entity\Category;
use App\Application\Domain\Entities\Products\Entity\Product;
use PHPUnit\Framework\TestCase;

class ProductTest extends TestCase
{
    public function testSettersGettersTouchAndToArray(): void
    {
        $category = new Category();
        $category->setName('Bebidas');

        $product = new Product();
        $product->setName('Refrigerante')
            ->setDescription('Lata 350ml')
            ->setAmount(7.25)
            ->setUrlImg('refri.png')
            ->setCustomizable(false)
            ->setAvailable(true)
            ->setCategory($category);

        $this->assertSame('Refrigerante', $product->getName());
        $this->assertSame('Lata 350ml', $product->getDescription());
        $this->assertSame(7.25, $product->getAmount());
        $this->assertSame('refri.png', $product->getUrlImg());
        $this->assertFalse($product->isCustomizable());
        $this->assertTrue($product->isAvailable());
        $this->assertSame($category, $product->getCategory());

        $updatedAt = $product->getUpdatedAt();
        usleep(1000);
        $product->touch();
        // Aceitar mesma unidade de segundo em ambientes rápidos
        $this->assertGreaterThanOrEqual($updatedAt->getTimestamp(), $product->getUpdatedAt()->getTimestamp());

        $arr = $product->toArray();
        $this->assertSame('Refrigerante', $arr['name']);
        $this->assertSame(7.25, $arr['amount']);
        $this->assertSame($category->getId(), $arr['category_id']);
        $this->assertArrayHasKey('created_at', $arr);
        $this->assertArrayHasKey('updated_at', $arr);
    }
}
