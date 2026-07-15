<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        // Admin - Full access
        User::create([
            'name' => 'System Administrator',
            'email' => 'admin@cms.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'department' => 'IT Department',
            'phone' => '+251900000001',
            'is_active' => true,
        ]);

        // Project Manager
        User::create([
            'name' => 'Abebe Kebede',
            'email' => 'manager@cms.com',
            'password' => Hash::make('password'),
            'role' => 'manager',
            'department' => 'Project Management',
            'phone' => '+251900000002',
            'is_active' => true,
        ]);

        // Engineer / Quantity Surveyor
        User::create([
            'name' => 'Tigist Haile',
            'email' => 'engineer@cms.com',
            'password' => Hash::make('password'),
            'role' => 'engineer',
            'department' => 'Engineering Department',
            'phone' => '+251900000003',
            'is_active' => true,
        ]);

        // Finance
        User::create([
            'name' => 'Meron Alemu',
            'email' => 'finance@cms.com',
            'password' => Hash::make('password'),
            'role' => 'finance',
            'department' => 'Finance Department',
            'phone' => '+251900000004',
            'is_active' => true,
        ]);

        // Viewer (Read-only)
        User::create([
            'name' => 'Bereket Tadesse',
            'email' => 'viewer@cms.com',
            'password' => Hash::make('password'),
            'role' => 'viewer',
            'department' => 'Management',
            'phone' => '+251900000005',
            'is_active' => true,
        ]);

        echo "✅ 5 users created with different roles!\n";
        echo "   admin@cms.com (Admin - Full Access)\n";
        echo "   manager@cms.com (Manager - Create/Edit/Delete)\n";
        echo "   engineer@cms.com (Engineer - Create/Edit BOQ & IPC)\n";
        echo "   finance@cms.com (Finance - View Only)\n";
        echo "   viewer@cms.com (Viewer - Read Only)\n";
        echo "   All passwords: 'password'\n";
    }
}
