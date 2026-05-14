<?php

use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\EmployeeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Attendance API endpoints
Route::prefix('attendance')->group(function () {
    Route::post('/log', [AttendanceController::class, 'store']);
    Route::post('/log-bulk', [AttendanceController::class, 'storeBulk']);
});

// Employee API endpoints
Route::prefix('employees')->group(function () {
    Route::post('/register', [EmployeeController::class, 'store']);
    Route::get('/', [EmployeeController::class, 'index']);
    Route::get('/{id}', [EmployeeController::class, 'show']);

    // Debug test endpoint
    Route::post('/test-register', function(Request $request) {
        return response()->json([
            'debug_info' => 'This is a test endpoint to see raw request data',
            'received_data' => $request->all(),
            'employee_id' => $request->employee_id ?? 'Not provided',
            'employee_name' => $request->employee_name ?? 'Not provided',
            'all_fields_count' => count($request->all())
        ], 200);
    });
});
