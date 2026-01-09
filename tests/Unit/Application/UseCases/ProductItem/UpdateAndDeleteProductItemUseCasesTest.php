<?php

namespace App\Tests\Unit\Application\UseCases\ProductItem;

use App\Application\Domain\Dtos\ProductItem\UpdateProductItemDto;
use App\Application\Domain\Entities\ProductItem\Entity\ProductItem;
use App\Application\Port\Output\Repositories\ProductItemRepositoryPort;
use App\Application\UseCases\ProductItem\DeleteProductItemUseCase;
use App\Application\UseCases\ProductItem\UpdateProductItemUseCase;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UpdateAndDeleteProductItemUseCasesTest extends TestCase
{
    public function test_update_success(): void
    {
        $repo = $this->createMock(ProductItemRepositoryPort::class);
        $existing = $this->getMockBuilder(ProductItem::class)->disableOriginalConstructor()->getMock();
        $repo->method('findById')->with(1)->willReturn($existing);

        $dto = new UpdateProductItemDto();
        $dto->id = 1;
        $dto->essential = false;
        $dto->quantity = 3;
        $dto->customizable = true;

        $updated = $this->getMockBuilder(ProductItem::class)->disableOriginalConstructor()->getMock();
        $repo->method('update')->with($dto)->willReturn($updated);

        $uc = new UpdateProductItemUseCase($repo);
        $this->assertSame($updated, $uc->execute($dto));
    }

    public function test_update_throws_404_when_missing(): void
    {
        $repo = $this->createMock(ProductItemRepositoryPort::class);
        $repo->method('findById')->with(999)->willReturn(null);
        $dto = new UpdateProductItemDto();
        $dto->id = 999;
        $dto->essential = true;
        $dto->quantity = 1;
        $dto->customizable = false;

        $uc = new UpdateProductItemUseCase($repo);
        $this->expectException(NotFoundHttpException::class);
        $uc->execute($dto);
    }

    public function test_delete_success(): void
    {
        $repo = $this->createMock(ProductItemRepositoryPort::class);
        $existing = $this->getMockBuilder(ProductItem::class)->disableOriginalConstructor()->getMock();
        $repo->method('findById')->with(1)->willReturn($existing);
        $repo->expects($this->once())->method('delete')->with($existing);

        $uc = new DeleteProductItemUseCase($repo);
        $uc->execute(1);
        $this->assertTrue(true);
    }

    public function test_delete_throws_404_when_missing(): void
    {
        $repo = $this->createMock(ProductItemRepositoryPort::class);
        $repo->method('findById')->with(2)->willReturn(null);
        $uc = new DeleteProductItemUseCase($repo);
        $this->expectException(NotFoundHttpException::class);
        $uc->execute(2);
    }
}
