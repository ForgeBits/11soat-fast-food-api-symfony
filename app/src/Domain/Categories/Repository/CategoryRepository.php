<?php

namespace App\Domain\Categories\Repository;

use App\Domain\Categories\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CategoryRepository extends ServiceEntityRepository
{
    public function __construct(
        public readonly ManagerRegistry $registry
    )
    {
        parent::__construct($registry, Category::class);
    }
}
