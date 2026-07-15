<?php
// Quick permission check script
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Role;

echo "🔐 PERMISSION CHECK\n";
echo "===================\n\n";

$testPermissions = [
    'projects.view', 'projects.create', 'projects.edit', 'projects.delete',
    'boq.view', 'boq.create', 'boq.edit', 'boq.delete',
    'ipc.view', 'ipc.create', 'ipc.approve',
    'subcontractors.view', 'subcontractors.create',
    'cost-categories.view', 'cost-categories.create',
    'reports.view', 'reports.export',
    'users.view', 'users.create', 'users.edit', 'users.delete',
    'roles.view', 'roles.create', 'roles.edit', 'roles.delete',
];

$roles = Role::all();

foreach ($roles as $role) {
    echo "Role: {$role->display_name} ({$role->name})\n";
    echo str_repeat('-', 40) . "\n";
    
    $user = new User(['role_id' => $role->id]);
    $user->setRelation('roleRelation', $role);
    
    foreach ($testPermissions as $perm) {
        $has = $role->hasPermission($perm);
        echo "  " . ($has ? '✅' : '❌') . " $perm\n";
    }
    echo "\n";
}
