<?php

namespace Database\Seeders;

use App\Models\designations;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Starter designations. Idempotent: existing designations (matched by code) are left untouched.
 */
class DesignationsSeeder extends Seeder
{
    public const DESIGNATIONS = [
        'CEO' => 'Chief Executive Officer',
        'CHRO' => 'Chief Human Resources Officer',
        'CIO' => 'Chief Information Officer',
        'CLO' => 'Chief Legal Officer',
        'COO' => 'Chief Operating Officer',
        'CPO' => 'Chief Product Officer',
        'CTO' => 'Chief Technology Officer',
        'Dir' => 'Director',
        'EDir' => 'Executive Director',
        'GM' => 'General Manager',
        'HR' => 'Head of Human Resources',
        'IT' => 'Head of Information Technology',
        'Marketing' => 'Head of Marketing',
        'Ops' => 'Head of Operations',
        'Sales' => 'Head of Sales',
        'HRM' => 'Human Resources Manager',
        'ITM' => 'Information Technology Manager',
        'MarketingM' => 'Marketing Manager',
        'OpsM' => 'Operations Manager',
        'SalesM' => 'Sales Manager',
        'SEO' => 'Senior Executive Officer',
        'SM' => 'Senior Manager',
        'SVP' => 'Senior Vice President',
        'VP' => 'Vice President',
    ];

    public function run(): void
    {
        $addedBy = User::query()->value('id') ?? User::factory()->create()->id;

        foreach (self::DESIGNATIONS as $code => $name) {
            designations::firstOrCreate(['code' => $code], [
                'name' => $name,
                'status' => designations::activeValue(),
                'added_by' => $addedBy,
            ]);
        }
    }
}
