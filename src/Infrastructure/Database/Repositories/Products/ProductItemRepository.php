<?php

namespace App\Infrastructure\Database\Repositories\Products;

use App\Application\Domain\Dtos\ProductItem\CreateProductItemDto;
use App\Application\Domain\Dtos\ProductItem\UpdateProductItemDto;
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

    public function findById(int $id): ?ProductItem
    {
        return $this->createQueryBuilder('pi')
            ->leftJoin('pi.product', 'p')->addSelect('p')
            ->leftJoin('pi.item', 'i')->addSelect('i')
            ->andWhere('pi.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findAllPaginated(array $filters, int $page, int $perPage): array
    {
        return $this->createQueryBuilder('pi')
            ->leftJoin('pi.product', 'p')->addSelect('p')
            ->leftJoin('pi.item', 'i')->addSelect('i')
            ->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage)
            ->getQuery()
            ->getResult();
    }

    public function findByProductId(int $productId): array
    {
        return $this->createQueryBuilder('pi')
            ->leftJoin('pi.product', 'p')->addSelect('p')
            ->leftJoin('pi.item', 'i')->addSelect('i')
            ->andWhere('p.id = :pid')
            ->setParameter('pid', $productId)
            ->getQuery()
            ->getResult();
    }

    public function update(UpdateProductItemDto $dto): ProductItem
    {
        /** @var ProductItem $entity */
        $entity = $this->find($dto->id);
        $entity->setEssential($dto->essential);
        $entity->setQuantity($dto->quantity);
        $entity->setCustomizable($dto->customizable);
        $entity->touch();

        $em = $this->getEntityManager();
        $em->persist($entity);
        $em->flush();

        return $entity;
    }

    public function delete(ProductItem $entity): void
    {
        $em = $this->getEntityManager();
        $em->remove($entity);
        $em->flush();
    }
}
