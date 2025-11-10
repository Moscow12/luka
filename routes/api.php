<?php

use App\Http\Controllers\Api\AttendanceController;
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
