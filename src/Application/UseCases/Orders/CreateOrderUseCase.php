<?php

namespace App\Application\UseCases\Orders;

use App\Application\Domain\Dtos\Orders\CreateOrderDto;
use App\Application\Domain\Entities\Orders\Entity\Order;
use App\Application\Port\Output\Repositories\ItemRepositoryPort;
use App\Application\Port\Output\Repositories\OrderRepositoryPort;
use App\Application\Port\Output\Repositories\ProductItemRepositoryPort;
use App\Application\Port\Output\Repositories\ProductRepositoryPort;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

readonly class CreateOrderUseCase
{
    public function __construct(
        private OrderRepositoryPort $orderRepository,
        private ProductRepositoryPort $productRepository,
        private ItemRepositoryPort $itemRepository,
        private ProductItemRepositoryPort $productItemRepository,
    ) {
    }

    public function execute(CreateOrderDto $dto): Order
    {
        foreach ($dto->items as $idx => $itemDto) {
            if ($itemDto->quantity <= 0) {
                throw new BadRequestHttpException("Quantidade inválida para item #$idx");
            }
            foreach ($itemDto->customerItems as $cIdx => $cDto) {
                if ($cDto->quantity <= 0) {
                    throw new BadRequestHttpException("Quantidade inválida para customização #$cIdx do item #$idx");
                }
            }
        }

        foreach ($dto->items as $itemDto) {
            $product = $this->productRepository->findById($itemDto->productId);
            if (!$product) {
                throw new NotFoundHttpException('Produto do item não encontrado.');
            }

            if (!empty($itemDto->customerItems) && !$product->isCustomizable()) {
                throw new BadRequestHttpException('Produto não permite customização.');
            }

            foreach ($itemDto->customerItems as $cDto) {
                $item = $this->itemRepository->findById($cDto->itemId);
                if (!$item) {
                    throw new NotFoundHttpException('Item de customização não encontrado.');
                }
                $relation = $this->productItemRepository->findByProductAndItem($itemDto->productId, $cDto->itemId);
                if (!$relation) {
                    throw new BadRequestHttpException('Item não pertence ao produto e não pode ser customizado.');
                }
                if (!$relation->isCustomizable()) {
                    throw new BadRequestHttpException('Este item '. $cDto->title .' não é customizável para o produto.');
                }
            }
        }
        return $this->orderRepository->create($dto);
    }
}
