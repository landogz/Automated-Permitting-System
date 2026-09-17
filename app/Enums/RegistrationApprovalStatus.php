<?php

declare(strict_types=1);

namespace App\Enums;

enum RegistrationApprovalStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Declined = 'declined';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Approved => 'Approved',
            self::Declined => 'Declined',
        };
    }

    public function canLogin(): bool
    {
        return $this === self::Approved;
    }
}
