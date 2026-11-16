<?php

namespace Database\Seeders;

use App\Models\asset;
use App\Models\assetclass;
use App\Models\building;
use App\Models\departments;
use App\Models\facilitylocation;
use App\Models\User;
use App\Models\workstations;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Starter asset configuration: asset classes, a small catalogue, and (on a fresh install only)
 * a building per workstation with a starter room per department.
 *
 * Safe on a live database: nothing existing is changed, classes / catalogue items are only
 * added when missing by name, and buildings / rooms are only created when none exist yet.
 */
class AssetSetupSeeder extends Seeder
{
    /** name => [depreciation method, useful life years, rate %, description] */
    public const CLASSES = [
        'Medical Equipment' => ['straight_line', 10, 10, 'Diagnostic, therapeutic and monitoring devices'],
        'Furniture' => ['straight_line', 8, 12.5, 'Beds, desks, chairs, cabinets'],
        'Electrical' => ['straight_line', 10, 10, 'Generators, UPS, air conditioners, lighting'],
        'Plumbing Fixtures' => ['straight_line', 15, 6.67, 'Pumps, water tanks, sanitary fittings'],
        'ICT' => ['reducing_balance', 4, 25, 'Computers, printers, network equipment'],
    ];

    /** class => catalogue items */
    public const CATALOGUE = [
        'Medical Equipment' => ['Infusion pump', 'Patient monitor', 'Oxygen concentrator', 'Suction machine', 'Autoclave'],
        'Furniture' => ['Hospital bed', 'Office desk', 'Office chair', 'Filing cabinet'],
        'Electrical' => ['Air conditioner', 'Generator', 'UPS'],
        'Plumbing Fixtures' => ['Water pump', 'Water tank'],
        'ICT' => ['Desktop computer', 'Laptop', 'Printer'],
    ];

    /** department => [room name, code] used only on a fresh install */
    public const ROOMS = [
        'Medical Ward' => [['Ward Room 1', 'MW-01'], ['Nurses Station', 'MW-NS']],
        'Surgical Ward' => [['Ward Room 1', 'SW-01'], ['Procedure Room', 'SW-PR']],
        'Dental Ward' => [['Dental Clinic', 'DW-01']],
        'Pharmacy' => [['Dispensary', 'PH-01'], ['Pharmacy Store', 'PH-ST']],
        'Laboratory' => [['Main Laboratory', 'LAB-01']],
        'OPD' => [['Consultation Room 1', 'OPD-01'], ['Triage', 'OPD-TR']],
        'IT' => [['Server Room', 'IT-SR']],
    ];

    public function run(): void
    {
        $addedBy = User::query()->value('id') ?? User::factory()->create()->id;

        $classes = [];
        foreach (self::CLASSES as $name => [$method, $life, $rate, $description]) {
            $classes[$name] = assetclass::whereRaw('LOWER(name) = ?', [Str::lower($name)])->first()
                ?? assetclass::create([
                    'name' => $name,
                    'depreciation' => $description,
                    'depreciation_method' => $method,
                    'useful_life_years' => $life,
                    'depreciation_rate' => $rate,
                    'added_by' => $addedBy,
                ]);
        }

        foreach (self::CATALOGUE as $className => $items) {
            foreach ($items as $item) {
                // asset names are unique across the whole catalogue
                if (! asset::withTrashed()->whereRaw('LOWER(name) = ?', [Str::lower($item)])->exists()) {
                    asset::create(['name' => $item, 'type' => 'physical', 'asset_class_id' => $classes[$className]->id, 'added_by' => $addedBy]);
                }
            }
        }

        $this->seedRooms($addedBy);
    }

    /** Fresh install only: a "Main Block" per workstation and starter rooms per department. */
    protected function seedRooms(string $addedBy): void
    {
        if (building::withTrashed()->exists() || facilitylocation::withTrashed()->exists()) {
            $this->command?->info('Buildings / rooms already exist; starter rooms skipped.');

            return;
        }

        $workstation = workstations::query()->orderBy('workstation_name')->first();

        if (! $workstation) {
            $this->command?->warn('No workstation yet; starter buildings and rooms skipped.');

            return;
        }

        foreach (workstations::all() as $station) {
            building::create(['name' => 'Main Block', 'workstation_id' => $station->id, 'added_by' => $addedBy]);
        }

        $mainBlock = building::where('workstation_id', $workstation->id)->first();

        foreach (departments::all() as $department) {
            $rooms = self::ROOMS[$department->name] ?? [[$department->name.' Office', Str::upper(Str::substr(Str::slug($department->name, ''), 0, 6)).'-01']];

            foreach ($rooms as [$name, $code]) {
                facilitylocation::create([
                    'name' => $name,
                    'code' => $code,
                    'building_id' => $mainBlock->id,
                    'workstation_id' => $workstation->id,
                    'department_id' => $department->id,
                    'added_by' => $addedBy,
                ]);
            }
        }
    }
}
