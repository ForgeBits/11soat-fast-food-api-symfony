<?php

namespace App\Tests\Unit\Application\UseCases\Items;

use App\Application\Domain\Entities\Items\Entity\Item;
use App\Application\Port\Output\Repositories\ItemRepositoryPort;
use App\Application\UseCases\Items\FindItemUseCase;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FindItemUseCaseTest extends TestCase
{
    public function test_find_item_success(): void
    {
        $repo = $this->createMock(ItemRepositoryPort::class);
        $item = new Item();
        $repo->method('findById')->with(1)->willReturn($item);

        $uc = new FindItemUseCase($repo);
        $this->assertSame($item, $uc->execute(1));
    }

    public function test_find_item_throws_404(): void
    {
        $repo = $this->createMock(ItemRepositoryPort::class);
        $repo->method('findById')->with(2)->willReturn(null);

        $uc = new FindItemUseCase($repo);
        $this->expectException(NotFoundHttpException::class);
        $uc->execute(2);
    }
}
