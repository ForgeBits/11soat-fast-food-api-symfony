<?php

namespace App\Infrastructure\Database\Repositories\Products;

use App\Application\Domain\Dtos\Products\CreateProductDto;
use App\Application\Domain\Dtos\Products\UpdateProductDto;
use App\Application\Domain\Entities\Categories\Entity\Category;
use App\Application\Domain\Entities\Products\Entity\Product;
use App\Application\Port\Output\Repositories\ProductRepositoryPort;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ProductRepository extends ServiceEntityRepository implements ProductRepositoryPort
{
    public function __construct(
        public readonly ManagerRegistry $registry
    ) {
        parent::__construct($registry, Product::class);
    }

    public function create(CreateProductDto $dto): Product
    {
        $product = new Product();
        $product->setName($dto->name);
        $product->setDescription($dto->description);
        $product->setAmount($dto->amount);
        $product->setUrlImg($dto->url_img);
        $product->setCustomizable($dto->customizable);
        $product->setAvailable($dto->available);
        if ($dto->category_id !== null) {
            $categoryRef = $this->registry->getManager()->getReference(Category::class, $dto->category_id);
            $product->setCategory($categoryRef);
        }

        $this->registry->getManager()->persist($product);
        $this->registry->getManager()->flush();

        return $product;
    }

    public function update(UpdateProductDto $dto): Product
    {
        /** @var Product $product */
        $product = $this->find($dto->id);

        $product->setName($dto->name);
        $product->setDescription($dto->description);
        $product->setAmount($dto->amount);
        $product->setUrlImg($dto->url_img);
        $product->setCustomizable($dto->customizable);
        $product->setAvailable($dto->available);
        if ($dto->category_id !== null) {
            $categoryRef = $this->registry->getManager()->getReference(Category::class, $dto->category_id);
            $product->setCategory($categoryRef);
        }

        $this->registry->getManager()->persist($product);
        $this->registry->getManager()->flush();

        return $product;
    }

    public function findByName(string $name): ?Product
    {
        /** @var Product $product */
        $product =  $this->findOneBy(['name' => $name]);

        return $product;
    }

    public function findById(int $id): ?Product
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.category', 'c')
            ->addSelect('c')
            ->andWhere('p.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findAllPaginated(array $filters, int $page, int $perPage)
    {
         return $this->createQueryBuilder('p')
            ->innerJoin('p.category', 'c')
            ->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage)
            ->getQuery()
            ->getResult();
    }

    public function delete(Product $product): void
    {
        $this->registry->getManager()->remove($product);
        $this->registry->getManager()->flush();
    }
}
