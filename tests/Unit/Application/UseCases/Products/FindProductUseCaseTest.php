<?php

namespace App\Tests\Unit\Application\UseCases\Products;

use App\Application\Domain\Entities\Products\Entity\Product;
use App\Application\Port\Output\Repositories\ProductRepositoryPort;
use App\Application\UseCases\Products\FindProductUseCase;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FindProductUseCaseTest extends TestCase
{
    public function test_execute_returns_product_when_found(): void
    {
        $repo = $this->createMock(ProductRepositoryPort::class);

        $product = (new Product())->setName('X-Burger');
        $repo->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($product);

        $uc = new FindProductUseCase($repo);
        $result = $uc->execute(1);

        $this->assertInstanceOf(Product::class, $result);
        $this->assertSame('X-Burger', $result->getName());
    }

    public function test_execute_throws_not_found_when_missing(): void
    {
        $repo = $this->createMock(ProductRepositoryPort::class);
        $repo->method('findById')->with(999)->willReturn(null);

        $uc = new FindProductUseCase($repo);

        $this->expectException(NotFoundHttpException::class);
        $uc->execute(999);
    }
}
