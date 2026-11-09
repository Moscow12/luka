<?php

namespace Tests\Support;

use App\Models\DepartmentPlan;
use App\Models\DepartmentPlanItem;
use App\Models\departments;
use App\Models\Employee;
use App\Models\EmployeePlan;
use App\Models\EmployeePlanImplementation;
use App\Models\EmployeePlanItem;
use App\Models\OrganizationalPlan;
use App\Models\OrganizationalPlanItem;
use App\Models\Permission;
use App\Models\PermissionCategory;
use Spatie\Permission\PermissionRegistrar;

/**
 * Adds the performance-plan tables and permissions on top of the slim ticket schema,
 * plus small builders for each level of the plan cascade.
 */
abstract class PerformanceTestCase extends TicketTestCase
{
    private const PERFORMANCE_MIGRATIONS = [
        '2025_11_16_180044_create_organizational_plans_table.php',
        '2025_11_16_180051_create_organizational_plan_items_table.php',
        '2025_11_16_180059_create_department_plans_table.php',
        '2025_11_16_180108_create_department_plan_items_table.php',
        '2025_11_16_180116_create_employee_plans_table.php',
        '2025_11_16_180125_create_employee_plan_items_table.php',
        '2026_01_15_055028_create_employee_plan_implementations_table.php',
        '2026_01_15_060636_create_performance_evaluations_table.php',
        '2026_11_05_120000_make_organizational_plan_item_optional_on_department_plan_items_table.php',
        '2026_11_05_130000_add_evidence_file_to_employee_plan_implementations_table.php',
        '2026_11_05_140000_add_task_scores_to_performance_tables.php',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        foreach (self::PERFORMANCE_MIGRATIONS as $file) {
            $this->artisan('migrate', ['--path' => 'database/migrations/'.$file]);
        }

        $category = PermissionCategory::create(['name' => 'Performance Management']);
        foreach (['view-performance', 'manage-org-plans', 'manage-dept-plans', 'view-all-dept-plans', 'manage-employee-plans'] as $name) {
            Permission::create(['name' => $name, 'guard_name' => 'web', 'category_id' => $category->id]);
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    protected function makeOrgPlan(array $attributes = []): OrganizationalPlan
    {
        return OrganizationalPlan::create(array_merge([
            'plan_name' => 'Strategic Plan 2026',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'period_type' => 'yearly',
            'status' => 'active',
        ], $attributes));
    }

    protected function makeOrgGoal(OrganizationalPlan $plan, array $attributes = []): OrganizationalPlanItem
    {
        return OrganizationalPlanItem::create(array_merge([
            'organizational_plan_id' => $plan->id,
            'item_name' => 'Clients served',
            'description' => 'Clients receiving a completed service',
            'kpi_type' => 'quantitative',
            'measurement_type' => 'numeric',
            'weight' => 100,
            'target_value' => 100,
            'target_unit' => 'clients',
            'is_active' => true,
        ], $attributes));
    }

    protected function makeDeptPlan(OrganizationalPlan $plan, departments $department): DepartmentPlan
    {
        return DepartmentPlan::create([
            'organizational_plan_id' => $plan->id,
            'department_id' => $department->id,
            'plan_name' => $department->name.' plan',
            'status' => 'active',
        ]);
    }

    protected function makeDeptGoal(DepartmentPlan $plan, ?OrganizationalPlanItem $parent, array $attributes = []): DepartmentPlanItem
    {
        return DepartmentPlanItem::create(array_merge([
            'department_plan_id' => $plan->id,
            'organizational_plan_item_id' => $parent?->id,
            'item_name' => $parent?->item_name ?? 'Department goal',
            'description' => 'Department share',
            'weight' => 100,
            'target_value' => 50,
            'target_unit' => $parent?->target_unit ?? 'clients',
            'is_active' => true,
        ], $attributes));
    }

    /** An employee plan with one goal under the given department goal. */
    protected function makeEmployeeGoal(DepartmentPlanItem $parent, Employee $employee, float $target, array $attributes = []): EmployeePlanItem
    {
        $plan = EmployeePlan::firstOrCreate(
            ['department_plan_id' => $parent->department_plan_id, 'employee_id' => $employee->id],
            ['plan_name' => 'Plan for '.$employee->first_name, 'status' => 'active'],
        );

        return EmployeePlanItem::create(array_merge([
            'employee_plan_id' => $plan->id,
            'department_plan_item_id' => $parent->id,
            'item_name' => $parent->item_name,
            'description' => 'My share',
            'weight' => 100,
            'target_value' => $target,
            'target_unit' => $parent->target_unit,
            'is_active' => true,
        ], $attributes));
    }

    protected function logWork(EmployeePlanItem $goal, float $quantity, string $status = 'verified', ?int $score = null): EmployeePlanImplementation
    {
        $employee = $goal->employeePlan->employee;

        return EmployeePlanImplementation::create([
            'employee_plan_item_id' => $goal->id,
            'employee_id' => $employee->id,
            'implementation_date' => now()->toDateString(),
            'activity_title' => 'Served clients',
            'quantity_achieved' => $quantity,
            'status' => $status,
            'verification_score' => $score,
            'added_by' => $employee->user_id,
        ]);
    }
}
