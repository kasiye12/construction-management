<?php
return [
    'roles' => [
        'admin' => [
            'name' => 'Administrator',
            'permissions' => ['*'], // All permissions
        ],
        'manager' => [
            'name' => 'Project Manager',
            'permissions' => [
                'projects.create', 'projects.edit', 'projects.delete',
                'boq.create', 'boq.edit', 'boq.delete',
                'ipc.create', 'ipc.approve',
                'subcontractors.create', 'subcontractors.edit',
                'cost-categories.create', 'cost-categories.edit',
                'reports.view', 'reports.export',
                'contractual-reports.view', 'contractual-reports.create', 'contractual-reports.edit', 'contractual-reports.delete', 'contractual-reports.export',
                'equipment-maintenance.view', 'equipment-maintenance.create', 'equipment-maintenance.edit', 'equipment-maintenance.delete', 'equipment-maintenance.export',
            ],
        ],
        'engineer' => [
            'name' => 'Engineer / QS',
            'permissions' => [
                'boq.create', 'boq.edit',
                'ipc.create',
                'reports.view',
                'contractual-reports.view', 'contractual-reports.create', 'contractual-reports.edit', 'contractual-reports.export',
                'equipment-maintenance.view', 'equipment-maintenance.create', 'equipment-maintenance.edit', 'equipment-maintenance.export',
            ],
        ],
        'finance' => [
            'name' => 'Finance Officer',
            'permissions' => [
                'ipc.view', 'ipc.approve',
                'reports.view', 'reports.export',
                'contractual-reports.view', 'contractual-reports.export',
                'equipment-maintenance.view', 'equipment-maintenance.export',
            ],
        ],
        'viewer' => [
            'name' => 'Viewer',
            'permissions' => [
                'projects.view',
                'boq.view',
                'ipc.view',
                'reports.view',
                'contractual-reports.view',
                'equipment-maintenance.view',
            ],
        ],
    ],
];
