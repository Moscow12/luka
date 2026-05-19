<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\asset;
use App\Models\assetregistry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class AssetController extends Controller
{
    /**
     * Receive asset registry entry from third-party system
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            // Remote exporters send "" for missing values; coerce to null before validating.
            $input = $this->normalizeInput($request->all());
            $request->replace($input);

            $validator = Validator::make($input, [
                'asset_registry_id' => 'nullable|string|max:100',
                'asset_id'          => 'nullable|string|max:100',
                'asset_name'        => 'required|string|max:255',
                'asset_type'        => 'nullable|string|max:100',
                'department_id'     => 'nullable|string|max:100',
                'department'        => 'nullable|string|max:255',
                'building_id'       => 'nullable|string|max:100',
                'description'       => 'nullable|string',
                'location'          => 'nullable|string|max:255',
                'asset_class_id'    => 'nullable|string|max:100',
                'condition'         => 'nullable|string|max:50',
                'application'       => 'nullable|string|max:255',
                'make'              => 'nullable|string|max:255',
                'model'             => 'nullable|string|max:255',
                'serial_no'         => 'nullable|string|max:255',
                'code_no'           => 'nullable|string|max:255',
                'replacement_cost'  => 'nullable|numeric|min:0',
                'depreciated_cost'  => 'nullable|numeric|min:0',
                'purchase_date'     => 'nullable|date',
                'kind'              => 'nullable|string|in:registry,catalogue',
            ]);

            if ($validator->fails()) {
                Log::error('Asset Sync Validation Failed', [
                    'asset_registry_id' => $request->asset_registry_id ?? 'N/A',
                    'asset_name'        => $request->asset_name ?? 'N/A',
                    'request_data'      => $request->all(),
                    'validation_errors' => $validator->errors(),
                ]);

                $formattedErrors = [];
                $missingFields = [];
                $invalidFields = [];

                foreach ($validator->errors()->messages() as $field => $messages) {
                    $formattedErrors[$field] = $messages[0];

                    if (str_contains($messages[0], 'required')) {
                        $missingFields[] = $field;
                    } else {
                        $invalidFields[] = $field;
                    }
                }

                $errorSummary = [];
                if (!empty($missingFields)) {
                    $errorSummary[] = 'Missing required fields: ' . implode(', ', $missingFields);
                }
                if (!empty($invalidFields)) {
                    $errorSummary[] = 'Invalid field values: ' . implode(', ', $invalidFields);
                }

                return response()->json([
                    'success'           => false,
                    'error_code'        => 'VALIDATION_FAILED',
                    'message'           => 'Asset sync validation failed',
                    'error_summary'     => implode('. ', $errorSummary),
                    'detailed_errors'   => $formattedErrors,
                    'total_errors'      => count($formattedErrors),
                    'asset_registry_id' => $request->asset_registry_id ?? null,
                    'asset_name'        => $request->asset_name ?? null,
                ], 422);
            }

            DB::beginTransaction();

            $firstUserId = DB::table('users')->first()->id ?? null;

            // Catalogue-only payload (kind=catalogue) carries no registry fields —
            // just upsert the asset catalogue row and return.
            if (($request->kind ?? 'registry') === 'catalogue') {
                $assetClassId = $this->resolveOrCreateAssetClass('General', $firstUserId);
                $assetId = $this->resolveOrCreateAsset(
                    $request->asset_name,
                    $request->asset_type ?: 'physical',
                    $assetClassId,
                    $firstUserId
                );

                if (!$assetId) {
                    DB::rollBack();
                    return response()->json([
                        'success'       => false,
                        'error_code'    => 'ASSET_CREATE_FAILED',
                        'message'       => 'Failed to create asset catalogue entry',
                        'error_summary' => "Could not create asset '{$request->asset_name}'",
                        'asset_name'    => $request->asset_name,
                    ], 422);
                }

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Asset catalogue entry synced successfully',
                    'data'    => [
                        'asset' => asset::find($assetId),
                        'kind'  => 'catalogue',
                    ],
                ], 201);
            }

            // Resolve or create asset class
            $assetClassId = $this->resolveOrCreateAssetClass($request->application ?: 'General', $firstUserId);

            // Resolve or create department
            $departmentId = $this->resolveOrCreateDepartment($request->department ?: 'General', $firstUserId);

            // Resolve or create workstation (needed by buildings, facilitylocations)
            $workstationId = $this->resolveOrCreateWorkstation('Main Office', $firstUserId);

            // Resolve or create building
            $buildingId = $this->resolveOrCreateBuilding($request->building_id ?: 'Main Building', $workstationId, $firstUserId);

            // Resolve or create facility location
            $facilityLocationId = $this->resolveOrCreateFacilityLocation(
                $request->location ?: 'Main Location',
                $buildingId,
                $workstationId,
                $firstUserId
            );

            // Resolve or create the asset (catalog entry)
            $assetId = $this->resolveOrCreateAsset(
                $request->asset_name,
                $request->asset_type ?: 'physical',
                $assetClassId,
                $firstUserId
            );

            if (!$assetClassId || !$departmentId || !$workstationId || !$buildingId || !$facilityLocationId || !$assetId) {
                DB::rollBack();

                $failedFields = [];
                if (!$assetClassId)       $failedFields[] = "asset_class ('{$request->application}')";
                if (!$departmentId)       $failedFields[] = "department ('{$request->department}')";
                if (!$workstationId)      $failedFields[] = "workstation";
                if (!$buildingId)         $failedFields[] = "building ('{$request->building_id}')";
                if (!$facilityLocationId) $failedFields[] = "location ('{$request->location}')";
                if (!$assetId)            $failedFields[] = "asset ('{$request->asset_name}')";

                Log::error('Asset Sync Field Resolution Failed', [
                    'asset_registry_id' => $request->asset_registry_id ?? 'N/A',
                    'failed_fields'     => $failedFields,
                ]);

                return response()->json([
                    'success'           => false,
                    'error_code'        => 'FIELD_RESOLUTION_FAILED',
                    'message'           => 'Failed to resolve or create required asset fields',
                    'error_summary'     => 'Could not find or create the following fields: ' . implode(', ', $failedFields),
                    'failed_fields'     => $failedFields,
                    'asset_registry_id' => $request->asset_registry_id ?? null,
                    'hint'              => 'Please ensure the field names exist in the database or contact administrator',
                ], 422);
            }

            // Check if registry entry already exists (by serial_no or code_no)
            $existingRegistry = null;
            if (!empty($request->serial_no)) {
                $existingRegistry = assetregistry::where('serial_number', $request->serial_no)->first();
            }
            if (!$existingRegistry && !empty($request->code_no)) {
                $existingRegistry = assetregistry::where('codeno', $request->code_no)->first();
            }

            $payload = [
                'asset_class_id'       => $assetClassId,
                'facility_location_id' => $facilityLocationId,
                'asset_id'             => $assetId,
                'workstation_id'       => $workstationId,
                'building_id'          => $buildingId,
                'department_id'        => $departmentId,
                'description'          => $request->description,
                'status'               => 'active',
                'serial_number'        => $request->serial_no ?: null,
                'purchase_date'        => $request->purchase_date ?: null,
                'purchase_cost'        => $request->replacement_cost ?: null,
                'condition'            => $request->condition ?: null,
                'depreciation'         => $request->depreciated_cost ?: 0,
                'model'                => $request->model ?: null,
                'make'                 => $request->make ?: null,
                'codeno'               => $request->code_no ?: null,
                'added_by'             => $firstUserId,
            ];

            if ($existingRegistry) {
                $existingRegistry->update($payload);

                DB::commit();

                return response()->json([
                    'success'       => false,
                    'error_code'    => 'ASSET_ALREADY_EXISTS',
                    'message'       => 'Asset registry already exists, updated successfully',
                    'error_summary' => "An asset registry with serial '{$request->serial_no}' or code '{$request->code_no}' already exists",
                    'data'          => [
                        'asset_registry' => $existingRegistry->load(['asset', 'assetClass', 'department', 'building', 'facilityLocation']),
                    ],
                ], 200);
            }

            $registry = assetregistry::create($payload);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Asset registry synced successfully',
                'data'    => [
                    'asset_registry' => $registry->load(['asset', 'assetClass', 'department', 'building', 'facilityLocation']),
                ],
            ], 201);

        } catch (ValidationException $e) {
            DB::rollBack();

            Log::error('Asset Sync Validation Exception', [
                'asset_registry_id' => $request->asset_registry_id ?? 'N/A',
                'asset_name'        => $request->asset_name ?? 'N/A',
                'exception'         => $e->getMessage(),
                'errors'            => $e->errors(),
            ]);

            return response()->json([
                'success'           => false,
                'error_code'        => 'VALIDATION_EXCEPTION',
                'message'           => 'Asset sync validation exception occurred',
                'error_summary'     => $e->getMessage(),
                'detailed_errors'   => $e->errors(),
                'asset_registry_id' => $request->asset_registry_id ?? null,
            ], 422);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Asset Sync Failed', [
                'asset_registry_id' => $request->asset_registry_id ?? 'N/A',
                'asset_name'        => $request->asset_name ?? 'N/A',
                'exception'         => $e->getMessage(),
                'trace'             => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success'           => false,
                'error_code'        => 'ASSET_SYNC_FAILED',
                'message'           => 'Failed to sync asset due to server error',
                'error_summary'     => $e->getMessage(),
                'asset_registry_id' => $request->asset_registry_id ?? null,
                'hint'              => 'Please check the data format and try again, or contact administrator if the issue persists',
            ], 500);
        }
    }

    /**
     * Resolve or create asset class
     */
    private function resolveOrCreateAssetClass(string $name, ?string $addedBy): ?string
    {
        $existing = DB::table('assetclasses')
            ->where('name', 'LIKE', '%' . $name . '%')
            ->first();

        if ($existing) {
            return $existing->id;
        }

        $id = (string) Str::uuid();
        DB::table('assetclasses')->insert([
            'id'                  => $id,
            'name'                => $name,
            'depreciation'        => '0',
            'depreciation_method' => 'straight_line',
            'useful_life_years'   => null,
            'depreciation_rate'   => null,
            'added_by'            => $addedBy,
            'created_at'          => now(),
            'updated_at'          => now(),
        ]);

        Log::info("Auto-created asset class: {$name}", ['id' => $id]);
        return $id;
    }

    /**
     * Resolve or create department
     */
    private function resolveOrCreateDepartment(string $name, ?string $addedBy): ?string
    {
        $existing = DB::table('departments')
            ->where('name', 'LIKE', '%' . $name . '%')
            ->first();

        if ($existing) {
            return $existing->id;
        }

        $firstJobTitle = DB::table('jobtitles')->first();

        $id = (string) Str::uuid();
        DB::table('departments')->insert([
            'id'                  => $id,
            'name'                => $name,
            'description'         => 'Auto-created department',
            'supervisor_title_id' => $firstJobTitle->id ?? null,
            'status'              => 'active',
            'added_by'            => $addedBy,
            'created_at'          => now(),
            'updated_at'          => now(),
        ]);

        Log::info("Auto-created department: {$name}", ['id' => $id]);
        return $id;
    }

    /**
     * Resolve or create workstation
     */
    private function resolveOrCreateWorkstation(string $name, ?string $addedBy): ?string
    {
        $existing = DB::table('workstations')
            ->where('workstation_name', 'LIKE', '%' . $name . '%')
            ->first();

        if ($existing) {
            return $existing->id;
        }

        $country = DB::table('countries')->where('name', 'LIKE', '%Tanzania%')->first()
            ?? DB::table('countries')->first();
        $region = DB::table('regions')->where('country_id', $country->id)->first()
            ?? DB::table('regions')->first();
        $district = DB::table('districts')->where('region_id', $region->id)->first()
            ?? DB::table('districts')->first();
        $ward = DB::table('wards')->where('district_id', $district->id)->first()
            ?? DB::table('wards')->first();

        $id = (string) Str::uuid();
        DB::table('workstations')->insert([
            'id'               => $id,
            'workstation_name' => $name,
            'location'         => 'Main Office',
            'phone_number'    => '0000000000',
            'tin_number'       => null,
            'email_address'    => null,
            'postal_code'      => null,
            'physical_address' => 'Auto-created',
            'country_id'       => $country->id,
            'region_id'        => $region->id,
            'district_id'      => $district->id,
            'ward_id'          => $ward->id,
            'added_by'         => $addedBy,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        Log::info("Auto-created workstation: {$name}", ['id' => $id]);
        return $id;
    }

    /**
     * Resolve or create building
     */
    private function resolveOrCreateBuilding(string $name, ?string $workstationId, ?string $addedBy): ?string
    {
        $existing = DB::table('buildings')
            ->where('name', 'LIKE', '%' . $name . '%')
            ->first();

        if ($existing) {
            return $existing->id;
        }

        $id = (string) Str::uuid();
        DB::table('buildings')->insert([
            'id'             => $id,
            'name'           => $name,
            'workstation_id' => $workstationId,
            'added_by'       => $addedBy,
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        Log::info("Auto-created building: {$name}", ['id' => $id]);
        return $id;
    }

    /**
     * Resolve or create facility location
     */
    private function resolveOrCreateFacilityLocation(string $name, ?string $buildingId, ?string $workstationId, ?string $addedBy): ?string
    {
        $existing = DB::table('facilitylocations')
            ->where('name', 'LIKE', '%' . $name . '%')
            ->where('building_id', $buildingId)
            ->first();

        if ($existing) {
            return $existing->id;
        }

        $id = (string) Str::uuid();
        DB::table('facilitylocations')->insert([
            'id'             => $id,
            'name'           => $name,
            'building_id'    => $buildingId,
            'workstation_id' => $workstationId,
            'added_by'       => $addedBy,
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        Log::info("Auto-created facility location: {$name}", ['id' => $id]);
        return $id;
    }

    /**
     * Resolve or create asset catalog entry
     */
    private function resolveOrCreateAsset(string $name, string $type, ?string $assetClassId, ?string $addedBy): ?string
    {
        $existing = DB::table('assets')
            ->where('name', $name)
            ->first();

        if ($existing) {
            return $existing->id;
        }

        try {
            $id = (string) Str::uuid();
            DB::table('assets')->insert([
                'id'             => $id,
                'name'           => $name,
                'type'           => $type ?: 'physical',
                'asset_class_id' => $assetClassId,
                'added_by'       => $addedBy,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);

            Log::info("Auto-created asset: {$name}", ['id' => $id]);
            return $id;
        } catch (\Exception $e) {
            Log::warning("Failed to create asset '{$name}', retrying lookup: {$e->getMessage()}");
            $existing = DB::table('assets')->where('name', $name)->first();
            return $existing->id ?? null;
        }
    }

    /**
     * Remote exporters ship "" for missing values; coerce empties to null so
     * `date`/`numeric` validators don't reject perfectly valid payloads.
     */
    private function normalizeInput(array $input): array
    {
        foreach ($input as $key => $value) {
            if (is_string($value) && trim($value) === '') {
                $input[$key] = null;
            }
        }
        return $input;
    }

    /**
     * Get all asset registry entries
     */
    public function index()
    {
        try {
            $registries = assetregistry::with(['asset', 'assetClass', 'department', 'building', 'facilityLocation'])
                ->latest()
                ->paginate(50);

            return response()->json([
                'success' => true,
                'data'    => $registries,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch asset registries',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get single asset registry entry
     */
    public function show(string $id)
    {
        try {
            $registry = assetregistry::with(['asset', 'assetClass', 'department', 'building', 'facilityLocation'])
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data'    => $registry,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Asset registry not found',
                'error'   => $e->getMessage(),
            ], 404);
        }
    }
}
