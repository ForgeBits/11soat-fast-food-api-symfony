<?php

namespace App\Tests\Unit\Application\UseCases\ProductItem;

use App\Application\Domain\Entities\Products\Entity\Product;
use App\Application\UseCases\ProductItem\FindAllProductItemsUseCase;
use App\Application\UseCases\ProductItem\FindProductItemsByProductUseCase;
use App\Application\Port\Output\Repositories\ProductItemRepositoryPort;
use App\Application\Port\Output\Repositories\ProductRepositoryPort;
use PHPUnit\Framework\TestCase;
use App\Application\Domain\Entities\ProductItem\Entity\ProductItem;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FindAllAndByProductUseCasesTest extends TestCase
{
    public function test_find_all_returns_array(): void
    {
        $piRepo = $this->createMock(ProductItemRepositoryPort::class);
        $pi = $this->getMockBuilder(ProductItem::class)->disableOriginalConstructor()->getMock();
        $piRepo->method('findAllPaginated')->willReturn([$pi]);

        $uc = new FindAllProductItemsUseCase($piRepo);
        $res = $uc->execute(new \App\Application\Domain\Dtos\Commons\PaginationQueryDto());
        $this->assertCount(1, $res);
    }

    public function test_find_by_product_success(): void
    {
        $piRepo = $this->createMock(ProductItemRepositoryPort::class);
        $prodRepo = $this->createMock(ProductRepositoryPort::class);
        $prodRepo->method('findById')->with(1)->willReturn(new Product());
        $pi = $this->getMockBuilder(ProductItem::class)->disableOriginalConstructor()->getMock();
        $piRepo->method('findByProductId')->with(1)->willReturn([$pi]);

        $uc = new FindProductItemsByProductUseCase($piRepo, $prodRepo);
        $res = $uc->execute(1);
        $this->assertCount(1, $res);
    }

    public function test_find_by_product_throws_404_when_product_missing(): void
    {
        $piRepo = $this->createMock(ProductItemRepositoryPort::class);
        $prodRepo = $this->createMock(ProductRepositoryPort::class);
        $prodRepo->method('findById')->with(9)->willReturn(null);

        $uc = new FindProductItemsByProductUseCase($piRepo, $prodRepo);
        $this->expectException(NotFoundHttpException::class);
        $uc->execute(9);
    }
}
