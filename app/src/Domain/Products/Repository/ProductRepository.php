<?php

namespace App\Domain\Products\Repository;

use App\Domain\Products\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ProductRepository extends ServiceEntityRepository
{
    public function __construct(
        public readonly ManagerRegistry $registry
    )
    {
        parent::__construct($registry, Product::class);
    }
}
