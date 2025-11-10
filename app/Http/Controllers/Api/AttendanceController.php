<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\employeeattendances;
use Illuminate\Http\Request;
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
        $validator = Validator::make($request->all(), [
            'fpuser_id' => 'required|integer',
            'device_id' => 'required|string',
            'clocktimestamp' => 'required|string',
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
                'fpuser_id' => $request->fpuser_id,
                'device_id' => $request->device_id,
                'clocktimestamp' => $request->clocktimestamp,
                'clockdate' => $request->clockdate ?? now()->toDateString(),
                'clocktime' => $request->clocktime,
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
        $validator = Validator::make($request->all(), [
            'attendances' => 'required|array',
            'attendances.*.fpuser_id' => 'required|integer',
            'attendances.*.device_id' => 'required|string',
            'attendances.*.clocktimestamp' => 'required|string',
            'attendances.*.clockdate' => 'nullable|date',
            'attendances.*.clocktime' => 'nullable|date_format:H:i:s',
            'attendances.*.status' => 'nullable|string',
            'attendances.*.clock_status' => 'nullable|string',
            'attendances.*.clock_in' => 'nullable|string',
            'attendances.*.clock_out' => 'nullable|string',
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

            foreach ($request->attendances as $attendanceData) {
                try {
                    employeeattendances::create([
                        'fpuser_id' => $attendanceData['fpuser_id'],
                        'device_id' => $attendanceData['device_id'],
                        'clocktimestamp' => $attendanceData['clocktimestamp'],
                        'clockdate' => $attendanceData['clockdate'] ?? now()->toDateString(),
                        'clocktime' => $attendanceData['clocktime'] ?? null,
                        'status' => $attendanceData['status'] ?? null,
                        'clock_status' => $attendanceData['clock_status'] ?? null,
                        'clock_in' => $attendanceData['clock_in'] ?? null,
                        'clock_out' => $attendanceData['clock_out'] ?? null,
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
