<?php

namespace App\Tests\Unit\Application\UseCases\ProductItem;

use App\Application\Domain\Dtos\ProductItem\CreateProductItemDto;
use App\Application\Domain\Entities\Items\Entity\Item;
use App\Application\Domain\Entities\Products\Entity\Product;
use App\Application\Port\Output\Repositories\ItemRepositoryPort;
use App\Application\Port\Output\Repositories\ProductItemRepositoryPort;
use App\Application\Port\Output\Repositories\ProductRepositoryPort;
use App\Application\UseCases\ProductItem\CreateProductItemUseCase;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CreateProductItemUseCaseTest extends TestCase
{
    private function makeDto(): CreateProductItemDto
    {
        $dto = new CreateProductItemDto();
        $dto->productId = 1;
        $dto->itemId = 2;
        $dto->essential = true;
        $dto->quantity = 2;
        $dto->customizable = false;
        return $dto;
    }

    public function test_execute_success_creates_relation(): void
    {
        $dto = $this->makeDto();

        $piRepo = $this->createMock(ProductItemRepositoryPort::class);
        $prodRepo = $this->createMock(ProductRepositoryPort::class);
        $itemRepo = $this->createMock(ItemRepositoryPort::class);

        $prod = new Product();
        $item = new Item();
        $prodRepo->method('findById')->with($dto->productId)->willReturn($prod);
        $itemRepo->method('findById')->with($dto->itemId)->willReturn($item);
        $piRepo->method('findByProductAndItem')->with($dto->productId, $dto->itemId)->willReturn(null);

        $expected = $this->getMockBuilder(\App\Application\Domain\Entities\ProductItem\Entity\ProductItem::class)
            ->disableOriginalConstructor()->getMock();
        $piRepo->method('create')->with($dto)->willReturn($expected);

        $uc = new CreateProductItemUseCase($piRepo, $prodRepo, $itemRepo);
        $result = $uc->execute($dto);
        $this->assertSame($expected, $result);
    }

    public function test_execute_throws_not_found_when_product_missing(): void
    {
        $dto = $this->makeDto();

        $piRepo = $this->createMock(ProductItemRepositoryPort::class);
        $prodRepo = $this->createMock(ProductRepositoryPort::class);
        $itemRepo = $this->createMock(ItemRepositoryPort::class);

        $prodRepo->method('findById')->with($dto->productId)->willReturn(null);

        $uc = new CreateProductItemUseCase($piRepo, $prodRepo, $itemRepo);
        $this->expectException(NotFoundHttpException::class);
        $uc->execute($dto);
    }

    public function test_execute_throws_not_found_when_item_missing(): void
    {
        $dto = $this->makeDto();

        $piRepo = $this->createMock(ProductItemRepositoryPort::class);
        $prodRepo = $this->createMock(ProductRepositoryPort::class);
        $itemRepo = $this->createMock(ItemRepositoryPort::class);

        $prodRepo->method('findById')->with($dto->productId)->willReturn(new Product());
        $itemRepo->method('findById')->with($dto->itemId)->willReturn(null);

        $uc = new CreateProductItemUseCase($piRepo, $prodRepo, $itemRepo);
        $this->expectException(NotFoundHttpException::class);
        $uc->execute($dto);
    }

    public function test_execute_throws_conflict_when_relation_exists(): void
    {
        $dto = $this->makeDto();

        $piRepo = $this->createMock(ProductItemRepositoryPort::class);
        $prodRepo = $this->createMock(ProductRepositoryPort::class);
        $itemRepo = $this->createMock(ItemRepositoryPort::class);

        $prodRepo->method('findById')->with($dto->productId)->willReturn(new Product());
        $itemRepo->method('findById')->with($dto->itemId)->willReturn(new Item());
        $exists = $this->getMockBuilder(\App\Application\Domain\Entities\ProductItem\Entity\ProductItem::class)
            ->disableOriginalConstructor()->getMock();
        $piRepo->method('findByProductAndItem')->with($dto->productId, $dto->itemId)->willReturn($exists);

        $uc = new CreateProductItemUseCase($piRepo, $prodRepo, $itemRepo);
        $this->expectException(ConflictHttpException::class);
        $uc->execute($dto);
    }
}
