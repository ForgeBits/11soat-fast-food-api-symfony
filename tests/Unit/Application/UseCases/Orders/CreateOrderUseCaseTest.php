<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\UseCases\Orders;

use App\Application\Domain\Dtos\Orders\CreateOrderDto;
use App\Application\Domain\Dtos\Orders\CreateOrderItemCustomizationDto;
use App\Application\Domain\Dtos\Orders\CreateOrderItemDto;
use App\Application\Domain\Entities\Items\Entity\Item;
use App\Application\Domain\Entities\Orders\Entity\Order;
use App\Application\Domain\Entities\ProductItem\Entity\ProductItem;
use App\Application\Domain\Entities\Products\Entity\Product;
use App\Application\Port\Output\Repositories\ItemRepositoryPort;
use App\Application\Port\Output\Repositories\OrderRepositoryPort;
use App\Application\Port\Output\Repositories\ProductItemRepositoryPort;
use App\Application\Port\Output\Repositories\ProductRepositoryPort;
use App\Application\UseCases\Orders\CreateOrderUseCase;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CreateOrderUseCaseTest extends TestCase
{
    private function makeDto(int $productId, int $itemId, int $quantity = 1, int $customQty = 1): CreateOrderDto
    {
        $cDto = new CreateOrderItemCustomizationDto();
        $cDto->itemId = $itemId;
        $cDto->title = 'Extra queijo';
        $cDto->quantity = $customQty;
        $cDto->price = 1.25;
        $cDto->observation = null;

        $iDto = new CreateOrderItemDto();
        $iDto->productId = $productId;
        $iDto->title = 'Produto X';
        $iDto->quantity = $quantity;
        $iDto->price = 10.00;
        $iDto->observation = null;
        $iDto->customerItems = [$cDto];

        $dto = new CreateOrderDto();
        $dto->amount = 10.00;
        $dto->productIds = [$productId];
        $dto->items = [$iDto];
        return $dto;
    }

    public function testExecuteHappyPathCreatesOrder(): void
    {
        // Arrange DTO
        $dto = $this->makeDto(productId: 100, itemId: 200);

        // Domain entities for stubs
        $product = (new Product())
            ->setName('Produto')
            ->setAmount(10.00)
            ->setUrlImg('p.png')
            ->setAvailable(true)
            ->setCustomizable(true);

        $item = (new Item())
            ->setName('Queijo')
            ->setDescription('Extra')
            ->setPrice(1.25)
            ->setUrlImg('i.png')
            ->setAvailable(true);

        $relation = (new ProductItem($product, $item))
            ->setCustomizable(true);

        // Mocks
        $orderRepository = $this->createMock(OrderRepositoryPort::class);
        $productRepository = $this->createMock(ProductRepositoryPort::class);
        $itemRepository = $this->createMock(ItemRepositoryPort::class);
        $productItemRepository = $this->createMock(ProductItemRepositoryPort::class);

        $expectedOrder = new Order();

        $orderRepository
            ->expects($this->once())
            ->method('create')
            ->with($dto)
            ->willReturn($expectedOrder);

        $productRepository
            ->method('findById')
            ->willReturn($product);

        $itemRepository
            ->method('findById')
            ->willReturn($item);

        $productItemRepository
            ->method('findByProductAndItem')
            ->willReturn($relation);

        $useCase = new CreateOrderUseCase(
            orderRepository: $orderRepository,
            productRepository: $productRepository,
            itemRepository: $itemRepository,
            productItemRepository: $productItemRepository,
        );

        // Act
        $result = $useCase->execute($dto);

        // Assert
        $this->assertSame($expectedOrder, $result);
    }

    public function testExecuteThrowsWhenProductIsNotCustomizableButHasCustomizations(): void
    {
        $dto = $this->makeDto(productId: 10, itemId: 20);

        $product = (new Product())
            ->setName('Produto')
            ->setAmount(10.00)
            ->setUrlImg('p.png')
            ->setAvailable(true)
            ->setCustomizable(false); // not customizable

        $orderRepository = $this->createMock(OrderRepositoryPort::class);
        $productRepository = $this->createMock(ProductRepositoryPort::class);
        $itemRepository = $this->createMock(ItemRepositoryPort::class);
        $productItemRepository = $this->createMock(ProductItemRepositoryPort::class);

        $productRepository->method('findById')->willReturn($product);

        $useCase = new CreateOrderUseCase(
            orderRepository: $orderRepository,
            productRepository: $productRepository,
            itemRepository: $itemRepository,
            productItemRepository: $productItemRepository,
        );

        $this->expectException(BadRequestHttpException::class);
        $useCase->execute($dto);
    }

    public function testExecuteThrowsWhenItemQuantityIsInvalid(): void
    {
        $dto = $this->makeDto(productId: 1, itemId: 2, quantity: 0); // invalid quantity

        $orderRepository = $this->createMock(OrderRepositoryPort::class);
        $productRepository = $this->createMock(ProductRepositoryPort::class);
        $itemRepository = $this->createMock(ItemRepositoryPort::class);
        $productItemRepository = $this->createMock(ProductItemRepositoryPort::class);

        $useCase = new CreateOrderUseCase(
            orderRepository: $orderRepository,
            productRepository: $productRepository,
            itemRepository: $itemRepository,
            productItemRepository: $productItemRepository,
        );

        $this->expectException(BadRequestHttpException::class);
        $useCase->execute($dto);
    }

    public function testExecuteThrowsWhenCustomizationQuantityIsInvalid(): void
    {
        $dto = $this->makeDto(productId: 1, itemId: 2, quantity: 1, customQty: 0); // invalid customization qty

        $orderRepository = $this->createMock(OrderRepositoryPort::class);
        $productRepository = $this->createMock(ProductRepositoryPort::class);
        $itemRepository = $this->createMock(ItemRepositoryPort::class);
        $productItemRepository = $this->createMock(ProductItemRepositoryPort::class);

        $useCase = new CreateOrderUseCase(
            orderRepository: $orderRepository,
            productRepository: $productRepository,
            itemRepository: $itemRepository,
            productItemRepository: $productItemRepository,
        );

        $this->expectException(BadRequestHttpException::class);
        $useCase->execute($dto);
    }

    public function testExecuteThrowsWhenProductNotFound(): void
    {
        $dto = $this->makeDto(productId: 9, itemId: 99);

        $orderRepository = $this->createMock(OrderRepositoryPort::class);
        $productRepository = $this->createMock(ProductRepositoryPort::class);
        $itemRepository = $this->createMock(ItemRepositoryPort::class);
        $productItemRepository = $this->createMock(ProductItemRepositoryPort::class);

        $productRepository->method('findById')->willReturn(null);

        $useCase = new CreateOrderUseCase(
            orderRepository: $orderRepository,
            productRepository: $productRepository,
            itemRepository: $itemRepository,
            productItemRepository: $productItemRepository,
        );

        $this->expectException(NotFoundHttpException::class);
        $useCase->execute($dto);
    }

    public function testExecuteThrowsWhenCustomizationItemNotFound(): void
    {
        $dto = $this->makeDto(productId: 5, itemId: 6);

        $product = (new Product())
            ->setName('Produto')
            ->setAmount(10.00)
            ->setUrlImg('p.png')
            ->setAvailable(true)
            ->setCustomizable(true);

        $orderRepository = $this->createMock(OrderRepositoryPort::class);
        $productRepository = $this->createMock(ProductRepositoryPort::class);
        $itemRepository = $this->createMock(ItemRepositoryPort::class);
        $productItemRepository = $this->createMock(ProductItemRepositoryPort::class);

        $productRepository->method('findById')->willReturn($product);
        $itemRepository->method('findById')->willReturn(null); // item not found

        $useCase = new CreateOrderUseCase(
            orderRepository: $orderRepository,
            productRepository: $productRepository,
            itemRepository: $itemRepository,
            productItemRepository: $productItemRepository,
        );

        $this->expectException(NotFoundHttpException::class);
        $useCase->execute($dto);
    }

    public function testExecuteThrowsWhenRelationMissing(): void
    {
        $dto = $this->makeDto(productId: 7, itemId: 8);

        $product = (new Product())
            ->setName('Produto')
            ->setAmount(10.00)
            ->setUrlImg('p.png')
            ->setAvailable(true)
            ->setCustomizable(true);

        $item = (new Item())
            ->setName('Queijo')
            ->setDescription('Extra')
            ->setPrice(1.25)
            ->setUrlImg('i.png')
            ->setAvailable(true);

        $orderRepository = $this->createMock(OrderRepositoryPort::class);
        $productRepository = $this->createMock(ProductRepositoryPort::class);
        $itemRepository = $this->createMock(ItemRepositoryPort::class);
        $productItemRepository = $this->createMock(ProductItemRepositoryPort::class);

        $productRepository->method('findById')->willReturn($product);
        $itemRepository->method('findById')->willReturn($item);
        $productItemRepository->method('findByProductAndItem')->willReturn(null); // missing relation

        $useCase = new CreateOrderUseCase(
            orderRepository: $orderRepository,
            productRepository: $productRepository,
            itemRepository: $itemRepository,
            productItemRepository: $productItemRepository,
        );

        $this->expectException(BadRequestHttpException::class);
        $useCase->execute($dto);
    }

    public function testExecuteThrowsWhenRelationIsNotCustomizable(): void
    {
        $dto = $this->makeDto(productId: 11, itemId: 22);

        $product = (new Product())
            ->setName('Produto')
            ->setAmount(10.00)
            ->setUrlImg('p.png')
            ->setAvailable(true)
            ->setCustomizable(true);

        $item = (new Item())
            ->setName('Queijo')
            ->setDescription('Extra')
            ->setPrice(1.25)
            ->setUrlImg('i.png')
            ->setAvailable(true);

        $relation = (new ProductItem($product, $item))
            ->setCustomizable(false); // relation not customizable

        $orderRepository = $this->createMock(OrderRepositoryPort::class);
        $productRepository = $this->createMock(ProductRepositoryPort::class);
        $itemRepository = $this->createMock(ItemRepositoryPort::class);
        $productItemRepository = $this->createMock(ProductItemRepositoryPort::class);

        $productRepository->method('findById')->willReturn($product);
        $itemRepository->method('findById')->willReturn($item);
        $productItemRepository->method('findByProductAndItem')->willReturn($relation);

        $useCase = new CreateOrderUseCase(
            orderRepository: $orderRepository,
            productRepository: $productRepository,
            itemRepository: $itemRepository,
            productItemRepository: $productItemRepository,
        );

        $this->expectException(BadRequestHttpException::class);
        $useCase->execute($dto);
    }
}
