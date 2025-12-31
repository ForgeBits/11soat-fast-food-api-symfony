<?php

namespace App\Application\UseCases\ProductItem;

use App\Application\Domain\Dtos\ProductItem\UpdateProductItemDto;
use App\Application\Domain\Entities\ProductItem\Entity\ProductItem;
use App\Application\Port\Output\Repositories\ProductItemRepositoryPort;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

readonly class UpdateProductItemUseCase
{
    public function __construct(
        private ProductItemRepositoryPort $repository,
    ) {
    }

    public function execute(UpdateProductItemDto $dto): ProductItem
    {
        $existing = $this->repository->findById($dto->id);
        if (!$existing) {
            throw new NotFoundHttpException(message: 'Relação Produto-Item não encontrada.', code: 404);
        }

        return $this->repository->update($dto);
    }
}
