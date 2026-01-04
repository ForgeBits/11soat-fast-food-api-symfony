<?php

namespace App\Tests\Unit\Application\UseCases\Categories;

use App\Application\Domain\Entities\Categories\Entity\Category;
use App\Application\Port\Output\Repositories\CategoryRepositoryPort;
use App\Application\UseCases\Categories\FindCategoryUseCase;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FindCategoryUseCaseTest extends TestCase
{
    public function test_execute_returns_category_when_found(): void
    {
        $repo = $this->createMock(CategoryRepositoryPort::class);

        $cat = (new Category())->setName('Bebidas');
        $repo->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($cat);

        $uc = new FindCategoryUseCase($repo);
        $result = $uc->execute(1);

        $this->assertInstanceOf(Category::class, $result);
        $this->assertSame('Bebidas', $result->getName());
    }

    public function test_execute_throws_not_found_when_missing(): void
    {
        $repo = $this->createMock(CategoryRepositoryPort::class);
        $repo->method('findById')->with(999)->willReturn(null);

        $uc = new FindCategoryUseCase($repo);

        $this->expectException(NotFoundHttpException::class);
        $uc->execute(999);
    }
}
