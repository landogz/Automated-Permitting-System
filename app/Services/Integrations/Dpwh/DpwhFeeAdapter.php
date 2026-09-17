<?php

declare(strict_types=1);

namespace App\Services\Integrations\Dpwh;

use Illuminate\Support\Facades\Log;

/**
 * Phase I stub for DPWH fee / electrical inspection hooks.
 */
final class DpwhFeeAdapter
{
    /**
     * @param  array{application_no: string, amount: float|string}  $payload
     * @return array{status: string, reference: string}
     */
    public function assessFee(array $payload): array
    {
        $reference = 'DPWH-STUB-'.strtoupper(substr(md5($payload['application_no'].$payload['amount']), 0, 8));

        Log::info('DpwhFeeAdapter.assessFee', [
            'reference' => $reference,
            'application_no' => $payload['application_no'],
        ]);

        return [
            'status' => 'stubbed',
            'reference' => $reference,
        ];
    }
}
