<?php

namespace Database\Seeders;

use App\Models\Jobtitle;
use App\Models\User;
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
        $firstUser = User::first();
        foreach ($jobtitles as $jobtitle) {
            Jobtitle::create(['name' => $jobtitle, 'code' => null, 'description' => null, 'added_by' =>$firstUser->id]);
        }
    }
}
