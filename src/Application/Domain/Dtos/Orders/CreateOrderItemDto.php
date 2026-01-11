<?php

namespace App\Application\Domain\Dtos\Orders;

use Symfony\Component\Validator\Constraints as Assert;

class CreateOrderItemDto
{
    #[Assert\NotNull]
    #[Assert\Positive]
    public int $productId;

    #[Assert\NotBlank]
    #[Assert\Length(max:255)]
    public string $title;

    #[Assert\NotNull]
    #[Assert\Positive]
    public int $quantity;

    #[Assert\NotNull]
    #[Assert\PositiveOrZero]
    public float $price;

    #[Assert\Optional]
    #[Assert\Length(max:5000)]
    public ?string $observation = null;

    /**
     * @var CreateOrderItemCustomizationDto[]
     */
    #[Assert\Type('array')]
    public array $customerItems = [];
}
