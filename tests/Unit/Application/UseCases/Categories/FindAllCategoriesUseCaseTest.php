<?php

namespace App\Tests\Unit\Application\UseCases\Categories;

use App\Application\Domain\Dtos\Commons\PaginationQueryDto;
use App\Application\Domain\Entities\Categories\Entity\Category;
use App\Application\Port\Output\Repositories\CategoryRepositoryPort;
use App\Application\UseCases\Categories\FindAllCategoriesUseCase;
use PHPUnit\Framework\TestCase;

class FindAllCategoriesUseCaseTest extends TestCase
{
    public function test_execute_returns_paginated_categories(): void
    {
        $repo = $this->createMock(CategoryRepositoryPort::class);

        $cat1 = (new Category())->setName('Bebidas');
        $cat2 = (new Category())->setName('Lanches');
        $expected = [$cat1, $cat2];

        $repo->expects($this->once())
            ->method('findAllPaginated')
            ->with($this->isArray(), 1, 10)
            ->willReturn($expected);

        $dto = new PaginationQueryDto();
        $dto->page = 1;
        $dto->perPage = 10;

        $uc = new FindAllCategoriesUseCase($repo);
        $result = $uc->execute($dto);

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertInstanceOf(Category::class, $result[0]);
    }
}
