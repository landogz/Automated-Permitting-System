<?php

declare(strict_types=1);

namespace App\Enums;

enum PermitClassification: string
{
    case Simple = 'simple';
    case Complex = 'complex';
    case HighlyTechnical = 'highly_technical';

    public function label(): string
    {
        return match ($this) {
            self::Simple => 'Simple',
            self::Complex => 'Complex',
            self::HighlyTechnical => 'Highly Technical',
        };
    }
}
