<?php

declare(strict_types=1);

namespace App\Enums;

enum ComplianceNoticeType: string
{
    case G03Compliance = 'g03_compliance';
    case G04Disapproval = 'g04_disapproval';
}
