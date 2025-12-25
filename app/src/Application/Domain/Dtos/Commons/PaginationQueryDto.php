<?php

namespace App\Application\Domain\Dtos\Commons;

use Symfony\Component\Validator\Constraints as Assert;

class PaginationQueryDto
{
    #[Assert\NotNull]
    #[Assert\PositiveOrZero]
    public float $amount;


}
