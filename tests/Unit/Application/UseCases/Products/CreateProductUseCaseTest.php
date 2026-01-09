<?php

namespace App\Tests\Unit\Application\UseCases\Products;

use App\Application\Domain\Dtos\Products\CreateProductDto;
use App\Application\Domain\Entities\Categories\Entity\Category;
use App\Application\Domain\Entities\Products\Entity\Product;
use App\Application\Port\Output\Repositories\CategoryRepositoryPort;
use App\Application\Port\Output\Repositories\ProductRepositoryPort;
use App\Application\UseCases\Products\CreateProductUseCase;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CreateProductUseCaseTest extends TestCase
{
    private function makeDto(): CreateProductDto
    {
        $dto = new CreateProductDto();
        $dto->name = 'X-Burger';
        $dto->description = 'Teste';
        $dto->amount = 29.9;
        $dto->url_img = 'https://example.com/x.jpg';
        $dto->customizable = true;
        $dto->available = true;
        $dto->category_id = 1;
        return $dto;
    }

    public function test_execute_success_creates_product(): void
    {
        $dto = $this->makeDto();

        $productRepo = $this->createMock(ProductRepositoryPort::class);
        $categoryRepo = $this->createMock(CategoryRepositoryPort::class);

        $productRepo->expects($this->once())
            ->method('findByName')
            ->with($dto->name)
            ->willReturn(null);

        $category = new Category();
        $category->setName('Lanches');
        $categoryRepo->expects($this->once())
            ->method('findById')
            ->with($dto->category_id)
            ->willReturn($category);

        $created = new Product();
        $created->setName($dto->name)
            ->setDescription($dto->description)
            ->setAmount($dto->amount)
            ->setUrlImg($dto->url_img)
            ->setCustomizable($dto->customizable)
            ->setAvailable($dto->available);

        $productRepo->expects($this->once())
            ->method('create')
            ->with($dto)
            ->willReturn($created);

        $uc = new CreateProductUseCase($productRepo, $categoryRepo);
        $result = $uc->execute($dto);

        $this->assertInstanceOf(Product::class, $result);
        $this->assertSame('X-Burger', $result->getName());
    }

    public function test_execute_throws_conflict_when_name_exists(): void
    {
        $dto = $this->makeDto();

        $productRepo = $this->createMock(ProductRepositoryPort::class);
        $categoryRepo = $this->createMock(CategoryRepositoryPort::class);

        $productRepo->method('findByName')->with($dto->name)->willReturn(new Product());

        $uc = new CreateProductUseCase($productRepo, $categoryRepo);

        $this->expectException(ConflictHttpException::class);
        $uc->execute($dto);
    }

    public function test_execute_throws_not_found_when_category_missing(): void
    {
        $dto = $this->makeDto();

        $productRepo = $this->createMock(ProductRepositoryPort::class);
        $categoryRepo = $this->createMock(CategoryRepositoryPort::class);

        $productRepo->method('findByName')->with($dto->name)->willReturn(null);
        $categoryRepo->method('findById')->with($dto->category_id)->willReturn(null);

        $uc = new CreateProductUseCase($productRepo, $categoryRepo);

        $this->expectException(NotFoundHttpException::class);
        $uc->execute($dto);
    }
}
