<?php

namespace App\Application\Domain\Dtos\Orders;

use App\Application\Domain\Entities\Orders\Enum\OrderStatus;
use Symfony\Component\Validator\Constraints as Assert;

class UpdateOrderStatusDto
{
    #[Assert\NotBlank]
    #[Assert\Positive]
    public int $id;

    #[Assert\NotNull]
    public OrderStatus $status;
}
