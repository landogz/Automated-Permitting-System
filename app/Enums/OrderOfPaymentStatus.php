<?php

declare(strict_types=1);

namespace App\Enums;

enum OrderOfPaymentStatus: string
{
    case Draft = 'draft';
    case Issued = 'issued';
    case PaidStub = 'paid_stub';
    case Cancelled = 'cancelled';
}
