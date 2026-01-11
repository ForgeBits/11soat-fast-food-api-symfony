<?php

namespace App\Tests\Unit\Application\UseCases\Categories;

use App\Application\Domain\Dtos\Categories\CreateCategoryDto;
use App\Application\Domain\Entities\Categories\Entity\Category;
use App\Application\Port\Output\Repositories\CategoryRepositoryPort;
use App\Application\UseCases\Categories\CreateCategoryUseCase;
use PHPUnit\Framework\TestCase;

class CreateCategoryUseCaseTest extends TestCase
{
    public function test_execute_success_creates_category(): void
    {
        $dto = new CreateCategoryDto();
        $dto->name = 'Bebidas';
        $dto->description = 'Refrigerantes, sucos e água';

        $repo = $this->createMock(CategoryRepositoryPort::class);

        $created = new Category();
        $created->setName($dto->name)->setDescription($dto->description);

        $repo->expects($this->once())
            ->method('create')
            ->with($dto)
            ->willReturn($created);

        $uc = new CreateCategoryUseCase($repo);
        $result = $uc->execute($dto);

        $this->assertInstanceOf(Category::class, $result);
        $this->assertSame('Bebidas', $result->getName());
    }
}
