<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\PermissionCategory;
use App\Models\Role;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'Roster Management' => [
                ['name' => 'view-roster', 'description' => 'View roster schedules and overview'],
                ['name' => 'create-roster', 'description' => 'Create and generate new rosters'],
                ['name' => 'edit-roster', 'description' => 'Edit existing roster schedules'],
                ['name' => 'manage-roster', 'description' => 'Manage and delete roster schedules'],
            ],
            'Leave Management' => [
                ['name' => 'view-leave', 'description' => 'View leave balances and history'],
                ['name' => 'request-leave', 'description' => 'Submit leave requests'],
                ['name' => 'approve-leave', 'description' => 'Approve or reject leave requests'],
                ['name' => 'manage-leave', 'description' => 'Manage leave types and policies'],
            ],
            'Loan Management' => [
                ['name' => 'view-loan', 'description' => 'View loan balances and history'],
                ['name' => 'request-loan', 'description' => 'Submit loan requests'],
                ['name' => 'approve-loan', 'description' => 'Approve or reject loan requests'],
                ['name' => 'manage-loan', 'description' => 'Manage loan types and policies'],
            ],
            'Asset Management' => [
                ['name' => 'manage-assets', 'description' => 'Manage department assets and asset reports'],
            ],
            'Staff Management' => [
                ['name' => 'view-staff', 'description' => 'View staff list and details'],
                ['name' => 'manage-staff', 'description' => 'Add, edit, and manage staff records'],
                ['name' => 'view-attendance', 'description' => 'View attendance records and fingerprint data'],
            ],
            'Payroll Management' => [
                ['name' => 'view-payroll', 'description' => 'View payroll records and reports'],
                ['name' => 'generate-payroll', 'description' => 'Generate payroll for staff'],
                ['name' => 'manage-payroll', 'description' => 'Manage allowances, deductions, and payments'],
            ],
            'Performance Management' => [
                ['name' => 'view-performance', 'description' => 'View performance plans and reports'],
                ['name' => 'manage-performance', 'description' => 'Manage KPIs, duties, and performance evaluations'],
            ],
            'Contract Management' => [
                ['name' => 'view-contracts', 'description' => 'View institutional contracts'],
                ['name' => 'manage-contracts', 'description' => 'Create, edit, and manage contracts'],
            ],
            'Approvals' => [
                ['name' => 'approve-requests', 'description' => 'General approval access'],
                ['name' => 'approve-payroll', 'description' => 'Approve or reject payroll requests'],
                ['name' => 'approve-roster', 'description' => 'Approve or reject roster schedules'],
                ['name' => 'approve-allowances', 'description' => 'Approve or reject allowance requests'],
                ['name' => 'approve-chop', 'description' => 'Approve or reject CHOP activities'],
                ['name' => 'approve-contract-requests', 'description' => 'Approve or reject staff contract requests'],
            ],
            'CHOP Management' => [
                ['name' => 'view-chop', 'description' => 'View CHOP budget requests and activity reports'],
                ['name' => 'manage-chop', 'description' => 'Manage CHOP activities, reviews, and settings'],
            ],
            'System Settings' => [
                ['name' => 'manage-settings', 'description' => 'Manage system settings, locations, and configurations'],
                ['name' => 'view-audit-logs', 'description' => 'View audit logs'],
                ['name' => 'manage-audit-logs', 'description' => 'View and manage audit logs'],
                ['name' => 'manage-activity-logs', 'description' => 'View and manage activity logs'],
                ['name' => 'manage-error-logs', 'description' => 'View and manage error logs'],
                ['name' => 'manage-maintenance-logs', 'description' => 'View and manage maintenance logs'],
                ['name' => 'manage-notifications', 'description' => 'View and manage notifications'],
                ['name' => 'manage-health-checks', 'description' => 'View and manage health checks'],
                ['name' => 'manage-backups', 'description' => 'View and manage backups'],
                ['name' => 'manage-maintenance-tasks', 'description' => 'View and manage maintenance tasks'],
                ['name' => 'manage-system-info', 'description' => 'View and manage system information'],
                ['name' => 'Use-api', 'description' => 'Use the API to manage the system and load data'],

            ],
            'User Management' => [
                ['name' => 'manage-users', 'description' => 'Create, edit, and manage user accounts'],
                ['name' => 'manage-roles', 'description' => 'Create and manage user roles'],
                ['name' => 'manage-permissions', 'description' => 'Create and manage permissions'],
            ],
        ];

        foreach ($permissions as $categoryName => $categoryPermissions) {
            // Create or find the category
            $category = PermissionCategory::firstOrCreate(
                ['name' => $categoryName]
            );

            // Create permissions for this category
            foreach ($categoryPermissions as $permissionData) {
                Permission::firstOrCreate(
                    [
                        'name' => $permissionData['name'],
                        'guard_name' => 'web',
                    ],
                    [
                        'description' => $permissionData['description'],
                        'category_id' => $category->id,
                    ]
                );
            }
        }

        $this->command->info('Permissions seeded successfully!');
        $this->command->table(
            ['Category', 'Permissions'],
            collect($permissions)->map(fn ($perms, $cat) => [$cat, count($perms)])->toArray()
        );

        // Assign all permissions to admin role
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $allPermissions = Permission::all();
            $adminRole->syncPermissions($allPermissions);
            $this->command->info("Assigned {$allPermissions->count()} permissions to 'admin' role.");
        } else {
            $this->command->warn("Admin role not found. Create the 'admin' role first.");
        }
    }
}
