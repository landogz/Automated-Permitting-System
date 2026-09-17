<?php

declare(strict_types=1);

namespace App\Enums;

enum AppealStatus: string
{
    case Pending = 'pending';
    case Upheld = 'upheld';
    case Denied = 'denied';
}
