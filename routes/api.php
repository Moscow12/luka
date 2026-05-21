<?php

use App\Http\Controllers\Api\AssetController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\LeaveController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Third-party sync API endpoints — guarded by a shared-secret token.
Route::middleware('api.token')->group(function () {
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
    });

    // Leave API endpoints (third-party sync)
    Route::prefix('leaves')->group(function () {
        Route::post('/register', [LeaveController::class, 'store']);
        Route::post('/sync', [LeaveController::class, 'store']);
        Route::get('/', [LeaveController::class, 'index']);
        Route::get('/{id}', [LeaveController::class, 'show']);
    });

    // Asset API endpoints (third-party sync)
    Route::prefix('assets')->group(function () {
        Route::post('/register', [AssetController::class, 'store']);
        Route::post('/sync', [AssetController::class, 'store']);
        Route::get('/', [AssetController::class, 'index']);
        Route::get('/{id}', [AssetController::class, 'show']);
    });
});
