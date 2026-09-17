<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\NumberingSeries;
use Illuminate\Database\Seeder;

class NumberingSeriesSeeder extends Seeder
{
    public function run(): void
    {
        NumberingSeries::query()->firstOrCreate(
            ['key' => 'permit_application'],
            [
                'prefix' => 'APICS-'.date('Y').'-',
                'next_number' => 1,
                'pad_length' => 6,
            ]
        );

        NumberingSeries::query()->firstOrCreate(
            ['key' => 'order_of_payment'],
            [
                'prefix' => 'OOP-'.date('Y').'-',
                'next_number' => 1,
                'pad_length' => 6,
            ]
        );
    }
}
