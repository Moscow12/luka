<?php

namespace App\Http\Controllers\Api\Concerns;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Validator;

/**
 * Shared helpers for the third-party sync API controllers
 * (Employee / Leave / Asset).
 *
 * Extracted verbatim from those controllers to remove duplication; the
 * behaviour is intended to match what each controller did individually.
 */
trait ResolvesSyncEntities
{
    /**
     * Resolve a department by (fuzzy) name, creating it if absent.
     */
    protected function resolveOrCreateDepartment(string $name, ?string $addedBy): ?string
    {
        $existing = DB::table('departments')
            ->where('name', 'LIKE', '%'.$name.'%')
            ->first();

        if ($existing) {
            return $existing->id;
        }

        // Departments require a supervisor job title; fall back to the first available.
        $firstJobTitle = DB::table('jobtitles')->first();

        $id = (string) Str::uuid();
        DB::table('departments')->insert([
            'id' => $id,
            'name' => $name,
            'description' => 'Auto-created department',
            'supervisor_title_id' => $firstJobTitle->id ?? null,
            'status' => 'active',
            'added_by' => $addedBy,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Log::info("Auto-created department: {$name}", ['id' => $id]);

        return $id;
    }

    /**
     * Coerce empty/blank string values in a request payload to null.
     * Remote exporters ship "" for missing values.
     */
    protected function normalizeEmptyStringsToNull(array $input): array
    {
        foreach ($input as $key => $value) {
            if (is_string($value) && trim($value) === '') {
                $input[$key] = null;
            }
        }

        return $input;
    }

    /**
     * Accepts ISO, DD/MM/YYYY, DD-MM-YYYY, or MySQL zero-date sentinels;
     * returns an ISO date string or null.
     */
    protected function normalizeDate(?string $value): ?string
    {
        if ($value === null || $value === '' || str_starts_with($value, '0000-')) {
            return null;
        }

        try {
            if (preg_match('#^(\d{2})[/-](\d{2})[/-](\d{4})$#', $value, $m)) {
                return \Carbon\Carbon::createFromFormat('d/m/Y', "{$m[1]}/{$m[2]}/{$m[3]}")->toDateString();
            }

            return \Carbon\Carbon::parse($value)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Build the standard 422 validation-failure JSON response shared by the
     * sync endpoints. $extra is merged into the payload (e.g. the entity's
     * reference id/name fields each controller echoes back).
     */
    protected function validationErrorResponse(Validator $validator, string $message, array $extra = [])
    {
        return response()->json($this->validationErrorPayload($validator, $message, $extra), 422);
    }

    /**
     * The body array for a 422 validation failure (without wrapping it in a
     * JsonResponse), so callers that aggregate results — e.g. a bulk handler —
     * can embed it directly.
     */
    protected function validationErrorPayload(Validator $validator, string $message, array $extra = []): array
    {
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
        if (! empty($missingFields)) {
            $errorSummary[] = 'Missing required fields: '.implode(', ', $missingFields);
        }
        if (! empty($invalidFields)) {
            $errorSummary[] = 'Invalid field values: '.implode(', ', $invalidFields);
        }

        return array_merge([
            'success' => false,
            'error_code' => 'VALIDATION_FAILED',
            'message' => $message,
            'error_summary' => implode('. ', $errorSummary),
            'detailed_errors' => $formattedErrors,
            'total_errors' => count($formattedErrors),
        ], $extra);
    }
}
