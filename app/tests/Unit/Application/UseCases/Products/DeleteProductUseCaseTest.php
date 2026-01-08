<?php

namespace App\Tests\Unit\Application\UseCases\Products;

use App\Application\Domain\Entities\Products\Entity\Product;
use App\Application\Port\Output\Repositories\ProductRepositoryPort;
use App\Application\UseCases\Products\DeleteProductUseCase;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DeleteProductUseCaseTest extends TestCase
{
    public function test_execute_deletes_when_found(): void
    {
        $repo = $this->createMock(ProductRepositoryPort::class);

        $product = new Product();
        $repo->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($product);

        $repo->expects($this->once())
            ->method('delete')
            ->with($product);

        $uc = new DeleteProductUseCase($repo);
        $uc->execute(1);

        $this->assertTrue(true); // no exceptions
    }

    public function test_execute_throws_not_found_when_missing(): void
    {
        $repo = $this->createMock(ProductRepositoryPort::class);
        $repo->method('findById')->with(999)->willReturn(null);

        $uc = new DeleteProductUseCase($repo);

        $this->expectException(NotFoundHttpException::class);
        $uc->execute(999);
    }
}
