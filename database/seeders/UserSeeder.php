<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@cms.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'department' => 'Management',
            'phone' => '+251900000000',
            'is_active' => true
        ]);

        User::create([
            'name' => 'Project Manager',
            'email' => 'pm@cms.com',
            'password' => Hash::make('password'),
            'role' => 'manager',
            'department' => 'Projects',
            'phone' => '+251911111111',
            'is_active' => true
        ]);

        User::create([
            'name' => 'Quantity Surveyor',
            'email' => 'qs@cms.com',
            'password' => Hash::make('password'),
            'role' => 'engineer',
            'department' => 'Engineering',
            'phone' => '+251922222222',
            'is_active' => true
        ]);

        echo "✅ Users created: admin@cms.com, pm@cms.com, qs@cms.com (password: password)\n";
    }
}
