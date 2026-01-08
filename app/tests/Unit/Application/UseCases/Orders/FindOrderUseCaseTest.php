<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\UseCases\Orders;

use App\Application\Domain\Dtos\Orders\CreateOrderDto;
use App\Application\Domain\Dtos\Orders\CreateOrderItemDto;
use App\Application\Domain\Entities\Orders\Enum\OrderStatus;
use App\Application\UseCases\Orders\FindOrderUseCase;
use App\Application\UseCases\Orders\CreateOrderUseCase;
use App\Infrastructure\Test\Doubles\InMemoryItemRepository;
use App\Infrastructure\Test\Doubles\InMemoryOrderRepository;
use App\Infrastructure\Test\Doubles\InMemoryProductItemRepository;
use App\Infrastructure\Test\Doubles\InMemoryProductRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FindOrderUseCaseTest extends TestCase
{
    public function test_success_find_order_by_id(): void
    {
        $orderRepo = new InMemoryOrderRepository();
        $productRepo = new InMemoryProductRepository();
        $itemRepo = new InMemoryItemRepository();
        $piRepo = new InMemoryProductItemRepository($productRepo, $itemRepo);

        // create a simple order first
        $p = new \App\Application\Domain\Entities\Products\Entity\Product();
        $p->setName('P')->setDescription(null)->setAmount(10)->setUrlImg('i')->setCustomizable(false)->setAvailable(true);
        $productRepo->seed($p);

        $dto = new CreateOrderDto();
        $dto->clientId = null;
        $dto->status = OrderStatus::PENDING;
        $dto->amount = 10;
        $dto->transactionId = 'tx-find-1';
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

        $create = new CreateOrderUseCase($orderRepo, $productRepo, $itemRepo, $piRepo);
        $order = $create->execute($dto);

        $useCase = new FindOrderUseCase($orderRepo);
        $found = $useCase->execute((int)$order->getId());

        $this->assertSame($order->getId(), $found->getId());
        $this->assertSame(OrderStatus::PENDING, $found->getStatus());
    }

    public function test_not_found_throws_404(): void
    {
        $orderRepo = new InMemoryOrderRepository();
        $useCase = new FindOrderUseCase($orderRepo);
        $this->expectException(NotFoundHttpException::class);
        $useCase->execute(999);
    }
}
