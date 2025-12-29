<?php

namespace App\Tests\Unit\Application\UseCases\Categories;

use App\Application\Domain\Entities\Categories\Entity\Category;
use App\Application\Port\Output\Repositories\CategoryRepositoryPort;
use App\Application\UseCases\Categories\DeleteCategorytUseCase;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DeleteCategoryUseCaseTest extends TestCase
{
    public function test_execute_deletes_when_found(): void
    {
        $repo = $this->createMock(CategoryRepositoryPort::class);

        $category = new Category();
        $repo->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($category);

        $repo->expects($this->once())
            ->method('delete')
            ->with($category);

        $uc = new DeleteCategorytUseCase($repo);
        $uc->execute(1);

        $this->assertTrue(true);
    }

    public function test_execute_throws_not_found_when_missing(): void
    {
        $repo = $this->createMock(CategoryRepositoryPort::class);
        $repo->method('findById')->with(999)->willReturn(null);

        $uc = new DeleteCategorytUseCase($repo);

        $this->expectException(NotFoundHttpException::class);
        $uc->execute(999);
    }
}
