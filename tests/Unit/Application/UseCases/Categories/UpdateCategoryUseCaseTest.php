<?php

namespace App\Tests\Unit\Application\UseCases\Categories;

use App\Application\Domain\Dtos\Categories\UpdateCategoryDto;
use App\Application\Domain\Entities\Categories\Entity\Category;
use App\Application\Port\Output\Repositories\CategoryRepositoryPort;
use App\Application\UseCases\Categories\UpdateCategoryUseCase;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UpdateCategoryUseCaseTest extends TestCase
{
    private function makeDto(): UpdateCategoryDto
    {
        $dto = new UpdateCategoryDto();
        $dto->id = 1;
        $dto->name = 'Bebidas';
        $dto->description = 'Refrigerantes';
        return $dto;
    }

    public function test_execute_success_updates_category(): void
    {
        $dto = $this->makeDto();

        $repo = $this->createMock(CategoryRepositoryPort::class);

        // existing category by id
        $existing = (new Category())->setName('Old');
        $repo->expects($this->once())
            ->method('findById')
            ->with($dto->id)
            ->willReturn($existing);

        // no conflict by name
        $repo->expects($this->once())
            ->method('findByName')
            ->with($dto->name)
            ->willReturn(null);

        $updated = (new Category())
            ->setName($dto->name)
            ->setDescription($dto->description);
        $repo->expects($this->once())
            ->method('update')
            ->with($dto)
            ->willReturn($updated);

        $uc = new UpdateCategoryUseCase($repo);
        $result = $uc->execute($dto);

        $this->assertInstanceOf(Category::class, $result);
        $this->assertSame('Bebidas', $result->getName());
    }

    public function test_execute_throws_not_found_when_category_missing(): void
    {
        $dto = $this->makeDto();

        $repo = $this->createMock(CategoryRepositoryPort::class);
        $repo->method('findById')->with($dto->id)->willReturn(null);

        $uc = new UpdateCategoryUseCase($repo);
        $this->expectException(NotFoundHttpException::class);
        $uc->execute($dto);
    }

    public function test_execute_throws_conflict_when_name_used_by_other(): void
    {
        $dto = $this->makeDto();

        $repo = $this->createMock(CategoryRepositoryPort::class);

        // found by id
        $repo->method('findById')->with($dto->id)->willReturn(new Category());

        // another category with same name and different id
        $other = new Category();
        $ref = new \ReflectionProperty(Category::class, 'id');
        $ref->setAccessible(true);
        $ref->setValue($other, 2);

        $repo->method('findByName')->with($dto->name)->willReturn($other);

        $uc = new UpdateCategoryUseCase($repo);
        $this->expectException(ConflictHttpException::class);
        $uc->execute($dto);
    }
}
