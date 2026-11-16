<?php

namespace Database\Seeders;

use App\Models\departments;
use App\Models\Jobtitle;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Starter departments. Idempotent: existing departments (matched by name) are left untouched.
 */
class DepartmentsSeeder extends Seeder
{
    public const DEPARTMENTS = [
        'Human Resources' => 'HR',
        'Finance' => 'Finance',
        'Accounting' => 'Accounting',
        'IT' => 'Information Technology',
        'Administration' => 'Administration',
        'Medical Ward' => 'MW',
        'Surgical Ward' => 'SW',
        'Dental Ward' => 'DW',
        'Pharmacy' => 'Pharmacy',
        'Laboratory' => 'Laboratory',
        'OPD' => 'OPD',
    ];

    public function run(): void
    {
        $addedBy = User::query()->value('id') ?? User::factory()->create()->id;
        $supervisorTitleId = Jobtitle::query()->value('id');

        foreach (self::DEPARTMENTS as $name => $description) {
            departments::firstOrCreate(['name' => $name], [
                'description' => $description,
                'supervisor_title_id' => $supervisorTitleId,
                'status' => departments::activeValue(),
                'added_by' => $addedBy,
            ]);
        }
    }
}
