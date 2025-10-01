<?php

namespace Database\Seeders;

use App\Models\Jobtitle;
use Illuminate\Database\Seeder;

class JobtitleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jobtitles = [
            'Medical Doctor',
            'Registered Nurse',
            'Laboratory Technician',
            'Radiologic Technologist',
            'Pharmacist',
            'Medical Administrator',
            'Department Head',
            'Specialist Consultant',
            'Medical Assistant',
            'Healthcare Assistant',
        ];

        foreach ($jobtitles as $jobtitle) {
            Jobtitle::create(['name' => $jobtitle]);
        }
    }
}
