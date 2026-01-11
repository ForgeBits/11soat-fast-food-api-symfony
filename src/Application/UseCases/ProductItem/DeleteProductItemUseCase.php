<?php

namespace App\Application\UseCases\ProductItem;

use App\Application\Port\Output\Repositories\ProductItemRepositoryPort;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

// Use case para deletar uma relação Produto-Item
readonly class DeleteProductItemUseCase
{
    public function __construct(
        private ProductItemRepositoryPort $repository,
    ) {
    }

    public function execute(int $id): void
    {
        $existing = $this->repository->findById($id);
        if (!$existing) {
            throw new NotFoundHttpException(message: 'Relação Produto-Item não encontrada.', code: 404);
        }

        $this->repository->delete($existing);
    }
}
