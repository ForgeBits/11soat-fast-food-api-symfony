<?php

namespace App\Application\Domain\Dtos\Categories;

use Symfony\Component\Validator\Constraints as Assert;

class CreateCategoryDto
{
    #[Assert\NotBlank]
    #[Assert\Length(max:150)]
    public string $name;

    #[Assert\Length(max:5000)]
    public ?string $description = null;
}
