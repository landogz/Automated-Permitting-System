<?php

declare(strict_types=1);

namespace App\Services\Integrations\CitizensPortal;

use Illuminate\Support\Facades\Log;

/**
 * Phase I stub adapter for Citizens Portal integration.
 */
final class CitizensPortalAdapter
{
    /**
     * @param  array{application_no: string, citizen_email: string, citizen_name: string}  $payload
     * @return array{status: string, reference: string}
     */
    public function syncApplicationSubmitted(array $payload): array
    {
        $reference = 'CITIZEN-STUB-'.strtoupper(substr(md5($payload['application_no']), 0, 8));

        Log::info('CitizensPortalAdapter.syncApplicationSubmitted', [
            'reference' => $reference,
            'application_no' => $payload['application_no'],
        ]);

        return [
            'status' => 'stubbed',
            'reference' => $reference,
        ];
    }
}
