<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\FeeAgency;
use App\Models\FeeRule;
use App\Models\NumberingSeries;
use Illuminate\Database\Seeder;

class InspectionFeesComplianceSeeder extends Seeder
{
    public function run(): void
    {
        FeeRule::query()->firstOrCreate(
            ['code' => 'LGU-BASE'],
            [
                'name' => 'LGU building permit base fee',
                'agency' => FeeAgency::Lgu->value,
                'basis' => 'fixed',
                'amount' => 1500,
                'priority' => 10,
                'is_active' => true,
                'conditions' => [],
            ]
        );

        FeeRule::query()->firstOrCreate(
            ['code' => 'LGU-AREA'],
            [
                'name' => 'LGU area surcharge',
                'agency' => FeeAgency::Lgu->value,
                'basis' => 'area_rate',
                'amount' => 0,
                'rate' => 15,
                'priority' => 20,
                'is_active' => true,
                'conditions' => [
                    ['field' => 'lot_area', 'operator' => '>=', 'value' => 50],
                ],
            ]
        );

        FeeRule::query()->firstOrCreate(
            ['code' => 'BFP-FSIC'],
            [
                'name' => 'BFP FSIC assessment (stub)',
                'agency' => FeeAgency::Bfp->value,
                'basis' => 'fixed',
                'amount' => 850,
                'priority' => 30,
                'is_active' => true,
                'conditions' => [],
            ]
        );

        FeeRule::query()->firstOrCreate(
            ['code' => 'DPWH-ELEC'],
            [
                'name' => 'DPWH electrical inspection fee (stub)',
                'agency' => FeeAgency::Dpwh->value,
                'basis' => 'fixed',
                'amount' => 500,
                'priority' => 40,
                'is_active' => true,
                'conditions' => [],
            ]
        );

        foreach (
            [
                'inspection' => ['prefix' => 'IN-'.date('Y').'-', 'next' => 1],
                'order_of_payment' => ['prefix' => 'G02-'.date('Y').'-', 'next' => 1],
                'notice_g03' => ['prefix' => 'G03-'.date('Y').'-', 'next' => 1],
                'notice_g04' => ['prefix' => 'G04-'.date('Y').'-', 'next' => 1],
            ] as $key => $cfg
        ) {
            NumberingSeries::query()->firstOrCreate(
                ['key' => $key],
                [
                    'prefix' => $cfg['prefix'],
                    'next_number' => $cfg['next'],
                    'pad_length' => 6,
                ]
            );
        }
    }
}
