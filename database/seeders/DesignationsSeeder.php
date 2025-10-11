<?php

namespace Database\Seeders;

use App\Models\designations;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DesignationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        designations::create(
            ['name' => 'Chief Executive Officer', 'code' => 'CEO', 'added_by' => \App\Models\User::factory()->create()->id],
            ['name' => 'Chief Financial Officer', 'code' => 'CFO', 'added_by' => \App\Models\User::factory()->create()->id],
            ['name' => 'Chief Human Resources Officer', 'code' => 'CHRO', 'added_by' => \App\Models\User::factory()->create()->id],
            ['name' => 'Chief Information Officer', 'code' => 'CIO', 'added_by' => \App\Models\User::factory()->create()->id],
            ['name' => 'Chief Legal Officer', 'code' => 'CLO', 'added_by' => \App\Models\User::factory()->create()->id],
            ['name' => 'Chief Operating Officer', 'code' => 'COO', 'added_by' => \App\Models\User::factory()->create()->id],
            ['name' => 'Chief Product Officer', 'code' => 'CPO', 'added_by' => \App\Models\User::factory()->create()->id],
            ['name' => 'Chief Technology Officer', 'code' => 'CTO', 'added_by' => \App\Models\User::factory()->create()->id],
            ['name' => 'Director', 'code' => 'Dir', 'added_by' => \App\Models\User::factory()->create()->id],
            ['name' => 'Executive Director', 'code' => 'EDir', 'added_by' => \App\Models\User::factory()->create()->id],
            ['name' => 'General Manager', 'code' => 'GM', 'added_by' => \App\Models\User::factory()->create()->id],
            ['name' => 'Head of Human Resources', 'code' => 'HR', 'added_by' => \App\Models\User::factory()->create()->id],
            ['name' => 'Head of Information Technology', 'code' => 'IT', 'added_by' => \App\Models\User::factory()->create()->id],
            ['name' => 'Head of Marketing', 'code' => 'Marketing', 'added_by' => \App\Models\User::factory()->create()->id],
            ['name' => 'Head of Operations', 'code' => 'Ops', 'added_by' => \App\Models\User::factory()->create()->id],
            ['name' => 'Head of Sales', 'code' => 'Sales', 'added_by' => \App\Models\User::factory()->create()->id],
            ['name' => 'Human Resources Manager', 'code' => 'HRM', 'added_by' => \App\Models\User::factory()->create()->id],
            ['name' => 'Information Technology Manager', 'code' => 'ITM', 'added_by' => \App\Models\User::factory()->create()->id],
            ['name' => 'Marketing Manager', 'code' => 'MarketingM', 'added_by' => \App\Models\User::factory()->create()->id],
            ['name' => 'Operations Manager', 'code' => 'OpsM', 'added_by' => \App\Models\User::factory()->create()->id],
            ['name' => 'Sales Manager', 'code' => 'SalesM', 'added_by' => \App\Models\User::factory()->create()->id],
            ['name' => 'Senior Executive Officer', 'code' => 'SEO', 'added_by' => \App\Models\User::factory()->create()->id],
            ['name' => 'Senior Manager', 'code' => 'SM', 'added_by' => \App\Models\User::factory()->create()->id],
            ['name' => 'Senior Vice President', 'code' => 'SVP', 'added_by' => \App\Models\User::factory()->create()->id],
            ['name' => 'Vice President', 'code' => 'VP', 'added_by' => \App\Models\User::factory()->create()->id],
        );
    }
}
