<?php

namespace App\Tests\Unit\Application\UseCases\ProductItem;

use App\Application\Domain\Entities\ProductItem\Entity\ProductItem;
use App\Application\Port\Output\Repositories\ProductItemRepositoryPort;
use App\Application\UseCases\ProductItem\FindProductItemUseCase;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FindProductItemUseCaseTest extends TestCase
{
    public function test_execute_returns_entity_when_found(): void
    {
        $repo = $this->createMock(ProductItemRepositoryPort::class);
        $entity = $this->getMockBuilder(ProductItem::class)->disableOriginalConstructor()->getMock();
        $repo->method('findById')->with(10)->willReturn($entity);

        $uc = new FindProductItemUseCase($repo);
        $this->assertSame($entity, $uc->execute(10));
    }

    public function test_execute_throws_404_when_missing(): void
    {
        $repo = $this->createMock(ProductItemRepositoryPort::class);
        $repo->method('findById')->with(10)->willReturn(null);
        $uc = new FindProductItemUseCase($repo);
        $this->expectException(NotFoundHttpException::class);
        $uc->execute(10);
    }
}
