<?php

namespace Database\Seeders;

use App\Models\Engineering\OxygenPlant;
use App\Models\Engineering\ProblemCategory;
use App\Models\Engineering\Section;
use Illuminate\Database\Seeder;

/**
 * Master data for the engineering module: sections, the emergency criteria list and a
 * starter set of routine categories. Safe to run more than once.
 */
class EngineeringSeeder extends Seeder
{
    // code => [name, Font Awesome icon]
    protected const SECTIONS = [
        'MASONRY' => ['Masonry', 'fa-trowel-bricks'],
        'WELDING' => ['Welding', 'fa-fire-flame-curved'],
        'PAINTING' => ['Painting', 'fa-paint-roller'],
        'CARPENTRY' => ['Carpentry', 'fa-hammer'],
        'PLUMBING' => ['Plumbing', 'fa-faucet-drip'],
        'ESTATE' => ['Estate', 'fa-building'],
        'MECHANICAL' => ['Mechanical', 'fa-gears'],
        'ELECTRICAL' => ['Electrical', 'fa-bolt'],
        'BIOMEDICAL' => ['Biomedical', 'fa-heart-pulse'],
    ];

    // code => [sections, criterion]. Choosing one of these forces EMERGENCY priority.
    protected const EMERGENCY_CRITERIA = [
        'EM-01' => [['BIOMEDICAL'], 'Device breakdown while a patient is being operated on'],
        'EM-02' => [['BIOMEDICAL'], 'Breakdown of machine/device/equipment while a patient is using it in ICU/HDU'],
        'EM-03' => [['ELECTRICAL', 'MECHANICAL', 'ESTATE'], 'People trapped in a lift'],
        'EM-04' => [['PLUMBING'], 'Blocked toilet'],
        'EM-05' => [['PLUMBING'], 'Water pipe or sewage water burst'],
        'EM-06' => [['ELECTRICAL'], 'Overheating or fire break'],
        'EM-07' => [['BIOMEDICAL'], 'Biomedical device/machine/equipment breakdown while in use on a patient'],
        'EM-08' => [['MECHANICAL', 'BIOMEDICAL'], 'Lack of medical oxygen supply'],
        'EM-09' => [['MECHANICAL', 'BIOMEDICAL'], 'Medical oxygen leakage'],
        'EM-10' => [['CARPENTRY'], 'Someone locked in a room because the key is broken or stuck in the door'],
        'EM-11' => [['CARPENTRY'], 'Medical supplies cupboard or locker cannot open - key broken or failed'],
        'EM-12' => [['BIOMEDICAL', 'ELECTRICAL'], 'Device/machine/equipment abruptly stops functioning or shuts down'],
        'EM-13' => [['BIOMEDICAL', 'ELECTRICAL'], 'Device/machine/equipment fails to start despite a previous proper shut-down'],
        'EM-14' => [['BIOMEDICAL'], 'Unexpected errors flickering on the machine screen'],
        'EM-15' => [['ELECTRICAL', 'ESTATE'], 'AC malfunctioning in a temperature-controlled room housing equipment such as MRI, CT scan, Cobalt-60'],
        'EM-16' => [['ELECTRICAL', 'MECHANICAL'], 'Generator fails to start after a power cut'],
        'EM-17' => [['ELECTRICAL'], 'Lack of power in an isolated area while the rest of the hospital has electricity'],
    ];

    // Starter list only: the engineering department maintains these under Engineering > Setup
    protected const ROUTINE_CATEGORIES = [
        'MASONRY' => ['Cracked wall or floor', 'Broken tiles', 'Damaged pavement or kerb'],
        'WELDING' => ['Broken bed or trolley frame', 'Damaged gate, grill or railing'],
        'PAINTING' => ['Wall repainting', 'Road or parking markings'],
        'CARPENTRY' => ['Loose door hinge or lock', 'Broken window frame', 'Furniture repair'],
        'PLUMBING' => ['Leaking tap', 'Slow drain', 'Low water pressure'],
        'ESTATE' => ['Grounds and compound maintenance', 'Roof leakage', 'AC servicing'],
        'MECHANICAL' => ['Abnormal noise or vibration', 'Routine servicing', 'Medical gas outlet fault'],
        'ELECTRICAL' => ['Faulty socket or switch', 'Light not working', 'Tripping circuit breaker'],
        'BIOMEDICAL' => ['Calibration request', 'Accessory or probe replacement', 'Intermittent fault, not in use on a patient'],
    ];

    public function run(): void
    {
        $sections = [];
        $order = 0;

        foreach (self::SECTIONS as $code => [$name, $icon]) {
            $sections[$code] = Section::firstOrCreate(['code' => $code], [
                'name' => $name,
                'icon' => $icon,
                'sort_order' => ++$order,
            ]);
        }

        foreach (self::EMERGENCY_CRITERIA as $code => [$sectionCodes, $criterion]) {
            foreach ($sectionCodes as $sectionCode) {
                ProblemCategory::firstOrCreate(
                    ['section_id' => $sections[$sectionCode]->id, 'emergency_code' => $code],
                    ['name' => $criterion],
                );
            }
        }

        foreach (self::ROUTINE_CATEGORIES as $sectionCode => $names) {
            foreach ([...$names, 'Other routine work'] as $name) {
                ProblemCategory::firstOrCreate(['section_id' => $sections[$sectionCode]->id, 'name' => $name]);
            }
        }

        if (OxygenPlant::count() === 0) {
            foreach (['Oxygen plant 1'] as $name) {
                OxygenPlant::create(['name' => $name]);
            }
        }
    }
}
