<?php

namespace Tests\Support;

use App\Models\departments;
use App\Models\Employee;
use App\Models\EmployeeAssignedDuty;
use App\Models\Permission;
use App\Models\PermissionCategory;
use App\Models\TicketCategory;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * Several legacy migrations are MySQL-only, so ticket tests build just the
 * tables they need on in-memory SQLite.
 */
abstract class TicketTestCase extends TestCase
{
    protected const MIGRATIONS = [
        '0001_01_01_000000_create_users_table.php',
        '0002_05_02_090051_create_jobtitles_table.php',
        '2025_06_20_223844_create_permission_tables.php',
        '2025_10_01_071218_create_activity_log_table.php',
        '2025_10_01_071219_add_event_column_to_activity_log_table.php',
        '2025_10_01_071220_add_batch_uuid_column_to_activity_log_table.php',
        '2026_01_01_092539_fix_activity_log_uuid_columns.php',
        '2025_10_08_132654_create_departments_table.php',
        '2026_01_01_180607_make_supervisor_title_id_nullable_in_departments.php',
        '2025_11_16_180132_create_employee_assigned_duties_table.php',
        '2026_09_07_112235_add_store_order_id_to_employee_assigned_duties_table.php',
        '2026_09_08_101200_add_approval_score_to_employee_assigned_duties_table.php',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        Schema::disableForeignKeyConstraints();

        $files = array_merge(
            self::MIGRATIONS,
            array_map('basename', glob(database_path('migrations/2026_10_27_1000*.php')))
        );

        foreach ($files as $file) {
            $this->artisan('migrate', ['--path' => 'database/migrations/'.$file]);
        }

        // Slim employees table: the real one references a dozen lookup tables
        Schema::create('employees', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->nullable();
            $table->foreignUuid('department_id');
            $table->foreignUuid('title_id')->nullable();
            $table->string('employee_no')->nullable();
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('status')->default('Active');
            $table->softDeletes();
            $table->timestamps();
        });

        // Chat tables: employee/department observers keep department channels in sync
        foreach (glob(database_path('migrations/2026_10_28_1000*.php')) as $file) {
            $this->artisan('migrate', ['--path' => 'database/migrations/'.basename($file)]);
        }

        $category = PermissionCategory::create(['name' => 'Tickets']);
        foreach (['create-tickets', 'manage-department-tickets', 'view-all-tickets', 'manage-ticket-categories', 'view-ticket-reports'] as $name) {
            Permission::create(['name' => $name, 'guard_name' => 'web', 'category_id' => $category->id]);
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    protected function makeUser(array $attributes = []): User
    {
        return User::forceCreate(array_merge([
            'id' => (string) Str::uuid(),
            'first_name' => 'Test',
            'surname' => Str::random(6),
            'gender' => 'Male',
            'email' => Str::random(10).'@example.test',
            'phone_number' => (string) random_int(100000000, 999999999),
            'username' => Str::random(10),
            'password' => 'secret',
        ], $attributes));
    }

    protected function makeDepartment(string $name = 'ICT', ?string $supervisorTitleId = null): departments
    {
        return departments::forceCreate([
            'id' => (string) Str::uuid(),
            'name' => $name.' '.Str::random(4),
            'description' => $name,
            'supervisor_title_id' => $supervisorTitleId,
            'added_by' => $this->makeUser()->id,
        ]);
    }

    /** Employee with a linked user account. */
    protected function makeStaff(departments $department, array $permissions = [], array $employeeAttributes = []): User
    {
        $user = $this->makeUser();
        if ($permissions !== []) {
            $user->givePermissionTo($permissions);
        }

        Employee::forceCreate(array_merge([
            'id' => (string) Str::uuid(),
            'user_id' => $user->id,
            'department_id' => $department->id,
            'first_name' => $user->first_name,
            'last_name' => $user->surname,
        ], $employeeAttributes));

        return $user;
    }

    protected function employeeOf(User $user): Employee
    {
        return Employee::where('user_id', $user->id)->firstOrFail();
    }

    protected function makeCategory(): TicketCategory
    {
        return TicketCategory::create(['name' => 'IT Support '.Str::random(4), 'status' => 'active']);
    }

    protected function makeDuty(Employee $employee, array $attributes = []): EmployeeAssignedDuty
    {
        return EmployeeAssignedDuty::create(array_merge([
            'employee_id' => $employee->id,
            'duty_name' => 'Handle support tickets',
            'kpi_type' => 'quantitative',
            'measurement_type' => 'numeric',
            'target_value' => 2,
            'priority' => 'medium',
            'status' => 'assigned',
            'is_active' => true,
            'start_date' => now()->subDay()->toDateString(),
        ], $attributes));
    }
}
