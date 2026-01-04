<?php

namespace App\Application\UseCases\ProductItem;

use App\Application\Domain\Entities\ProductItem\Entity\ProductItem;
use App\Application\Port\Output\Repositories\ProductItemRepositoryPort;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

readonly class FindProductItemUseCase
{
    public function __construct(
        private ProductItemRepositoryPort $repository,
    ) {
    }

    public function execute(int $id): ProductItem
    {
        $entity = $this->repository->findById($id);
        if (!$entity) {
            throw new NotFoundHttpException(message: 'Relação Produto-Item não encontrada.', code: 404);
        }

        return $entity;
    }
}
