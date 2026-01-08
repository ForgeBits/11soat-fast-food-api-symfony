<?php

namespace App\Tests\Unit\Application\UseCases\Products;

use App\Application\Domain\Dtos\Commons\PaginationQueryDto;
use App\Application\Domain\Entities\Products\Entity\Product;
use App\Application\Port\Output\Repositories\ProductRepositoryPort;
use App\Application\UseCases\Products\FindAllProductsUseCase;
use PHPUnit\Framework\TestCase;

class FindAllProductsUseCaseTest extends TestCase
{
    public function test_execute_returns_paginated_products(): void
    {
        $repo = $this->createMock(ProductRepositoryPort::class);

        $p1 = (new Product())->setName('X-Burger');
        $p2 = (new Product())->setName('X-Salada');
        $expected = [$p1, $p2];

        $repo->expects($this->once())
            ->method('findAllPaginated')
            ->with($this->isArray(), 1, 10)
            ->willReturn($expected);

        $dto = new PaginationQueryDto();
        $dto->page = 1;
        $dto->perPage = 10;

        $uc = new FindAllProductsUseCase($repo);
        $result = $uc->execute($dto);

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertInstanceOf(Product::class, $result[0]);
    }
}
