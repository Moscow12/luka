<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\PermissionCategory;
use App\Models\Role;
use Illuminate\Database\Seeder;

class AclSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create or fetch permission categories
        $usersCategory = PermissionCategory::firstOrCreate(['name' => 'Users Management']);
        $accessCategory = PermissionCategory::firstOrCreate(['name' => 'Access Control Management']);
        $patientCategory = PermissionCategory::firstOrCreate(['name' => 'Patient Management']);

        // Define permissions for each category
        $usersPermissions = [
            ['name' => 'view users',            'description' => 'Can view list of users'],
            ['name' => 'create users',          'description' => 'Can create new users'],
            ['name' => 'update users',          'description' => 'Can update existing users'],
            ['name' => 'delete users',          'description' => 'Can delete users'],
            ['name' => 'export users',          'description' => 'Can export user data'],
            ['name' => 'import users',          'description' => 'Can import user data'],
            ['name' => 'deactivate users',      'description' => 'Can deactivate user accounts'],
            ['name' => 'reactivate users',      'description' => 'Can reactivate user accounts'],
            ['name' => 'reset user password',   'description' => 'Can reset user passwords'],
            ['name' => 'assign user roles',     'description' => 'Can assign roles to users'],
        ];

        $accessPermissions = [
            ['name' => 'view roles',            'description' => 'Can view list of roles'],
            ['name' => 'create roles',          'description' => 'Can create new roles'],
            ['name' => 'update roles',          'description' => 'Can update existing roles'],
            ['name' => 'delete roles',          'description' => 'Can delete roles'],
            ['name' => 'view permissions',      'description' => 'Can view list of permissions'],
            ['name' => 'create permissions',    'description' => 'Can create new permissions'],
            ['name' => 'update permissions',    'description' => 'Can update existing permissions'],
            ['name' => 'delete permissions',    'description' => 'Can delete permissions'],
            ['name' => 'assign permissions',    'description' => 'Can assign permissions to roles'],
            ['name' => 'audit access logs',     'description' => 'Can view access control audit logs'],
        ];

        $patientPermissions = [
            ['name' => 'view patients',         'description' => 'Can view patient records'],
            ['name' => 'register patients',     'description' => 'Can register new patients'],
            ['name' => 'update patients',       'description' => 'Can update existing patient records'],
            ['name' => 'delete patients',       'description' => 'Can delete patient records'],
            ['name' => 'discharge patients',    'description' => 'Can discharge patients'],
            ['name' => 'admit patients',        'description' => 'Can admit patients'],
            ['name' => 'transfer patients',     'description' => 'Can transfer patients between wards'],
            ['name' => 'export patient reports', 'description' => 'Can export patient reports'],
            ['name' => 'upload patient files',  'description' => 'Can upload files to patient records'],
            ['name' => 'view medical history',  'description' => 'Can view full medical history'],
        ];

        // Group permissions with their categories
        $permissionGroups = [
            ['permissions' => $usersPermissions,   'category' => $usersCategory],
            ['permissions' => $accessPermissions,  'category' => $accessCategory],
            ['permissions' => $patientPermissions, 'category' => $patientCategory],
        ];

        // Create permissions under each category
        foreach ($permissionGroups as $group) {
            foreach ($group['permissions'] as $permData) {
                Permission::firstOrCreate(
                    [
                        'name' => $permData['name'],
                        'guard_name' => 'web',
                    ],
                    [
                        'description' => $permData['description'],
                        'category_id' => $group['category']->id,
                    ]
                );
            }
        }

        // Create or fetch roles
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $userRole = Role::firstOrCreate(['name' => 'user',  'guard_name' => 'web']);

        // Assign all permissions to Admin
        $adminRole->syncPermissions(Permission::all());

        // Assign a subset of permissions to User
        $userSubset = Permission::whereIn('name', [
            'view users',
            'view roles',
            'view permissions',
            'view patients',
            'register patients',
            'update patients',
        ])->get();
        $userRole->syncPermissions($userSubset);
    }
}
