<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RamadhanPeriod;

class RamadhanPeriodSeeder extends Seeder
{
    public function run(): void
    {
        $periods = [
            [
                'hijri_year' => 1445,
                'label'      => 'Ramadhan 1445H / 2024M',
                'start_date' => '2024-03-12',
                'end_date'   => '2024-04-10',
                'target'     => 1800000000,
            ],
            [
                'hijri_year' => 1446,
                'label'      => 'Ramadhan 1446H / 2025M',
                'start_date' => '2025-03-01',
                'end_date'   => '2025-03-30',
                'target'     => 1800000000,
            ],
            [
                'hijri_year' => 1447,
                'label'      => 'Ramadhan 1447H / 2026M',
                'start_date' => '2026-02-18',
                'end_date'   => '2026-03-19',
                'target'     => 1800000000,
            ],
        ];

        foreach ($periods as $p) {
            RamadhanPeriod::updateOrCreate(
                ['hijri_year' => $p['hijri_year']],
                $p
            );
        }
    }
}
