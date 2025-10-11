<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            JobtitleSeeder::class,
            UserSeeder::class,
            AclSeeder::class,
            ReligionsSeeder::class,
            DenominationsSeeder::class,
            CountriesSeeder::class,
            RegionsSeeder::class,
            DistrictsSeeder::class,
            WardsSeeder::class,
            VillagesSeeder::class,    
            DesignationsSeeder::class,
            DepartmentsSeeder::class,
            WorkstationsSeeder::class,
            EmployeeSeeder::class,
            LeavesSeeder::class,
            AllowancesSeeder::class,
            ShiftsSeeder::class,
            FinancialYearsSeeder::class,
        ]);
    }
}
