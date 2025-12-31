<?php

namespace App\Application\Domain\Dtos\ProductItem;

use Symfony\Component\Validator\Constraints as Assert;

class CreateProductItemDto
{
    #[Assert\NotBlank]
    #[Assert\Positive]
    public int $productId;

    #[Assert\NotBlank]
    #[Assert\Positive]
    public int $itemId;

    #[Assert\NotNull]
    public bool $essential = false;

    #[Assert\NotNull]
    #[Assert\Positive]
    public int $quantity = 1;

    #[Assert\NotNull]
    public bool $customizable = false;
}
