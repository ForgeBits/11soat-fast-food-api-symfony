<?php

namespace App\Infrastructure\Database\Repositories\Products;

use App\Application\Domain\Dtos\ProductItem\CreateProductItemDto;
use App\Application\Domain\Entities\Items\Entity\Item;
use App\Application\Domain\Entities\ProductItem\Entity\ProductItem;
use App\Application\Domain\Entities\Products\Entity\Product;
use App\Application\Port\Output\Repositories\ProductItemRepositoryPort;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ProductItemRepository extends ServiceEntityRepository implements ProductItemRepositoryPort
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductItem::class);
    }

    public function create(CreateProductItemDto $dto): ProductItem
    {
        $em = $this->getEntityManager();

        /** @var Product $productRef */
        $productRef = $em->getReference(Product::class, $dto->productId);
        /** @var Item $itemRef */
        $itemRef = $em->getReference(Item::class, $dto->itemId);

        $entity = new ProductItem($productRef, $itemRef);
        $entity->setEssential($dto->essential);
        $entity->setQuantity($dto->quantity);
        $entity->setCustomizable($dto->customizable);

        $em->persist($entity);
        $em->flush();

        return $entity;
    }

    public function findByProductAndItem(int $productId, int $itemId): ?ProductItem
    {
        return $this->createQueryBuilder('pi')
            ->join('pi.product', 'p')
            ->join('pi.item', 'i')
            ->andWhere('p.id = :pid')
            ->andWhere('i.id = :iid')
            ->setParameter('pid', $productId)
            ->setParameter('iid', $itemId)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
