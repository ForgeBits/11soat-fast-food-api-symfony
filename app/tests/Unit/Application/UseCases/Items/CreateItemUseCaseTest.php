<?php

namespace App\Tests\Unit\Application\UseCases\Items;

use App\Application\Domain\Dtos\Items\CreateItemDto;
use App\Application\Domain\Entities\Items\Entity\Item;
use App\Application\Port\Output\Repositories\ItemRepositoryPort;
use App\Application\UseCases\Items\CreateItemUseCase;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class CreateItemUseCaseTest extends TestCase
{
    private function makeDto(): CreateItemDto
    {
        $dto = new CreateItemDto();
        $dto->name = 'Batata Frita Média';
        $dto->description = 'Porção crocante';
        $dto->price = 14.9;
        $dto->url_img = 'https://example.com/img.jpg';
        $dto->available = true;
        return $dto;
    }

    public function test_execute_success_creates_item(): void
    {
        $dto = $this->makeDto();

        $repo = $this->createMock(ItemRepositoryPort::class);
        // no item with same name
        $repo->expects($this->once())
            ->method('findByName')
            ->with($dto->name)
            ->willReturn(null);

        $created = (new Item())
            ->setName($dto->name)
            ->setDescription($dto->description)
            ->setPrice($dto->price)
            ->setUrlImg($dto->url_img)
            ->setAvailable($dto->available);

        $repo->expects($this->once())
            ->method('create')
            ->with($dto)
            ->willReturn($created);

        $uc = new CreateItemUseCase($repo);
        $result = $uc->execute($dto);

        $this->assertInstanceOf(Item::class, $result);
        $this->assertSame('Batata Frita Média', $result->getName());
    }

    public function test_execute_throws_conflict_when_name_in_use(): void
    {
        $dto = $this->makeDto();

        $repo = $this->createMock(ItemRepositoryPort::class);
        $existing = new Item();
        $existing->setName($dto->name)->setPrice(10)->setUrlImg('https://x');

        $repo->method('findByName')->with($dto->name)->willReturn($existing);

        $uc = new CreateItemUseCase($repo);
        $this->expectException(ConflictHttpException::class);
        $uc->execute($dto);
    }
}
