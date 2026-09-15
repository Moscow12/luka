<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Models\{Region, District, districts, regions, Ward, Street, wards};

class ImportAreas extends Command
{
    protected $signature = 'import:areas';
    protected $description = 'Import Regions, Districts, Wards, and Streets from GoT-HOMIS API';

    public function handle()
    {
        $url = 'http://196.192.73.16:50003/gothomis-uaa/api/v1/setup/external/all-areas';
        $response = Http::get($url);

        if (!$response->successful()) {
            $this->error('❌ Failed to fetch data from API');
            return;
        }

        $areas = $response->json();
        $this->info('✅ Fetched ' . count($areas) . ' regions from API.');

        foreach ($areas as $regionData) {
            $region = regions::updateOrCreate(
                ['name' => $regionData['region_name']],
                ['name' => $regionData['region_name']]
            );

            foreach ($regionData['districts'] as $districtData) {
                $district = districts::updateOrCreate(
                    ['name' => $districtData['district_name'], 'state_id' => $region->id],
                    ['state_id' => $region->id, 'name' => $districtData['district_name']]
                );

                foreach ($districtData['wards'] as $wardData) {
                    $ward = wards::updateOrCreate(
                        ['name' => $wardData['ward_name'], 'district_id' => $district->id],
                        ['district_id' => $district->id, 'name' => $wardData['ward_name']]
                    );

                    foreach ($wardData['streets'] as $streetData) {
                        Street::updateOrCreate(
                            ['name' => $streetData['street_name'], 'ward_id' => $ward->id],
                            ['ward_id' => $ward->id, 'name' => $streetData['street_name']]
                        );
                    }
                }
            }
        }

        $this->info('🎉 Areas imported successfully!');
    }
}
