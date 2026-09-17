<?php

declare(strict_types=1);

namespace App\Enums;

enum ComplianceNoticeStatus: string
{
    case Draft = 'draft';
    case Issued = 'issued';
    case Appealed = 'appealed';
    case Closed = 'closed';
}
