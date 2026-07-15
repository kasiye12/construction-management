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
            ],
        ],
        'engineer' => [
            'name' => 'Engineer / QS',
            'permissions' => [
                'boq.create', 'boq.edit',
                'ipc.create',
                'reports.view',
            ],
        ],
        'finance' => [
            'name' => 'Finance Officer',
            'permissions' => [
                'ipc.view', 'ipc.approve',
                'reports.view', 'reports.export',
            ],
        ],
        'viewer' => [
            'name' => 'Viewer',
            'permissions' => [
                'projects.view',
                'boq.view',
                'ipc.view',
                'reports.view',
            ],
        ],
    ],
];
