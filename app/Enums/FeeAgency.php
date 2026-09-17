<?php

declare(strict_types=1);

namespace App\Enums;

enum FeeAgency: string
{
    case Lgu = 'lgu';
    case Bfp = 'bfp';
    case Dpwh = 'dpwh';
    case Cto = 'cto';
}
