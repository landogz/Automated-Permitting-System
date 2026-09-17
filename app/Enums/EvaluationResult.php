<?php

declare(strict_types=1);

namespace App\Enums;

enum EvaluationResult: string
{
    case Compliant = 'compliant';
    case NonCompliant = 'non_compliant';
    case NeedsInfo = 'needs_info';
}
