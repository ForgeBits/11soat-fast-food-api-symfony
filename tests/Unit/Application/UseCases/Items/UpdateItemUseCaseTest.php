<?php

namespace App\Tests\Unit\Application\UseCases\Items;

use App\Application\Domain\Dtos\Items\UpdateItemDto;
use App\Application\Domain\Entities\Items\Entity\Item;
use App\Application\Port\Output\Repositories\ItemRepositoryPort;
use App\Application\UseCases\Items\UpdateItemUseCase;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UpdateItemUseCaseTest extends TestCase
{
    private function makeDto(): UpdateItemDto
    {
        $dto = new UpdateItemDto();
        $dto->id = 1;
        $dto->name = 'Batata';
        $dto->description = 'Crocante';
        $dto->price = 12.5;
        $dto->url_img = 'https://example.com/i.jpg';
        $dto->available = true;
        return $dto;
    }

    public function test_execute_success(): void
    {
        $dto = $this->makeDto();
        $repo = $this->createMock(ItemRepositoryPort::class);

        $existing = new Item();
        $repo->method('findById')->with($dto->id)->willReturn($existing);
        $repo->method('findByName')->with($dto->name)->willReturn(null);

        $updated = (new Item())
            ->setName($dto->name)
            ->setDescription($dto->description)
            ->setPrice($dto->price)
            ->setUrlImg($dto->url_img)
            ->setAvailable($dto->available);
        $repo->method('update')->with($dto)->willReturn($updated);

        $uc = new UpdateItemUseCase($repo);
        $result = $uc->execute($dto);

        $this->assertInstanceOf(Item::class, $result);
        $this->assertSame('Batata', $result->getName());
    }

    public function test_execute_throws_not_found_when_item_missing(): void
    {
        $dto = $this->makeDto();
        $repo = $this->createMock(ItemRepositoryPort::class);
        $repo->method('findById')->with($dto->id)->willReturn(null);

        $uc = new UpdateItemUseCase($repo);
        $this->expectException(NotFoundHttpException::class);
        $uc->execute($dto);
    }

    public function test_execute_throws_conflict_when_name_in_use_by_other(): void
    {
        $dto = $this->makeDto();
        $repo = $this->createMock(ItemRepositoryPort::class);

        $current = new Item();
        $repo->method('findById')->with($dto->id)->willReturn($current);

        $other = new Item();
        $ref = new \ReflectionProperty(Item::class, 'id');
        $ref->setAccessible(true);
        $ref->setValue($other, 2);
        $repo->method('findByName')->with($dto->name)->willReturn($other);

        $uc = new UpdateItemUseCase($repo);
        $this->expectException(ConflictHttpException::class);
        $uc->execute($dto);
    }
}
