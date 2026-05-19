<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\employeeattendances;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AttendanceController extends Controller
{
    /**
     * Store attendance log from fingerprint device
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        Log::info('Fingerprint attendance log received', [
            'ip' => $request->ip(),
            'payload' => $request->all(),
        ]);

        if (is_array($request->records)) {
            return $this->storeBulk($request);
        }

        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'device_ip' => 'required',
            'timestamp' => 'required',
            'clockdate' => 'nullable|date',
            'clocktime' => 'nullable|date_format:H:i:s',
            'status' => 'nullable|string',
            'clock_status' => 'nullable|string',
            'clock_in' => 'nullable|string',
            'clock_out' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $attendance = employeeattendances::create([
                'user_id' => $request->user_id,
                'device_id' => $request->device_ip,
                'clocktimestamp' => $request->timestamp,
                'clockdate' => $request->timestamp ?? now()->toDateString(),
                'clocktime' => $request->timestamp,
                'status' => $request->status,
                'clock_status' => $request->clock_status,
                'clock_in' => $request->clock_in,
                'clock_out' => $request->clock_out,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Attendance logged successfully',
                'data' => $attendance,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error logging attendance',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store multiple attendance logs at once
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeBulk(Request $request)
    {
        Log::info('Fingerprint attendance bulk log received', [
            'ip' => $request->ip(),
            'count' => is_array($request->records) ? count($request->records) : null,
            'payload' => $request->all(),
        ]);

        $validator = Validator::make($request->all(), [
            'device_ip' => 'required|string',
            'records' => 'required|array',
            'records.*.user_id' => 'required|integer',
            'records.*.timestamp' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $created = 0;
            $failed = 0;
            $deviceId = $request->device_ip;

            foreach ($request->records as $record) {
                try {
                    employeeattendances::create([
                        'fpuser_id' => $record['user_id'],
                        'device_id' => $deviceId,
                        'clocktimestamp' => $record['timestamp'],
                        'clockdate' => now()->toDateString(),
                        'clocktime' => null,
                        'status' => null,
                        'clock_status' => null,
                        'clock_in' => null,
                        'clock_out' => null,
                    ]);
                    $created++;
                } catch (\Exception $e) {
                    $failed++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Successfully logged {$created} attendance records",
                'data' => [
                    'created' => $created,
                    'failed' => $failed,
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error logging attendance',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
