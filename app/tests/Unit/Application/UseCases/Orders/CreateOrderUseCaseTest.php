<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\UseCases\Orders;

use App\Application\Domain\Dtos\Orders\CreateOrderDto;
use App\Application\Domain\Dtos\Orders\CreateOrderItemCustomizationDto;
use App\Application\Domain\Dtos\Orders\CreateOrderItemDto;
use App\Application\Domain\Entities\Orders\Enum\OrderStatus;
use App\Application\UseCases\Orders\CreateOrderUseCase;
use App\Infrastructure\Test\Doubles\InMemoryItemRepository;
use App\Infrastructure\Test\Doubles\InMemoryOrderRepository;
use App\Infrastructure\Test\Doubles\InMemoryProductItemRepository;
use App\Infrastructure\Test\Doubles\InMemoryProductRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CreateOrderUseCaseTest extends TestCase
{
    private InMemoryOrderRepository $orderRepo;
    private InMemoryProductRepository $productRepo;
    private InMemoryItemRepository $itemRepo;
    private InMemoryProductItemRepository $productItemRepo;

    protected function setUp(): void
    {
        $this->orderRepo = new InMemoryOrderRepository();
        $this->productRepo = new InMemoryProductRepository();
        $this->itemRepo = new InMemoryItemRepository();
        $this->productItemRepo = new InMemoryProductItemRepository($this->productRepo, $this->itemRepo);
    }

    public function test_success_create_with_customizations(): void
    {
        // Seed product (customizable) and items + relation customizable
        $product = new \App\Application\Domain\Entities\Products\Entity\Product();
        $product->setName('X-Burger')->setDescription(null)->setAmount(29.9)->setUrlImg('img')->setCustomizable(true)->setAvailable(true);
        $this->productRepo->seed($product);

        $item = new \App\Application\Domain\Entities\Items\Entity\Item();
        $item->setName('Alface')->setDescription(null)->setPrice(1.5)->setUrlImg('img')->setAvailable(true);
        $this->itemRepo->seed($item);

        // Make relation customizable between product and item
        $relDto = new \App\Application\Domain\Dtos\ProductItem\CreateProductItemDto();
        $relDto->productId = $product->getId();
        $relDto->itemId = $item->getId();
        $relDto->essential = false;
        $relDto->quantity = 1;
        $relDto->customizable = true;
        $this->productItemRepo->create($relDto);

        $dto = new CreateOrderDto();
        $dto->clientId = 123;
        $dto->status = OrderStatus::PENDING;
        $dto->amount = 31.4;
        $dto->transactionId = 'tx-1';
        $dto->isRandomClient = false;
        $dto->codeClientRandom = null;
        $dto->observation = null;
        $dto->productIds = [];

        $it = new CreateOrderItemDto();
        $it->productId = (int)$product->getId();
        $it->title = 'X-Burger';
        $it->quantity = 1;
        $it->price = 29.9;
        $it->observation = null;

        $cz = new CreateOrderItemCustomizationDto();
        $cz->itemId = (int)$item->getId();
        $cz->title = 'Alface extra';
        $cz->quantity = 1;
        $cz->price = 1.5;
        $cz->observation = null;
        $it->customerItems = [$cz];

        $dto->items = [$it];

        $useCase = new CreateOrderUseCase($this->orderRepo, $this->productRepo, $this->itemRepo, $this->productItemRepo);
        $order = $useCase->execute($dto);

        $this->assertNotNull($order->getId());
        $this->assertSame(OrderStatus::PENDING, $order->getStatus());
        $this->assertCount(1, $order->getItems());
        $this->assertCount(1, $order->getItems()->first()->getCustomerItems());
    }

    public function test_fails_when_product_not_found(): void
    {
        $dto = new CreateOrderDto();
        $dto->clientId = null;
        $dto->status = OrderStatus::PENDING;
        $dto->amount = 0;
        $dto->transactionId = 'tx-2';
        $dto->isRandomClient = true;
        $dto->codeClientRandom = 100;
        $dto->observation = null;
        $dto->productIds = [];

        $it = new CreateOrderItemDto();
        $it->productId = 999; // inexistente
        $it->title = 'X';
        $it->quantity = 1;
        $it->price = 1.0;
        $it->customerItems = [];
        $dto->items = [$it];

        $useCase = new CreateOrderUseCase($this->orderRepo, $this->productRepo, $this->itemRepo, $this->productItemRepo);
        $this->expectException(NotFoundHttpException::class);
        $useCase->execute($dto);
    }

    public function test_fails_when_product_is_not_customizable_but_has_customizations(): void
    {
        // Seed product NOT customizable
        $product = new \App\Application\Domain\Entities\Products\Entity\Product();
        $product->setName('X')->setDescription(null)->setAmount(10)->setUrlImg('i')->setCustomizable(false)->setAvailable(true);
        $this->productRepo->seed($product);

        $dto = new CreateOrderDto();
        $dto->clientId = null;
        $dto->status = OrderStatus::PENDING;
        $dto->amount = 0;
        $dto->transactionId = 'tx-3';
        $dto->isRandomClient = true;
        $dto->codeClientRandom = 1;
        $dto->observation = null;
        $dto->productIds = [];

        $it = new CreateOrderItemDto();
        $it->productId = (int)$product->getId();
        $it->title = 'X';
        $it->quantity = 1;
        $it->price = 10.0;
        $cz = new CreateOrderItemCustomizationDto();
        $cz->itemId = 1;
        $cz->title = 'Algo';
        $cz->quantity = 1;
        $cz->price = 1.0;
        $it->customerItems = [$cz];
        $dto->items = [$it];

        $useCase = new CreateOrderUseCase($this->orderRepo, $this->productRepo, $this->itemRepo, $this->productItemRepo);
        $this->expectException(BadRequestHttpException::class);
        $useCase->execute($dto);
    }

    public function test_fails_when_customization_item_not_found(): void
    {
        // Seed customizable product
        $product = new \App\Application\Domain\Entities\Products\Entity\Product();
        $product->setName('X')->setDescription(null)->setAmount(10)->setUrlImg('i')->setCustomizable(true)->setAvailable(true);
        $this->productRepo->seed($product);

        $dto = new CreateOrderDto();
        $dto->clientId = null;
        $dto->status = OrderStatus::PENDING;
        $dto->amount = 0;
        $dto->transactionId = 'tx-4';
        $dto->isRandomClient = false;
        $dto->codeClientRandom = null;
        $dto->observation = null;
        $dto->productIds = [];

        $it = new CreateOrderItemDto();
        $it->productId = (int)$product->getId();
        $it->title = 'X';
        $it->quantity = 1;
        $it->price = 10.0;
        $cz = new CreateOrderItemCustomizationDto();
        $cz->itemId = 999; // item inexistente
        $cz->title = 'Alface extra';
        $cz->quantity = 1;
        $cz->price = 1.0;
        $it->customerItems = [$cz];
        $dto->items = [$it];

        $useCase = new CreateOrderUseCase($this->orderRepo, $this->productRepo, $this->itemRepo, $this->productItemRepo);
        $this->expectException(NotFoundHttpException::class);
        $useCase->execute($dto);
    }

    public function test_fails_when_customization_not_allowed_by_relation(): void
    {
        // Seed customizable product and an item, but relation not present
        $product = new \App\Application\Domain\Entities\Products\Entity\Product();
        $product->setName('X')->setDescription(null)->setAmount(10)->setUrlImg('i')->setCustomizable(true)->setAvailable(true);
        $this->productRepo->seed($product);

        $item = new \App\Application\Domain\Entities\Items\Entity\Item();
        $item->setName('Alface')->setDescription(null)->setPrice(1.5)->setUrlImg('img')->setAvailable(true);
        $this->itemRepo->seed($item);

        // Do NOT create ProductItem relation => should fail
        $dto = new CreateOrderDto();
        $dto->clientId = null;
        $dto->status = OrderStatus::PENDING;
        $dto->amount = 0;
        $dto->transactionId = 'tx-5';
        $dto->isRandomClient = false;
        $dto->codeClientRandom = null;
        $dto->observation = null;
        $dto->productIds = [];

        $it = new CreateOrderItemDto();
        $it->productId = (int)$product->getId();
        $it->title = 'X';
        $it->quantity = 1;
        $it->price = 10.0;
        $cz = new CreateOrderItemCustomizationDto();
        $cz->itemId = (int)$item->getId();
        $cz->title = 'Alface extra';
        $cz->quantity = 1;
        $cz->price = 1.0;
        $it->customerItems = [$cz];
        $dto->items = [$it];

        $useCase = new CreateOrderUseCase($this->orderRepo, $this->productRepo, $this->itemRepo, $this->productItemRepo);
        $this->expectException(BadRequestHttpException::class);
        $useCase->execute($dto);
    }

    public function test_fails_when_invalid_quantities(): void
    {
        $product = new \App\Application\Domain\Entities\Products\Entity\Product();
        $product->setName('X')->setDescription(null)->setAmount(10)->setUrlImg('i')->setCustomizable(true)->setAvailable(true);
        $this->productRepo->seed($product);

        $item = new \App\Application\Domain\Entities\Items\Entity\Item();
        $item->setName('Alface')->setDescription(null)->setPrice(1.5)->setUrlImg('img')->setAvailable(true);
        $this->itemRepo->seed($item);

        // allow relation
        $relDto = new \App\Application\Domain\Dtos\ProductItem\CreateProductItemDto();
        $relDto->productId = $product->getId();
        $relDto->itemId = $item->getId();
        $relDto->essential = false;
        $relDto->quantity = 1;
        $relDto->customizable = true;
        $this->productItemRepo->create($relDto);

        $dto = new CreateOrderDto();
        $dto->clientId = null;
        $dto->status = OrderStatus::PENDING;
        $dto->amount = 0;
        $dto->transactionId = 'tx-6';
        $dto->isRandomClient = false;
        $dto->codeClientRandom = null;
        $dto->observation = null;
        $dto->productIds = [];

        $it = new CreateOrderItemDto();
        $it->productId = (int)$product->getId();
        $it->title = 'X';
        $it->quantity = 0; // inválido
        $it->price = 10.0;
        $cz = new CreateOrderItemCustomizationDto();
        $cz->itemId = (int)$item->getId();
        $cz->title = 'Alface extra';
        $cz->quantity = 1;
        $cz->price = 1.0;
        $it->customerItems = [$cz];
        $dto->items = [$it];

        $useCase = new CreateOrderUseCase($this->orderRepo, $this->productRepo, $this->itemRepo, $this->productItemRepo);
        $this->expectException(BadRequestHttpException::class);
        $useCase->execute($dto);
    }
}
