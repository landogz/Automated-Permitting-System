<?php

declare(strict_types=1);

namespace App\Services\Integrations\Cto;

use Illuminate\Support\Facades\Log;

/**
 * Phase I stub for City Treasurer's Office payment posting.
 */
final class CtoPaymentAdapter
{
    /**
     * @param  array{oop_no: string, amount: float|string, application_no: string}  $payload
     * @return array{status: string, reference: string}
     */
    public function postOrderOfPayment(array $payload): array
    {
        $reference = 'CTO-STUB-'.strtoupper(substr(md5($payload['oop_no']), 0, 8));

        Log::info('CtoPaymentAdapter.postOrderOfPayment', [
            'reference' => $reference,
            'oop_no' => $payload['oop_no'],
            'amount' => $payload['amount'],
        ]);

        return [
            'status' => 'stubbed',
            'reference' => $reference,
        ];
    }
}
