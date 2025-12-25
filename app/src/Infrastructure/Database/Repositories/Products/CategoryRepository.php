<?php

namespace App\Infrastructure\Database\Repositories\Products;

use App\Application\Port\Output\Repositories\CategoryRepositoryPort;
use App\Domain\Categories\DTO\CreateCategoryDto;
use App\Domain\Categories\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CategoryRepository extends ServiceEntityRepository implements CategoryRepositoryPort
{
    public function __construct(
        public readonly ManagerRegistry $registry
    ) {
        parent::__construct($registry, Category::class);
    }

    public function create(CreateCategoryDto $dto): Category
    {
        $category = new Category();
        $category->setName($dto->name);
        $category->setDescription($dto->description);

        $this->registry->getManager()->persist($category);
        $this->registry->getManager()->flush();

        return $category;
    }
}
