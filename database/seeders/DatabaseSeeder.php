<?php

namespace Database\Seeders;

use Barryvdh\Reflection\DocBlock\Location;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,         
            AclSeeder::class,
            PermissionSeeder::class,
            ReligionsSeeder::class,
            DenominationsSeeder::class,
            CountriesSeeder::class,
            LocationsSeeder::class,
            WardsSeeder::class,
            DesignationsSeeder::class,
            DepartmentsSeeder::class,
            LeavesSeeder::class,
            AllowanceSeeder::class,
            ShiftsSeeder::class,
            FinancialYearsSeeder::class,
            DeductionSeeder::class,
            JobtitleSeeder::class,
            AllowanceSeeder::class,
            WorkstationsSeeder::class,
            EmployeeSeeder::class,
            
        ]);
    }
}
