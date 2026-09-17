<?php

declare(strict_types=1);

namespace App\Services\Integrations\Bfp;

use Illuminate\Support\Facades\Log;

/**
 * Phase I stub for BFP fee assessment hooks.
 */
final class BfpFeeAdapter
{
    /**
     * @param  array{application_no: string, amount: float|string}  $payload
     * @return array{status: string, reference: string}
     */
    public function assessFee(array $payload): array
    {
        $reference = 'BFP-STUB-'.strtoupper(substr(md5($payload['application_no'].$payload['amount']), 0, 8));

        Log::info('BfpFeeAdapter.assessFee', [
            'reference' => $reference,
            'application_no' => $payload['application_no'],
        ]);

        return [
            'status' => 'stubbed',
            'reference' => $reference,
        ];
    }
}
