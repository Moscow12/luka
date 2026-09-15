<?php

namespace Database\Seeders;

use App\Models\districts;
use App\Models\wards;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class WardsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         // 1. Fetch JSON
        $url = 'https://raw.githubusercontent.com/Kijacode/Tanzania_Geo_Data/main/Wards.json';
        $response = Http::get($url);

        if (!$response->successful()) {
            $this->command->error("Could not fetch JSON from {$url}");
            return;
        }

        $json = $response->json();

        if (!isset($json['features']) || !is_array($json['features'])) {
            $this->command->error('JSON format invalid: "features" key is missing or not an array.');
            return;
        }

        // cache for districts to avoid duplicate DB queries
        $districtsCache = [];

        foreach ($json['features'] as $feature) {
            $properties = $feature['properties'] ?? null;
            if (!$properties) {
                continue;
            }

            // We expect keys like "District" and "Ward" inside properties
            if (!isset($properties['District']) || !isset($properties['Ward'])) {
                // skip malformed entry
                continue;
            }

            $districtName = trim($properties['District']);
            $wardName = trim($properties['Ward']);

            if ($districtName === '' || $wardName === '') {
                continue;
            }
            // dd($districtName);
            // Remove the word "District" from the statement
            $cleanedStatementname = Str::replace(' District', '', $districtName);
            // Get district
            $district = districts::where('name', 'like', '%' . $cleanedStatementname . '%')->first();
                    
            $districtsCache[$districtName] = $district;
            // Create ward or update existing ward or skip if district not found use if statement
            if ($district) {
                wards::firstOrCreate(
                    [
                        'name' => $wardName,
                        'district_id' => $district->id
                    ],
                    [
                        'created_at' => now(),
                        'updated_at' => now()
                    ]
                );
            }

            
        }

        $this->command->info('Wards seeding completed successfully.');
    }
}
