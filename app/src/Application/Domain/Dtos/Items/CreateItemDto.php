<?php

namespace App\Application\Domain\Dtos\Items;

use Symfony\Component\Validator\Constraints as Assert;

class CreateItemDto
{
    #[Assert\NotBlank]
    #[Assert\Length(max:150)]
    public string $name;

    #[Assert\Length(max:5000)]
    public ?string $description = null;

    #[Assert\NotNull]
    #[Assert\Positive]
    public float $price;

    #[Assert\NotNull]
    public bool $available = true;

    #[Assert\NotBlank]
    #[Assert\Url]
    public string $url_img;
}

