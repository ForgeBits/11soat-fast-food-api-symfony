<?php
namespace App\Domain\Products\Service;

use App\Domain\Products\DTO\CreateProductDto;
use App\Domain\Products\Entity\Product;
use App\Domain\Products\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;

class ProductService
{
    public function __construct(
        private ProductRepository $repo,
        private EntityManagerInterface $em
    ) {}

    public function create(CreateProductDto $dto): Product
    {
        if ($this->repo->findOneBy(['name' => $dto->name])) {
            throw new \DomainException('Já existe um produto com esse nome.');
        }

        $product = (new Product())
            ->setName($dto->name)
            ->setDescription($dto->description)
            ->setAmount($dto->amount)
            ->setUrlImg($dto->url_img)
            ->setCustomizable($dto->customizable)
            ->setAvailable($dto->available)
            ->setCategoryId($dto->category_id);

        $this->em->persist($product);
        $this->em->flush();

        return $product;
    }
}
