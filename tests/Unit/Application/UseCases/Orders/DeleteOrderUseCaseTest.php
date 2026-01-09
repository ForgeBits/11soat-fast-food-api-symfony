<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\UseCases\Orders;

use App\Application\Domain\Dtos\Orders\CreateOrderDto;
use App\Application\Domain\Dtos\Orders\CreateOrderItemDto;
use App\Application\Domain\Entities\Orders\Enum\OrderStatus;
use App\Application\UseCases\Orders\CreateOrderUseCase;
use App\Application\UseCases\Orders\DeleteOrderUseCase;
use App\Infrastructure\Test\Doubles\InMemoryItemRepository;
use App\Infrastructure\Test\Doubles\InMemoryOrderRepository;
use App\Infrastructure\Test\Doubles\InMemoryProductItemRepository;
use App\Infrastructure\Test\Doubles\InMemoryProductRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DeleteOrderUseCaseTest extends TestCase
{
    public function test_success_delete_order(): void
    {
        $orderRepo = new InMemoryOrderRepository();
        $productRepo = new InMemoryProductRepository();
        $itemRepo = new InMemoryItemRepository();
        $piRepo = new InMemoryProductItemRepository($productRepo, $itemRepo);

        // seed product and create order
        $p = new \App\Application\Domain\Entities\Products\Entity\Product();
        $p->setName('P')->setDescription(null)->setAmount(10)->setUrlImg('i')->setCustomizable(false)->setAvailable(true);
        $productRepo->seed($p);

        $cDto = new CreateOrderDto();
        $cDto->clientId = null;
        $cDto->status = OrderStatus::PENDING;
        $cDto->amount = 10;
        $cDto->transactionId = 'tx-del-1';
        $cDto->isRandomClient = false;
        $cDto->codeClientRandom = null;
        $cDto->observation = null;
        $cDto->productIds = [];
        $it = new CreateOrderItemDto();
        $it->productId = (int)$p->getId();
        $it->title = 'P';
        $it->quantity = 1;
        $it->price = 10;
        $it->customerItems = [];
        $cDto->items = [$it];
        $create = new CreateOrderUseCase($orderRepo, $productRepo, $itemRepo, $piRepo);
        $order = $create->execute($cDto);

        $useCase = new DeleteOrderUseCase($orderRepo);
        $useCase->execute((int)$order->getId());

        $this->assertNull($orderRepo->findById((int)$order->getId()));
    }

    public function test_not_found_throws_404(): void
    {
        $orderRepo = new InMemoryOrderRepository();
        $useCase = new DeleteOrderUseCase($orderRepo);
        $this->expectException(NotFoundHttpException::class);
        $useCase->execute(999);
    }
}
