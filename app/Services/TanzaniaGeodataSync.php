<?php

namespace App\Services;

use App\Models\countries;
use App\Models\districts;
use App\Models\regions;
use App\Models\street;
use App\Models\wards;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Synchronises the Tanzania geo-location hierarchy
 * (country -> region -> district -> ward -> street) from the bundled
 * NBS dataset in public/tanzania_full_geodata.json.
 *
 * The sync is idempotent: locations are matched by name within their parent,
 * existing rows are kept and any missing rows are inserted, so the button can
 * be pressed repeatedly without creating duplicates.
 *
 * To complete inside a single web request the importer avoids per-row queries:
 * it loads each level's existing rows into an in-memory map once, then writes
 * new rows with chunked bulk inserts.
 */
class TanzaniaGeodataSync
{
    /** Relative path (within public/) to the source dataset. */
    public const SOURCE_FILE = 'tanzania_full_geodata.json';

    /** Rows per bulk insert batch. */
    protected const INSERT_CHUNK = 1000;

    /**
     * Counters describing what the last run touched.
     *
     * @var array<string,int>
     */
    protected array $stats = [
        'regions_created' => 0,
        'regions_matched' => 0,
        'districts_created' => 0,
        'districts_matched' => 0,
        'wards_created' => 0,
        'wards_matched' => 0,
        'streets_created' => 0,
        'streets_matched' => 0,
    ];

    /**
     * Run the import and return the per-entity statistics.
     *
     * @return array<string,int>
     */
    public function sync(): array
    {
        $data = $this->loadDataset();

        DB::transaction(function () use ($data) {
            $country = $this->resolveCountry($data['country'] ?? 'Tanzania');
            $rows = $data['data'] ?? [];

            $regionMap = $this->syncRegions($country, $rows);
            $districtMap = $this->syncDistricts($regionMap, $rows);
            $wardMap = $this->syncWards($regionMap, $districtMap, $rows);
            $this->syncStreets($regionMap, $districtMap, $wardMap, $rows);
        });

        return $this->stats;
    }

    /**
     * Read and decode the bundled dataset.
     *
     * @return array<string,mixed>
     */
    protected function loadDataset(): array
    {
        $path = public_path(self::SOURCE_FILE);

        if (! is_file($path)) {
            throw new RuntimeException('Geodata file not found: '.self::SOURCE_FILE);
        }

        $decoded = json_decode((string) file_get_contents($path), true);

        if (! is_array($decoded) || ! isset($decoded['data'])) {
            throw new RuntimeException('Geodata file is malformed or empty.');
        }

        return $decoded;
    }

    protected function resolveCountry(string $name): countries
    {
        $name = $this->clean($name) ?: 'Tanzania';

        return countries::firstOrCreate(
            ['name' => $name],
            ['code' => 'TZ', 'shortcode' => 'TZ'],
        );
    }

    /**
     * Insert any missing regions for the country and return a
     * [lowercased name => id] lookup of every region.
     *
     * @param  array<int,array<string,mixed>>  $rows
     * @return array<string,string>
     */
    protected function syncRegions(countries $country, array $rows): array
    {
        $existing = regions::where('country_id', $country->id)
            ->pluck('id', 'name');

        $map = [];
        foreach ($existing as $name => $id) {
            $map[mb_strtolower($name)] = $id;
        }

        $insert = [];
        $now = now();
        foreach ($rows as $node) {
            $name = $this->clean($node['region'] ?? '');
            $key = mb_strtolower($name);

            if (isset($map[$key])) {
                $this->stats['regions_matched']++;

                continue;
            }

            $id = (string) Str::orderedUuid();
            $map[$key] = $id;
            $insert[] = [
                'id' => $id,
                'name' => $name,
                'country_id' => $country->id,
                'created_at' => $now,
                'updated_at' => $now,
            ];
            $this->stats['regions_created']++;
        }

        $this->bulkInsert(regions::class, $insert);

        return $map;
    }

