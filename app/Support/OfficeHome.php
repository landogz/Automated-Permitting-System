<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\User;

/**
 * Role-aware landing paths for office staff after login / unauthorized redirects.
 */
final class OfficeHome
{
    public static function pathFor(?User $user): string
    {
        if (! $user) {
            return '/login';
        }

        if ($user->hasRole('applicant')) {
            return '/applications';
        }

        return match (true) {
            $user->hasRole('inspector') => '/admin/inspections',
            $user->hasRole('evaluator'), $user->hasRole('receiving') => '/admin/evaluation-queue',
            $user->hasRole('assessor') => '/admin/orders-of-payment',
            $user->hasRole('compliance') => '/admin/compliance-notices',
            $user->hasRole('records') => '/admin/logbooks',
            default => '/admin',
        };
    }
}
