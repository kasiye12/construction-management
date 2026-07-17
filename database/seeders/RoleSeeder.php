<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RoleSeeder extends Seeder
{
    public function run()
    {
        // Create roles with proper permissions
        $roles = [
            [
                'name' => 'admin',
                'display_name' => 'Administrator',
                'description' => 'Full system access - can do everything',
                'permissions' => ['*'],
            ],
            [
                'name' => 'manager',
                'display_name' => 'Project Manager',
                'description' => 'Can manage projects, BOQ, IPCs, subcontractors, and view reports',
                'permissions' => [
                    'projects.view', 'projects.create', 'projects.edit', 'projects.delete',
                    'boq.view', 'boq.create', 'boq.edit', 'boq.delete',
                    'ipc.view', 'ipc.create', 'ipc.edit', 'ipc.approve', 'ipc.delete',
                    'subcontractors.view', 'subcontractors.create', 'subcontractors.edit', 'subcontractors.delete',
                    'cost-categories.view', 'cost-categories.create', 'cost-categories.edit', 'cost-categories.delete',
                    'reports.view', 'reports.export',
                    'actual-costs.view', 'actual-costs.create', 'actual-costs.edit', 'actual-costs.delete',
                    'users.view',
                ],
            ],
            [
                'name' => 'engineer',
                'display_name' => 'Engineer / QS',
                'description' => 'Can create/edit BOQ, IPCs, and view projects',
                'permissions' => [
                    'projects.view',
                    'boq.view', 'boq.create', 'boq.edit',
                    'ipc.view', 'ipc.create', 'ipc.edit',
                    'subcontractors.view',
                    'cost-categories.view',
                    'reports.view',
                    'actual-costs.view', 'actual-costs.create',
                ],
            ],
            [
                'name' => 'finance',
                'display_name' => 'Finance Officer',
                'description' => 'Can view projects, approve IPCs, and export reports',
                'permissions' => [
                    'projects.view',
                    'boq.view',
                    'ipc.view', 'ipc.approve',
                    'reports.view', 'reports.export',
                    'actual-costs.view',
                ],
            ],
            [
                'name' => 'viewer',
                'display_name' => 'Viewer',
                'description' => 'Read-only access to view projects, BOQ, and reports',
                'permissions' => [
                    'projects.view',
                    'boq.view',
                    'ipc.view',
                    'reports.view',
                ],
            ],
        ];

        foreach ($roles as $roleData) {
            Role::updateOrCreate(['name' => $roleData['name']], $roleData);
        }

        // Create users
        $users = [
            ['name' => 'System Administrator', 'email' => 'admin@cms.com', 'role' => 'admin', 'department' => 'IT', 'position' => 'System Admin'],
            ['name' => 'Abebe Kebede', 'email' => 'manager@cms.com', 'role' => 'manager', 'department' => 'Projects', 'position' => 'Senior PM'],
            ['name' => 'Tigist Haile', 'email' => 'engineer@cms.com', 'role' => 'engineer', 'department' => 'Engineering', 'position' => 'QS'],
            ['name' => 'Meron Alemu', 'email' => 'finance@cms.com', 'role' => 'finance', 'department' => 'Finance', 'position' => 'Finance Officer'],
            ['name' => 'Bereket Tadesse', 'email' => 'viewer@cms.com', 'role' => 'viewer', 'department' => 'Management', 'position' => 'Stakeholder'],
        ];

        foreach ($users as $userData) {
            $roleName = $userData['role'];
            unset($userData['role']);
            $userData['role_id'] = Role::where('name', $roleName)->first()->id;
            $userData['password'] = Hash::make('password');
            $userData['is_active'] = true;
            
            User::updateOrCreate(['email' => $userData['email']], $userData);
        }

        echo "✅ Roles and users created with proper permissions!\n";
    }
}
