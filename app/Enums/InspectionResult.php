<?php

declare(strict_types=1);

namespace App\Enums;

enum InspectionResult: string
{
    case Passed = 'passed';
    case Failed = 'failed';
    case Conditional = 'conditional';
}
