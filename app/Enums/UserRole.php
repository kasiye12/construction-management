<?php
namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';
    case MANAGER = 'manager';
    case ENGINEER = 'engineer';
    case FINANCE = 'finance';
    case VIEWER = 'viewer';

    public function permissions(): array
    {
        return match($this) {
            self::ADMIN => ['*'],
            self::MANAGER => ['projects.*', 'boq.*', 'ipc.*', 'subcontractors.*'],
            self::ENGINEER => ['boq.create', 'boq.edit', 'ipc.create', 'reports.view'],
            self::FINANCE => ['ipc.view', 'ipc.approve', 'reports.*'],
            self::VIEWER => ['*.view', 'reports.view'],
        };
    }

    public function label(): string
    {
        return match($this) {
            self::ADMIN => 'Administrator',
            self::MANAGER => 'Project Manager',
            self::ENGINEER => 'Engineer / QS',
            self::FINANCE => 'Finance Officer',
            self::VIEWER => 'Viewer',
        };
    }
}
