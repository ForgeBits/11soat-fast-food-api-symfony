<?php

namespace App\Infrastructure\Database\Repositories\Products;

use App\Application\Domain\Dtos\Items\CreateItemDto;
use App\Application\Domain\Dtos\Items\UpdateItemDto;
use App\Application\Domain\Entities\Items\Entity\Item;
use App\Application\Port\Output\Repositories\ItemRepositoryPort;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ItemsRepository extends ServiceEntityRepository implements ItemRepositoryPort
{
    public function __construct(
        public readonly ManagerRegistry $registry
    ) {
        parent::__construct($registry, Item::class);
    }

    public function create(CreateItemDto $dto): Item
    {
        $item = new Item();
        $item->setName($dto->name);
        $item->setDescription($dto->description);
        $item->setPrice($dto->price);
        $item->setUrlImg($dto->url_img);
        $item->setAvailable($dto->available);

        $this->registry->getManager()->persist($item);
        $this->registry->getManager()->flush();

        return $item;
    }

    public function update(UpdateItemDto $dto): Item
    {
        /** @var Item $item */
        $item = $this->find($dto->id);

        $item->setName($dto->name);
        $item->setDescription($dto->description);
        $item->setPrice($dto->price);
        $item->setUrlImg($dto->url_img);
        $item->setAvailable($dto->available);

        $this->registry->getManager()->persist($item);
        $this->registry->getManager()->flush();

        return $item;
    }

    public function findByName(string $name): ?Item
    {
        /** @var Item $item */
        $item =  $this->findOneBy(['name' => $name]);

        return $item;
    }

    public function findById(int $id): ?Item
    {
        /** @var Item $item */
        $item = $this->find($id);

        return $item;
    }

    public function findAllPaginated(array $filters, int $page, int $perPage): array
    {
         return $this->createQueryBuilder('p')
            ->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage)
            ->getQuery()
            ->getResult();
    }

    public function delete(Item $item): void
    {
        $this->registry->getManager()->remove($item);
        $this->registry->getManager()->flush();
    }
}
