<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\UseCases\Orders;

use App\Application\Domain\Dtos\Commons\PaginationQueryDto;
use App\Application\Domain\Dtos\Orders\CreateOrderDto;
use App\Application\Domain\Dtos\Orders\CreateOrderItemDto;
use App\Application\Domain\Entities\Orders\Enum\OrderStatus;
use App\Application\UseCases\Orders\CreateOrderUseCase;
use App\Application\UseCases\Orders\FindAllOrdersUseCase;
use App\Infrastructure\Test\Doubles\InMemoryItemRepository;
use App\Infrastructure\Test\Doubles\InMemoryOrderRepository;
use App\Infrastructure\Test\Doubles\InMemoryProductItemRepository;
use App\Infrastructure\Test\Doubles\InMemoryProductRepository;
use PHPUnit\Framework\TestCase;

class FindAllOrdersUseCaseTest extends TestCase
{
    public function test_returns_paged_orders_array(): void
    {
        $orderRepo = new InMemoryOrderRepository();
        $productRepo = new InMemoryProductRepository();
        $itemRepo = new InMemoryItemRepository();
        $piRepo = new InMemoryProductItemRepository($productRepo, $itemRepo);

        // seed a product
        $p = new \App\Application\Domain\Entities\Products\Entity\Product();
        $p->setName('P')->setDescription(null)->setAmount(10)->setUrlImg('i')->setCustomizable(false)->setAvailable(true);
        $productRepo->seed($p);

        // create 3 orders
        $create = new CreateOrderUseCase($orderRepo, $productRepo, $itemRepo, $piRepo);
        for ($i = 0; $i < 3; $i++) {
            $dto = new CreateOrderDto();
            $dto->clientId = null;
            $dto->status = OrderStatus::PENDING;
            $dto->amount = 10;
            $dto->transactionId = 'tx-list-'.$i;
            $dto->isRandomClient = false;
            $dto->codeClientRandom = null;
            $dto->observation = null;
            $dto->productIds = [];
            $it = new CreateOrderItemDto();
            $it->productId = (int)$p->getId();
            $it->title = 'P';
            $it->quantity = 1;
            $it->price = 10;
            $it->customerItems = [];
            $dto->items = [$it];
            $create->execute($dto);
        }

        $useCase = new FindAllOrdersUseCase($orderRepo);
        $page = new PaginationQueryDto();
        $page->page = 1;
        $page->perPage = 10;
        $result = $useCase->execute($page);

        $this->assertIsArray($result);
        $this->assertCount(3, $result);
    }
}
