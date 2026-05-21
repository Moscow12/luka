<?php

use App\Models\FinancialYear;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'api.token' => \App\Http\Middleware\VerifyApiToken::class,
        ]);
    })
    ->withSchedule(function (Schedule $schedule): void {
        // Update current financial year daily at midnight
        $schedule->call(function () {
            FinancialYear::updateCurrentFinancialYear();
        })->daily()->name('update-current-financial-year');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
