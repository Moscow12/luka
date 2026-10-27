<?php

namespace App\Providers;

use App\Models\departments;
use App\Models\Employee;
use App\Models\User;
use App\Observers\DepartmentChatObserver;
use App\Observers\EmployeeChatObserver;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Department chat channels follow HR records
        Employee::observe(EmployeeChatObserver::class);
        departments::observe(DepartmentChatObserver::class);

        // Super admin bypasses all authorization checks
        Gate::before(function (User $user, string $ability) {
            if ($user->isSuperAdmin()) {
                return true;
            }

            return null; // Fall through to normal permission checks
        });
    }
}
