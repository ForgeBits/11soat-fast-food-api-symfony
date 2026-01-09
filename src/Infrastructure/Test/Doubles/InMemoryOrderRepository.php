<?php

namespace App\Infrastructure\Test\Doubles;

use App\Application\Domain\Dtos\Orders\CreateOrderDto;
use App\Application\Domain\Dtos\Orders\UpdateOrderDto;
use App\Application\Domain\Entities\Items\Entity\Item;
use App\Application\Domain\Entities\Orders\Entity\Order;
use App\Application\Domain\Entities\Orders\Entity\OrderItem;
use App\Application\Domain\Entities\Orders\Entity\OrderItemCustomization;
use App\Application\Domain\Entities\Orders\Enum\OrderStatus;
use App\Application\Domain\Entities\Products\Entity\Product;
use App\Application\Port\Output\Repositories\OrderRepositoryPort;

/**
 * In-memory repository for Order module used in unit/feature tests.
 */
class InMemoryOrderRepository implements OrderRepositoryPort
{
    /** @var array<int, Order> */
    private array $storage = [];
    private int $autoId = 1;
    private int $autoOrderItemId = 1;
    private int $autoCustomizationId = 1;

    public function reset(): void
    {
        $this->storage = [];
        $this->autoId = 1;
        $this->autoOrderItemId = 1;
        $this->autoCustomizationId = 1;
    }

    public function create(CreateOrderDto $dto): Order
    {
        $order = new Order();
        $order
            ->setClientId($dto->clientId)
            ->setStatus($dto->status)
            ->setAmount($dto->amount)
            ->setTransactionId($dto->transactionId)
            ->setIsRandomClient($dto->isRandomClient)
            ->setCodeClientRandom($dto->codeClientRandom)
            ->setObservation($dto->observation);

        // assign ID via reflection
        $this->assignOrderId($order);

        // legacy products collection (optional)
        foreach ($dto->productIds as $pid) {
            $product = new Product();
            $this->setPrivateId(Product::class, $product, 'id', (int)$pid);
            $product->setName('Product #'.$pid);
            $order->addProduct($product);
        }

        // items with customizations
        foreach ($dto->items as $itemDto) {
            $productRef = new Product();
            $this->setPrivateId(Product::class, $productRef, 'id', $itemDto->productId);
            $productRef->setName('Product #'.$itemDto->productId);

            $oi = new OrderItem();
            $oi->setOrder($order)
                ->setProduct($productRef)
                ->setTitle($itemDto->title)
                ->setQuantity($itemDto->quantity)
                ->setPrice($itemDto->price)
                ->setObservation($itemDto->observation ?? null);

            // assign fake id to order item (helps presenter/tests if needed)
            $this->setPrivateId(OrderItem::class, $oi, 'id', $this->autoOrderItemId++);

            foreach ($itemDto->customerItems as $cDto) {
                $itemRef = new Item();
                $this->setPrivateId(Item::class, $itemRef, 'id', $cDto->itemId);
                $itemRef->setName('Item #'.$cDto->itemId)
                    ->setPrice(0)
                    ->setUrlImg('')
                    ->setAvailable(true);

                $oic = new OrderItemCustomization();
                $oic->setOrderItem($oi)
                    ->setItem($itemRef)
                    ->setTitle($cDto->title)
                    ->setQuantity($cDto->quantity)
                    ->setPrice($cDto->price)
                    ->setObservation($cDto->observation ?? null);

                $this->setPrivateId(OrderItemCustomization::class, $oic, 'id', $this->autoCustomizationId++);
                $oi->addCustomerItem($oic);
            }

            $order->addItem($oi);
        }

        $this->storage[$order->getId()] = $order;
        return $order;
    }

    public function update(UpdateOrderDto $dto): Order
    {
        $order = $this->findById($dto->id);
        if (!$order) {
            throw new \RuntimeException('Order not found');
        }
        $order
            ->setClientId($dto->clientId)
            ->setStatus($dto->status)
            ->setAmount($dto->amount)
            ->setTransactionId($dto->transactionId)
            ->setIsRandomClient($dto->isRandomClient)
            ->setCodeClientRandom($dto->codeClientRandom)
            ->setObservation($dto->observation);

        // re-sync legacy products
        foreach ($order->getProducts()->toArray() as $existing) {
            $order->removeProduct($existing);
        }
        foreach ($dto->productIds as $pid) {
            $product = new Product();
            $this->setPrivateId(Product::class, $product, 'id', (int)$pid);
            $product->setName('Product #'.$pid);
            $order->addProduct($product);
        }

        return $order;
    }

    public function updateStatus(int $id, OrderStatus $status): Order
    {
        $order = $this->findById($id);
        if (!$order) {
            throw new \RuntimeException('Order not found');
        }
        $order->setStatus($status);
        return $order;
    }

    public function findById(int $id): ?Order
    {
        return $this->storage[$id] ?? null;
    }

    public function findAllPaginated(array $filters, int $page, int $perPage): array
    {
        return array_values($this->storage);
    }

    public function delete(Order $order): void
    {
        $id = $order->getId();
        if ($id !== null && isset($this->storage[$id])) {
            unset($this->storage[$id]);
        }
    }

    private function assignOrderId(Order $order): void
    {
        $this->setPrivateId(Order::class, $order, 'id', $this->autoId++);
    }

    /**
     * @param class-string $class
     */
    private function setPrivateId(string $class, object $obj, string $property, int $value): void
    {
        $ref = new \ReflectionProperty($class, $property);
        $ref->setAccessible(true);
        $ref->setValue($obj, $value);
    }
}
