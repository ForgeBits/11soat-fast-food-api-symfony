<?php

namespace App\Infrastructure\Database\Repositories\Products;

use App\Application\Domain\Dtos\Categories\CreateCategoryDto;
use App\Application\Domain\Dtos\Categories\UpdateCategoryDto;
use App\Application\Domain\Entities\Categories\Entity\Category;
use App\Application\Port\Output\Repositories\CategoryRepositoryPort;
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

    public function update(UpdateCategoryDto $dto): Category
    {
        /** @var Category $category */
        $category = $this->find($dto->id);

        $category->setName($dto->name);
        $category->setDescription($dto->description);

        $this->registry->getManager()->persist($category);
        $this->registry->getManager()->flush();

        return $category;
    }

    public function findById(int $id): ?Category
    {
        /** @var Category $category */
        $category = $this->find($id);

        return $category;
    }

    public function findByName(string $name): ?Category
    {
        /** @var Category $category */
        $category = $this->findOneBy(['name' => $name]);

        return $category;
    }

    public function findAllPaginated(array $filters, int $page, int $perPage): array
    {
        return $this->createQueryBuilder('c')
            ->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage)
            ->getQuery()
            ->getResult();
    }

    public function delete(Category $category): void
    {
        $this->registry->getManager()->remove($category);
        $this->registry->getManager()->flush();
    }
}
