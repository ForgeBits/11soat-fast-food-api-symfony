<?php

namespace App\Application\Domain\Dtos\Orders;

use App\Application\Domain\Entities\Orders\Enum\OrderStatus;
use Symfony\Component\Validator\Constraints as Assert;

class CreateOrderDto
{
    #[Assert\Positive]
    #[Assert\Optional]
    public ?int $clientId = null;

    #[Assert\NotNull]
    public OrderStatus $status = OrderStatus::PENDING;

    #[Assert\NotNull]
    #[Assert\PositiveOrZero]
    public float $amount = 0.0;

    #[Assert\All([
        new Assert\Positive(),
    ])]
    #[Assert\Type('array')]
    public array $productIds = [];

    #[Assert\Length(max: 191)]
    public ?string $transactionId = null;

    #[Assert\NotNull]
    public bool $isRandomClient = false;

    #[Assert\Optional]
    #[Assert\Positive]
    public ?int $codeClientRandom = null;

    #[Assert\Optional]
    #[Assert\Length(max: 5000)]
    public ?string $observation = null;

    /**
     * Itens do pedido com personalizações do cliente
     * @var CreateOrderItemDto[]
     */
    #[Assert\Type('array')]
    public array $items = [];
}
