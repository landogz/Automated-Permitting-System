<?php

declare(strict_types=1);

namespace App\Enums;

enum RoutingStepStatus: string
{
    case Pending = 'pending';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Skipped = 'skipped';
}
