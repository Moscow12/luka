<?php

namespace Database\Seeders;

use App\Models\denominations;
use App\Models\religions;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DenominationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       // Define denominations grouped by religion name
        $data = [
            'Christianity' => [
                'Catholic',
                'Anglican',
                'Pentecostal',
                'Lutheran',
                'Baptist',
            ],
            'Islam' => [
                'Sunni',
                'Shia',
                'Ahmadiyya',
            ],
            'Hinduism' => [
                'Vaishnavism',
                'Shaivism',
                'Shaktism',
            ],
            'Buddhism' => [
                'Theravada',
                'Mahayana',
                'Vajrayana',
            ],
        ];

        foreach ($data as $religionName => $denominations) {
            $religion = religions::where('name', $religionName)->first();

            if ($religion) {
                foreach ($denominations as $denominationName) {
                    denominations::updateOrCreate(
                        [
                            'name' => $denominationName,
                            'religion_id' => $religion->uuid,
                        ]
                    );
                }
            }
        }
    }
}
