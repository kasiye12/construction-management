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
        // Create roles
        $roles = [
            [
                'name' => 'admin',
                'display_name' => 'Administrator',
                'description' => 'Full system access',
                'permissions' => ['*'],
            ],
            [
                'name' => 'manager',
                'display_name' => 'Project Manager',
                'description' => 'Can manage projects, BOQ, IPCs, and subcontractors',
                'permissions' => [
                    'projects.view', 'projects.create', 'projects.edit', 'projects.delete',
                    'boq.view', 'boq.create', 'boq.edit', 'boq.delete',
                    'ipc.view', 'ipc.create', 'ipc.approve',
                    'subcontractors.view', 'subcontractors.create', 'subcontractors.edit',
                    'cost-categories.view', 'cost-categories.create', 'cost-categories.edit',
                    'reports.view', 'reports.export',
                    'users.view',
                ],
            ],
            [
                'name' => 'engineer',
                'display_name' => 'Engineer / QS',
                'description' => 'Can create BOQ items and IPCs',
                'permissions' => [
                    'projects.view',
                    'boq.view', 'boq.create', 'boq.edit',
                    'ipc.view', 'ipc.create',
                    'subcontractors.view',
                    'cost-categories.view',
                    'reports.view',
                ],
            ],
            [
                'name' => 'finance',
                'display_name' => 'Finance Officer',
                'description' => 'Can view and approve IPCs, access reports',
                'permissions' => [
                    'projects.view',
                    'boq.view',
                    'ipc.view', 'ipc.approve',
                    'reports.view', 'reports.export',
                ],
            ],
            [
                'name' => 'viewer',
                'display_name' => 'Viewer',
                'description' => 'Read-only access',
                'permissions' => [
                    'projects.view',
                    'boq.view',
                    'ipc.view',
                    'reports.view',
                ],
            ],
        ];

        foreach ($roles as $roleData) {
            Role::create($roleData);
        }

        // Create users
        $users = [
            [
                'name' => 'System Administrator',
                'email' => 'admin@cms.com',
                'password' => Hash::make('password'),
                'role_id' => Role::where('name', 'admin')->first()->id,
                'department' => 'IT Department',
                'position' => 'System Administrator',
                'is_active' => true,
            ],
            [
                'name' => 'Abebe Kebede',
                'email' => 'manager@cms.com',
                'password' => Hash::make('password'),
                'role_id' => Role::where('name', 'manager')->first()->id,
                'department' => 'Project Management',
                'position' => 'Senior Project Manager',
                'is_active' => true,
            ],
            [
                'name' => 'Tigist Haile',
                'email' => 'engineer@cms.com',
                'password' => Hash::make('password'),
                'role_id' => Role::where('name', 'engineer')->first()->id,
                'department' => 'Engineering',
                'position' => 'Quantity Surveyor',
                'is_active' => true,
            ],
            [
                'name' => 'Meron Alemu',
                'email' => 'finance@cms.com',
                'password' => Hash::make('password'),
                'role_id' => Role::where('name', 'finance')->first()->id,
                'department' => 'Finance',
                'position' => 'Finance Officer',
                'is_active' => true,
            ],
            [
                'name' => 'Bereket Tadesse',
                'email' => 'viewer@cms.com',
                'password' => Hash::make('password'),
                'role_id' => Role::where('name', 'viewer')->first()->id,
                'department' => 'Management',
                'position' => 'Stakeholder',
                'is_active' => true,
            ],
        ];

        foreach ($users as $userData) {
            User::create($userData);
        }

        echo "✅ Roles and users created successfully!\n";
    }
}