    /**
     * @param  array<string,string>  $regionMap  region name(lower) => id
     * @param  array<int,array<string,mixed>>  $rows
     * @return array<string,string> "regionId|districtName(lower)" => district id
     */
    protected function syncDistricts(array $regionMap, array $rows): array
    {
        $map = [];
        foreach (districts::select('id', 'name', 'region_id')->get() as $d) {
            $map[$d->region_id.'|'.mb_strtolower($d->name)] = $d->id;
        }

        $insert = [];
        $now = now();
        foreach ($rows as $regionNode) {
            $regionId = $regionMap[mb_strtolower($this->clean($regionNode['region'] ?? ''))] ?? null;
            if (! $regionId) {
                continue;
            }

            foreach ($regionNode['districts'] ?? [] as $node) {
                $name = $this->clean($node['district'] ?? '');
                $key = $regionId.'|'.mb_strtolower($name);

                if (isset($map[$key])) {
                    $this->stats['districts_matched']++;

                    continue;
                }

                $id = (string) Str::orderedUuid();
                $map[$key] = $id;
                $insert[] = [
                    'id' => $id,
                    'name' => $name,
                    'region_id' => $regionId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                $this->stats['districts_created']++;
            }
        }

        $this->bulkInsert(districts::class, $insert);

        return $map;
    }

    /**
     * @param  array<string,string>  $regionMap  region name(lower) => id
     * @param  array<string,string>  $districtMap  "regionId|districtName(lower)" => district id
     * @param  array<int,array<string,mixed>>  $rows
     * @return array<string,string> "districtId|wardName(lower)" => ward id
     */
    protected function syncWards(array $regionMap, array $districtMap, array $rows): array
    {
        $map = [];
        foreach (wards::select('id', 'name', 'district_id')->get() as $w) {
            $map[$w->district_id.'|'.mb_strtolower($w->name)] = $w->id;
        }

        $insert = [];
        $now = now();
        foreach ($rows as $regionNode) {
            $regionId = $regionMap[mb_strtolower($this->clean($regionNode['region'] ?? ''))] ?? null;
            if (! $regionId) {
                continue;
            }

            foreach ($regionNode['districts'] ?? [] as $districtNode) {
                $districtId = $districtMap[$regionId.'|'.mb_strtolower($this->clean($districtNode['district'] ?? ''))] ?? null;
                if (! $districtId) {
                    continue;
                }

                foreach ($districtNode['wards'] ?? [] as $wardNode) {
                    // The full dataset stores wards as objects with a "streets"
                    // array; the lighter dataset stores plain name strings.
                    $name = $this->clean(is_string($wardNode) ? $wardNode : ($wardNode['ward'] ?? ''));
                    $key = $districtId.'|'.mb_strtolower($name);

                    if (isset($map[$key])) {
                        $this->stats['wards_matched']++;

                        continue;
                    }

                    $id = (string) Str::orderedUuid();
                    $map[$key] = $id;
                    $insert[] = [
                        'id' => $id,
                        'name' => $name,
                        'district_id' => $districtId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                    $this->stats['wards_created']++;
                }
            }
        }

        $this->bulkInsert(wards::class, $insert);

        return $map;
    }

    /**
     * @param  array<string,string>  $regionMap  region name(lower) => id
     * @param  array<string,string>  $districtMap  "regionId|districtName(lower)" => district id
     * @param  array<string,string>  $wardMap  "districtId|wardName(lower)" => ward id
     * @param  array<int,array<string,mixed>>  $rows
     */
    protected function syncStreets(array $regionMap, array $districtMap, array $wardMap, array $rows): void
    {
        // Existing streets keyed by "wardId|streetName(lower)".
        $existing = [];
        foreach (street::select('id', 'name', 'ward_id')->get() as $s) {
            $existing[$s->ward_id.'|'.mb_strtolower($s->name)] = true;
        }

        $insert = [];
        $now = now();
        foreach ($rows as $regionNode) {
            $regionId = $regionMap[mb_strtolower($this->clean($regionNode['region'] ?? ''))] ?? null;
            if (! $regionId) {
                continue;
            }

            foreach ($regionNode['districts'] ?? [] as $districtNode) {
                $districtId = $districtMap[$regionId.'|'.mb_strtolower($this->clean($districtNode['district'] ?? ''))] ?? null;
                if (! $districtId) {
                    continue;
                }

                foreach ($districtNode['wards'] ?? [] as $wardNode) {
                    if (is_string($wardNode)) {
                        continue; // lighter dataset has no streets
                    }

                    $wardName = $this->clean($wardNode['ward'] ?? '');
                    $wardId = $wardMap[$districtId.'|'.mb_strtolower($wardName)] ?? null;
                    if (! $wardId) {
                        continue;
                    }

                    foreach ($wardNode['streets'] ?? [] as $streetName) {
                        $name = $this->clean($streetName);
                        $key = $wardId.'|'.mb_strtolower($name);

                        if (isset($existing[$key])) {
                            $this->stats['streets_matched']++;

                            continue;
                        }

                        // Guard against duplicate street names within the same
                        // ward inside the dataset itself.
                        $existing[$key] = true;
                        $insert[] = [
                            'id' => (string) Str::orderedUuid(),
                            'name' => $name,
                            'ward_id' => $wardId,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                        $this->stats['streets_created']++;
                    }
                }
            }
        }

        $this->bulkInsert(street::class, $insert);
    }

    /**
     * Chunked bulk insert. Uses the model's table; skips empty sets.
     *
     * @param  class-string  $model
     * @param  array<int,array<string,mixed>>  $rows
     */
    protected function bulkInsert(string $model, array $rows): void
    {
        if (empty($rows)) {
            return;
        }

        $table = (new $model)->getTable();

        foreach (array_chunk($rows, self::INSERT_CHUNK) as $chunk) {
            DB::table($table)->insert($chunk);
        }
    }

    /**
     * Normalise dataset values: collapse embedded newlines/whitespace and
     * title-case for consistent display (names in the JSON are lower-cased).
     */
    protected function clean(string $value): string
    {
        $value = preg_replace('/\s+/u', ' ', trim($value)) ?? '';

        return ucwords($value);
    }
}
