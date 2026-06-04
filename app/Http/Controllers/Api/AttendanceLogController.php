<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\employeeattendances;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Standalone receiver for fingerprint attendance logs.
 *
 * Laravel equivalent of the legacy minimal PHP receiver: it accepts a bare
 * JSON array of { user_id, timestamp } records, splits the timestamp into a
 * date and a time, and inserts them into the employeeattendances table while
 * skipping duplicate entries.
 */
class AttendanceLogController extends Controller
{
    /**
     * Receive and store attendance logs.
     *
     * Expected body (bare JSON array):
     *   [
     *     { "user_id": 12, "timestamp": "2025-08-11 06:58:00" },
     *     { "user_id": 15, "timestamp": "2025-08-11 07:02:13" }
     *   ]
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function receive(Request $request)
    {
        $data = $request->json()->all();

        // Allow a single record object as well as a bare array.
        if (isset($data['user_id']) || isset($data['timestamp'])) {
            $data = [$data];
        }

        Log::info('Attendance log receiver hit', [
            'ip' => $request->ip(),
            'count' => is_array($data) ? count($data) : 0,
        ]);

        if (! is_array($data) || empty($data)) {
            return response()->json([
                'status' => 'error',
                'message' => 'No attendance records received',
            ], 422);
        }

        $inserted = 0;
        $duplicates = 0;
        $failed = 0;

        foreach ($data as $value) {
            $userId = $value['user_id'] ?? null;
            $datetime = trim((string) ($value['timestamp'] ?? ''));

            if ($userId === null || $datetime === '') {
                $failed++;

                continue;
            }

            try {
                $parsed = Carbon::parse($datetime);
                $valuedate = $parsed->toDateString();  // 2025-08-11
                $valuetime = $parsed->toTimeString();  // 06:58:00

                // Skip duplicate entry: same user clocking at the same timestamp.
                $exists = employeeattendances::where('fpuser_id', $userId)
                    ->where('clocktimestamp', $datetime)
                    ->exists();

                if ($exists) {
                    $duplicates++;

                    continue;
                }

                employeeattendances::create([
                    'fpuser_id' => $userId,
                    'clocktimestamp' => $datetime,
                    'clockdate' => $valuedate,
                    'clocktime' => $valuetime,
                ]);

                $inserted++;
            } catch (\Throwable $e) {
                $failed++;
                Log::warning('Attendance record insert failed', [
                    'record' => $value,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return response()->json([
            'status' => $failed === 0 ? 'Success' : 'Partial',
            'message' => 'Attendance logs processed',
            'total' => count($data),
            'inserted' => $inserted,
            'duplicates' => $duplicates,
            'failed' => $failed,
        ], 200);
    }
}
