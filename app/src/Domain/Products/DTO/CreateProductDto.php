<?php

namespace App\Domain\Products\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class CreateProductDto
{
    #[Assert\NotBlank]
    #[Assert\Length(max:150)]
    public string $name;

    #[Assert\Length(max:5000)]
    public ?string $description = null;

    #[Assert\NotNull]
    #[Assert\PositiveOrZero]
    public float $amount;

    #[Assert\NotBlank]
    #[Assert\Url]
    public string $url_img;

    #[Assert\NotNull]
    public bool $customizable = false;

    #[Assert\NotNull]
    public bool $available = true;

    #[Assert\Positive]
    public ?int $category_id = null;
}
