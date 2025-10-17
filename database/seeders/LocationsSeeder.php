<?php

namespace Database\Seeders;

use App\Models\countries;
use App\Models\districts;
use App\Models\regions;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class LocationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Fetch data from the API
        $response = Http::get('https://api.first.org/data/v1/countries');

        // Decode response
        if ($response->successful()) {
            $countries = $response->json()['data'];

            $insertData = [];
            foreach ($countries as $code => $details) {
                $insertData[] = [
                    'code' => $code,
                    'name' => $details['country'],
                ];
            }

            // Insert into the database
            
            foreach ($insertData as $data) {
               countries::create($data); 
            }
        } else {
            $this->command->error('Failed to fetch country data.');
        }

        $regions =[
            [
                "name"=> "Arusha",
                "districts" => [
                
                    ["name"=> "Arumeru Magharibi"],
                
                    ["name"=> "Arumeru Mashariki"],
                
                    ["name"=> "Arusha Mjini "],
                
                    ["name"=> "Karatu"],
                
                    ["name"=> "Longido"],
                
                    ["name"=> "Monduli"]
                ]
            ],    
            [
                "name"=> "Dar es Salaam",
                "districts" => [
                
                    ["name"=> "Kinondoni"],
                
                    ["name"=> "Temeke"],
                
                    ["name"=> "Ilala"],
                
                    ["name"=> "Ubungo"],
                
                    ["name"=> "Kigamboni"]
                ]
            ],
        
            [
                "name"=> "Dodoma",
                "districts" => [
                
                    ["name"=> "Bahi"],
                
                    ["name"=> "Chamwino"],
                
                    ["name"=> "Chemba"],
                
                    ["name"=> "Dodoma"],
                
                    ["name"=> "Kondoa"],
                
                    ["name"=> "Kongwa"],
                
                    ["name"=> "Mpwapwa"]
                ]
            ],
        
            [
                "name"=> "Iringa",
                "districts" => [
                
                    ["name"=> "Bahi"],
                
                    ["name"=> "Chamwino"],
                
                    ["name"=> "Chemba"],
                
                    ["name"=> "Dodoma Mjini"],
                
                    ["name"=> "Dodoma Vijijini"],
                
                    ["name"=> "Kondoa"],
                
                    ["name"=> "Kongwa"],
                
                    ["name"=> "Mpwapwa"]
                ]
            ],
        
            [   
                "name"=> "Kagera",
                "districts" => [
                
                    ["name"=> "Biharamulo"],
                
                    ["name"=> "Bukoba Mjini"],
                
                    ["name"=> "Bukoba Vijijini"],
                
                    ["name"=> "Karagwe"],
                
                    ["name"=> "Missenyi"],
                
                    ["name"=> "Muleba"],
                
                    ["name"=> "Ngara"],
                
                    ["name"=> "Kyerwa"]
                ]
            ],
            [
                "name"=> "Kaskazini Pemba",
                "districts" => [
                
                    ["name"=> "Micheweni"],
                
                    ["name"=> "Wete"]
                ]
            ],    
            [
                "name"=> "Kaskazini Unguja",
                "districts" => [
                
                    ["name"=> "Unguja Kaskazini A"],
                
                    ["name"=> "Unguja Kaskazini B"]
                ]
            ],
        
            [
                "name"=> "Kigoma",
                "districts" => [
                
                    ["name"=> "Kasulu"],
                
                    ["name"=> "Kibondo"],
                
                    ["name"=> "Kigoma Vijijini"],
                
                    ["name"=> "Kigoma Mjini"],
                
                    ["name"=> "Kigoma Ujiji"],
                
                    ["name"=> "Buhigwe"],
                
                    ["name"=> "Kakonko"],
                
                    ["name"=> "Uvinza"]
                ]
            ],
        
            [
                "name"=> "Kilimanjaro",
                "districts" => [
                
                    ["name"=> "Hai"],
                
                    ["name"=> "Moshi Mjini"],
                
                    ["name"=> "Moshi Vijijini"],
                
                    ["name"=> "Mwanga"],
                
                    ["name"=> "Rombo"],
                
                    ["name"=> "Same"],
                
                    ["name"=> "Siha"]
                ]
            ],
        
            [
                "name"=> "Kusini Pemba",
                "districts" => [
                
                    ["name"=> "Chake Chake"],
                
                    ["name"=> "Mkoani"]
                ]
            ],
        
            [
                "name"=> "Kusini Unguja",
                "districts" => [
                
                    ["name"=> "Kati Unguja"],
                
                    ["name"=> "Kusini Unguja"]
                ]
            ],
        
            [
                "name"=> "Lindi",
                "districts" => [
                
                    ["name"=> "Kilwa"],
                
                    ["name"=> "Lindi Mjini"],
                
                    ["name"=> "Lindi Vijijini"],
                
                    ["name"=> "Nachingwea"],
                
                    ["name"=> "Ruangwa"],
                
                    ["name"=> "Liwale"]
                ]
            ],
        
            [   "name"=> "Manyara",
                "districts" => [
                
                    ["name"=> "Babati"],
                
                    ["name"=> "Babati Vijijini"],
                
                    ["name"=> "Hanang"],
                
                    ["name"=> "Kiteto"],
                
                    ["name"=> "Mbulu"],
                
                    ["name"=> "Simanjiro"]
                ]
            ],
        
            [
                "name"=> "Mara",
                "districts" => [
                
                    ["name"=> "Bunda"],
                
                    ["name"=> "Musoma Mjini"],
                
                    ["name"=> "Musoma Vijijini"],
                
                    ["name"=> "Rorya"],
                
                    ["name"=> "Serengeti"],
                
                    ["name"=> "Tarime"]
                ]
            ],
        
            [
                "name"=> "Mbeya",
                "districts" => [
                
                    ["name"=> "Chunya"],
                
                    ["name"=> "Ileje"],
                
                    ["name"=> "Kyela"],
                
                    ["name"=> "Mbarali"],
                
                    ["name"=> "Mbeya Mjini"],
                
                    ["name"=> "Mbeya Vijijini"],
                
                    ["name"=> "Mbozi"]
                ]
            ],
        
            [
                "name"=> "Mjini Magharibi",
                "districts" => [
                
                    ["name"=> "Magharibi Unguja"],
                
                    ["name"=> "Mjini Unguja"]
                ]
            ],
        
            [
                "name"=> "Morogoro",
                "districts" => [
                
                    ["name"=> "Gairo"],
                
                    ["name"=> "Morogoro Mjini"],
                
                    ["name"=> "Morogoro Vijijini"],
                
                    ["name"=> "Kilombero"],
                
                    ["name"=> "Kilosa"],
                
                    ["name"=> "Mvomero"],
                
                    ["name"=> "Malinyi"],
                
                    ["name"=> "Ulanga"]
                ]
            ],
        
            [
                "name"=> "Mtwara",
                "districts" => [
                
                    ["name"=> "Masasi"],
                
                    ["name"=> "Masasi Mjini"],
                
                    ["name"=> "Mtwara Mikindani"],
                
                    ["name"=> "Mtwara Mjini"],
                
                    ["name"=> "Mtwara Vijijini"],
                
                    ["name"=> "Newala"],
                
                    ["name"=> "Tandahimba"]
                ]
            ],
        
            [
                "name"=> "Mwanza",
                "districts" => [
                
                    ["name"=> "Geita"],
                
                    ["name"=> "Ilemela"],
                
                    ["name"=> "Kwimba"],
                
                    ["name"=> "Magu"],
                
                    ["name"=> "Misungwi"],
                
                    ["name"=> "Nyamagana"],
                
                    ["name"=> "Sengerema"],
                
                    ["name"=> "Ukerewe"]
                ]
            ],
        
            [
                "name"=> "Pwani",
                "districts" => [
                
                    ["name"=> "Bagamoyo"],
                
                    ["name"=> "Kibaha"],
                
                    ["name"=> "Kibiti"],
                
                    ["name"=> "Kisarawe"],
                
                    ["name"=> "Mafia"],
                
                    ["name"=> "Mkuranga"],
                
                    ["name"=> "Rufiji"]
                ]
            ],
        
            [
                "name"=> "Rukwa",
                "districts" => [
                
                    ["name"=> "Mpanda"],
                
                    ["name"=> "Nkansi"],
                
                    ["name"=> "Sumbawanga Mjini"],
                
                    ["name"=> "Sumbawanga Vijijini"],
                
                    ["name"=> "Kalambo"]
                ]
            ],
        
            [
                "name"=> "Ruvuma",
                "districts" => [
                
                    ["name"=> "Mbinga"],
                
                    ["name"=> "Namtumbo"],
                
                    ["name"=> "Songea Mjini"],
                
                    ["name"=> "Songea Vijijini"],
                
                    ["name"=> "Tunduru"],
                
                    ["name"=> "Nyasa"]
                ]
            ],
        
            [
                "name"=> "Shinyanga",
                "districts" => [            
                    ["name"=> "Bariadi"],
                
                    ["name"=> "Shinyanga Mjini"],
                
                    ["name"=> "Shinyanga Vijijini"],
                
                    ["name"=> "Bukombe"],
                
                    ["name"=> "Kahama Mjini"],
                
                    ["name"=> "Kahama Vijijini"],
                
                    ["name"=> "Kishapu"],
                
                    ["name"=> "Maswa"],
                
                    ["name"=> "Meatu"]
                ]
            ],
        
            [
                "name"=> "Singida",
                "districts" => [
                
                    ["name"=> "Iramba"],
                
                    ["name"=> "Manyoni"],
                
                    ["name"=> "Singida Mjini"],
                
                    ["name"=> "Singida Vijijini"],
                
                    ["name"=> "Ikungi"],
                
                    ["name"=> "Mkalama"]
                ]
            ],
        
            [
                "name"=> "Tabora",
                "districts" => [
                
                    ["name"=> "Igunga"],
                
                    ["name"=> "Tabora Mjini"],
                
                    ["name"=> "Nzega"],
                
                    ["name"=> "Sikonge"],
                
                    ["name"=> "Urambo"],
                
                    ["name"=> "Uyui"],
                
                    ["name"=> "Kaliua"]
                ]
            ],
        
            [
                "name"=> "Tanga",
                "districts" => [
                
                    ["name"=> "Handeni"],
                
                    ["name"=> "Handeni Mjini"],
                
                    ["name"=> "Handeni Vijijini"],
                
                    ["name"=> "Korogwe"],
                
                    ["name"=> "Lushoto"],
                
                    ["name"=> "Mkinga"],
                
                    ["name"=> "Muheza"],
                
                    ["name"=> "Pangani"],
                
                    ["name"=> "Tanga"]
                ]
            ],
        ];
        $country_id = countries::where('code', 'TZ')->first()->id;
        foreach ($regions as $regionData) {
            $region = regions::create(['name' => $regionData['name'], 'country_id' => $country_id]);

            foreach ($regionData['districts'] as $districtData) {
                districts::create([
                    'name' => $districtData['name'],
                    'region_id' => $region->id
                ]);
            }
        }
    }
}
