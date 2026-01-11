<?php

namespace App\Tests\Unit\Application\UseCases\Items;

use App\Application\Domain\Dtos\Commons\PaginationQueryDto;
use App\Application\Domain\Entities\Items\Entity\Item;
use App\Application\Port\Output\Repositories\ItemRepositoryPort;
use App\Application\UseCases\Items\FindAllItemsUseCase;
use PHPUnit\Framework\TestCase;

class FindAllItemsUseCaseTest extends TestCase
{
    public function test_find_all_returns_array(): void
    {
        $repo = $this->createMock(ItemRepositoryPort::class);
        $repo->method('findAllPaginated')->willReturn([new Item()]);

        $uc = new FindAllItemsUseCase($repo);
        $res = $uc->execute(new PaginationQueryDto());

        $this->assertIsArray($res);
        $this->assertCount(1, $res);
        $this->assertInstanceOf(Item::class, $res[0]);
    }
}
