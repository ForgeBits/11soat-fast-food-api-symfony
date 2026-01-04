<?php

namespace App\Application\Domain\Entities\Orders\Enum;

enum OrderStatus: string
{
    case PENDING = 'PENDING';
    case PAID = 'PAID';
    case CANCELED = 'CANCELED';
    case FAILED = 'FAILED';
    case IN_PREPARATION = 'IN_PREPARATION';
    case READY_TO_DELIVER = 'READY_TO_DELIVER';
    case DONE = 'DONE';
}
