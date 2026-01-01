<?php

namespace App\Infrastructure\Database\Repositories\Orders;

use App\Application\Domain\Dtos\Orders\CreateOrderDto;
use App\Application\Domain\Dtos\Orders\UpdateOrderDto;
use App\Application\Domain\Entities\Orders\Entity\Order;
use App\Application\Domain\Entities\Orders\Entity\OrderItem;
use App\Application\Domain\Entities\Orders\Entity\OrderItemCustomization;
use App\Application\Domain\Entities\Products\Entity\Product;
use App\Application\Domain\Entities\Items\Entity\Item;
use App\Application\Port\Output\Repositories\OrderRepositoryPort;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class OrderRepository extends ServiceEntityRepository implements OrderRepositoryPort
{
    public function __construct(
        public readonly ManagerRegistry $registry
    ) {
        parent::__construct($registry, Order::class);
    }

    public function create(CreateOrderDto $dto): Order
    {
        $em = $this->registry->getManager();

        $order = new Order();
        $order
            ->setClientId($dto->clientId)
            ->setStatus($dto->status)
            ->setAmount($dto->amount)
            ->setTransactionId($dto->transactionId)
            ->setIsRandomClient($dto->isRandomClient)
            ->setCodeClientRandom($dto->codeClientRandom)
            ->setObservation($dto->observation);

        // attach products (legacy support)
        foreach ($dto->productIds as $pid) {
            /** @var Product $ref */
            $ref = $em->getReference(Product::class, $pid);
            $order->addProduct($ref);
        }

        // attach structured items with customizations
        foreach ($dto->items as $itemDto) {
            /** @var Product $productRef */
            $productRef = $em->getReference(Product::class, $itemDto->productId);
            $orderItem = new OrderItem();
            $orderItem
                ->setOrder($order)
                ->setProduct($productRef)
                ->setTitle($itemDto->title)
                ->setQuantity($itemDto->quantity)
                ->setPrice($itemDto->price)
                ->setObservation($itemDto->observation ?? null);

            // persist customizations for this order item
            foreach ($itemDto->customerItems as $cDto) {
                /** @var Item $itemRef */
                $itemRef = $em->getReference(Item::class, $cDto->itemId);
                $cust = new OrderItemCustomization();
                $cust
                    ->setOrderItem($orderItem)
                    ->setItem($itemRef)
                    ->setTitle($cDto->title)
                    ->setQuantity($cDto->quantity)
                    ->setPrice($cDto->price)
                    ->setObservation($cDto->observation ?? null);
                $orderItem->addCustomerItem($cust);
            }

            $order->addItem($orderItem);
            $em->persist($orderItem);
        }

        $em->persist($order);
        $em->flush();

        return $order;
    }

    public function update(UpdateOrderDto $dto): Order
    {
        $em = $this->registry->getManager();
        /** @var Order $order */
        $order = $this->find($dto->id);

        $order
            ->setClientId($dto->clientId)
            ->setStatus($dto->status)
            ->setAmount($dto->amount)
            ->setTransactionId($dto->transactionId)
            ->setIsRandomClient($dto->isRandomClient)
            ->setCodeClientRandom($dto->codeClientRandom)
            ->setObservation($dto->observation);

        // sync products collection
        // remove all current and add the provided ones
        foreach ($order->getProducts()->toArray() as $existing) {
            $order->removeProduct($existing);
        }
        foreach ($dto->productIds as $pid) {
            /** @var Product $ref */
            $ref = $em->getReference(Product::class, $pid);
            $order->addProduct($ref);
        }

        $em->persist($order);
        $em->flush();

        return $order;
    }

    public function findById(int $id): ?Order
    {
        return $this->createQueryBuilder('o')
            ->leftJoin('o.products', 'p')->addSelect('p')
            ->andWhere('o.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findAllPaginated(array $filters, int $page, int $perPage): array
    {
        return $this->createQueryBuilder('o')
            ->select('DISTINCT o, p')
            ->leftJoin('o.products', 'p')
            ->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage)
            ->getQuery()
            ->getResult();
    }

    public function delete(Order $order): void
    {
        $em = $this->registry->getManager();
        $em->remove($order);
        $em->flush();
    }
}
