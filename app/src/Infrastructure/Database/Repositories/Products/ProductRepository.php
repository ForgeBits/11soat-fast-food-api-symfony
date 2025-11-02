<?php

namespace App\Infrastructure\Database\Repositories\Products;

use App\Application\Port\Output\Repositories\ProductRepositoryPort;
use App\Domain\Products\DTO\CreateProductDto;
use App\Domain\Products\Entity\Product;
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
        dd($dto->name);
        $product = new Product();
        $product->setName($dto->name);
        $product->setDescription($dto->description);
        $product->setAmount($dto->amount);
        $product->setUrlImg($dto->url_img);
        $product->setCustomizable($dto->customizable);
        $product->setAvailable($dto->available);

        $this->registry->getManager()->persist($product);
        $this->registry->getManager()->flush();

        return $product;
    }
}
