<?php
namespace App\Services;

use App\Models\Ipc;
use App\Models\QuantityTakeoff;
use App\Models\Notification;
use App\Models\User;
use App\Models\WorkflowPermission;

class WorkflowService
{
    // IPC Statuses
    const STATUS_DRAFT = 'draft';
    const STATUS_PREPARED = 'prepared';
    const STATUS_CHECKED = 'checked';
    const STATUS_SUBMITTED = 'submitted';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_PAID = 'paid';

    // Take-Off Statuses
    const TO_STATUS_DRAFT = 'draft';
    const TO_STATUS_VERIFIED = 'verified';
    const TO_STATUS_APPROVED = 'approved';

    // IPC Workflow
    const WORKFLOW = [
        self::STATUS_DRAFT => [
            'label' => 'Draft', 'icon' => 'file', 'color' => 'secondary',
            'next' => ['prepared'],
        ],
        self::STATUS_PREPARED => [
            'label' => 'Prepared', 'icon' => 'pencil-alt', 'color' => 'info',
            'next' => ['checked', 'submitted'],
        ],
        self::STATUS_CHECKED => [
            'label' => 'Checked', 'icon' => 'check', 'color' => 'warning',
            'next' => ['submitted'],
        ],
        self::STATUS_SUBMITTED => [
            'label' => 'Submitted', 'icon' => 'paper-plane', 'color' => 'primary',
            'next' => ['approved', 'rejected'],
        ],
        self::STATUS_APPROVED => [
            'label' => 'Approved', 'icon' => 'check-circle', 'color' => 'success',
            'next' => ['paid'],
        ],
        self::STATUS_REJECTED => [
            'label' => 'Rejected', 'icon' => 'times-circle', 'color' => 'danger',
            'next' => ['draft'],
        ],
        self::STATUS_PAID => [
            'label' => 'Paid', 'icon' => 'money-bill', 'color' => 'dark',
            'next' => [],
        ],
    ];

    // Take-Off Workflow
    const TAKEOFF_WORKFLOW = [
        self::TO_STATUS_DRAFT => [
            'label' => 'Draft', 'icon' => 'file', 'color' => 'secondary',
            'next' => ['verified'],
            'roles' => ['admin', 'manager', 'engineer'],
        ],
        self::TO_STATUS_VERIFIED => [
            'label' => 'Verified', 'icon' => 'check', 'color' => 'info',
            'next' => ['approved'],
            'roles' => ['admin', 'manager'],
        ],
        self::TO_STATUS_APPROVED => [
            'label' => 'Approved', 'icon' => 'check-circle', 'color' => 'success',
            'next' => [],
            'roles' => ['admin', 'manager'],
        ],
    ];

    // Contractual Reports Workflow (OF/TNT/CAD/01)
    const CR_STATUS_DRAFT = 'draft';
    const CR_STATUS_SUBMITTED = 'submitted';
    const CR_STATUS_CHECKED = 'checked';
    const CR_STATUS_APPROVED = 'approved';
    const CR_STATUS_REJECTED = 'rejected';

    const CONTRACTUAL_WORKFLOW = [
        self::CR_STATUS_DRAFT => [
            'label' => 'Draft', 'icon' => 'file', 'color' => 'secondary',
            'next' => ['submitted'],
        ],
        self::CR_STATUS_SUBMITTED => [
            'label' => 'Submitted', 'icon' => 'paper-plane', 'color' => 'primary',
            'next' => ['checked', 'rejected'],
        ],
        self::CR_STATUS_CHECKED => [
            'label' => 'Checked', 'icon' => 'user-check', 'color' => 'warning',
            'next' => ['approved', 'rejected'],
        ],
        self::CR_STATUS_APPROVED => [
            'label' => 'Approved', 'icon' => 'check-circle', 'color' => 'success',
            'next' => [],
        ],
        self::CR_STATUS_REJECTED => [
            'label' => 'Rejected', 'icon' => 'times-circle', 'color' => 'danger',
            'next' => ['draft'],
        ],
    ];

    const ROUTE_MAP = [
        'prepared' => 'ipcs.prepare', 'checked' => 'ipcs.check',
        'submitted' => 'ipcs.submit', 'approved' => 'ipcs.approve',
        'rejected' => 'ipcs.reject', 'paid' => 'ipcs.mark-paid',
        'verified' => 'quantity-takeoffs.verify',
    ];

    public static function getStatusLabel($status): string
    {
        return self::WORKFLOW[$status]['label'] ?? self::TAKEOFF_WORKFLOW[$status]['label'] ?? ucfirst($status);
    }

    public static function getStatusColor($status): string
    {
        return self::WORKFLOW[$status]['color'] ?? self::TAKEOFF_WORKFLOW[$status]['color'] ?? 'secondary';
    }

    public static function getStatusIcon($status): string
    {
        return self::WORKFLOW[$status]['icon'] ?? self::TAKEOFF_WORKFLOW[$status]['icon'] ?? 'circle';
    }

    /**
     * Check if user can perform take-off action
     */
    public static function canUserVerifyTakeoff(User $user): bool
    {
        if ($user->isAdmin()) return true;
        return WorkflowPermission::canUserAct($user->id, 'verify_takeoff');
    }

    public static function canUserApproveTakeoff(User $user): bool
    {
        if ($user->isAdmin()) return true;
        return WorkflowPermission::canUserAct($user->id, 'approve_takeoff');
    }

    /**
     * Check if user can perform contractual report actions
     */
    public static function canUserPrepareContractualReport(User $user): bool
    {
        if ($user->isAdmin()) return true;
        return WorkflowPermission::canUserAct($user->id, 'prepare_contractual_report');
    }

    public static function canUserCheckContractualReport(User $user): bool
    {
        if ($user->isAdmin()) return true;
        return WorkflowPermission::canUserAct($user->id, 'check_contractual_report');
    }

    public static function canUserApproveContractualReport(User $user): bool
    {
        if ($user->isAdmin()) return true;
        return WorkflowPermission::canUserAct($user->id, 'approve_contractual_report');
    }

    public static function canUserRejectContractualReport(User $user): bool
    {
        if ($user->isAdmin()) return true;
        return WorkflowPermission::canUserAct($user->id, 'reject_contractual_report');
    }

    /**
     * Check if user can perform equipment PM schedule actions (OF/EAMD/074)
     */
    public static function canUserPrepareEquipmentSchedule(User $user): bool
    {
        if ($user->isAdmin()) return true;
        return WorkflowPermission::canUserAct($user->id, 'prepare_equipment_schedule');
    }

    public static function canUserCheckEquipmentSchedule(User $user): bool
    {
        if ($user->isAdmin()) return true;
        return WorkflowPermission::canUserAct($user->id, 'check_equipment_schedule');
    }

    public static function canUserApproveEquipmentSchedule(User $user): bool
    {
        if ($user->isAdmin()) return true;
        return WorkflowPermission::canUserAct($user->id, 'approve_equipment_schedule');
    }

    public static function canUserRejectEquipmentSchedule(User $user): bool
    {
        if ($user->isAdmin()) return true;
        return WorkflowPermission::canUserAct($user->id, 'reject_equipment_schedule');
    }

    /**
     * Check if user can perform breakdown report actions (OF/TNT/ EAMD/030)
     */
    public static function canUserPrepareMaintenanceReport(User $user): bool
    {
        if ($user->isAdmin()) return true;
        return WorkflowPermission::canUserAct($user->id, 'prepare_maintenance_report');
    }

    public static function canUserCheckMaintenanceReport(User $user): bool
    {
        if ($user->isAdmin()) return true;
        return WorkflowPermission::canUserAct($user->id, 'check_maintenance_report');
    }

    public static function canUserApproveMaintenanceReport(User $user): bool
    {
        if ($user->isAdmin()) return true;
        return WorkflowPermission::canUserAct($user->id, 'approve_maintenance_report');
    }

    public static function canUserRejectMaintenanceReport(User $user): bool
    {
        if ($user->isAdmin()) return true;
        return WorkflowPermission::canUserAct($user->id, 'reject_maintenance_report');
    }

    /**
     * Get available take-off actions for user
     */
    public static function getTakeoffActions(QuantityTakeoff $takeoff, User $user): array
    {
        $actions = [];
        $currentStatus = $takeoff->status;
        $workflow = self::TAKEOFF_WORKFLOW[$currentStatus] ?? null;
        
        if (!$workflow) return [];
        
        foreach ($workflow['next'] as $nextStatus) {
            $canAct = false;
            if ($nextStatus == 'verified') {
                $canAct = self::canUserVerifyTakeoff($user);
            } elseif ($nextStatus == 'approved') {
                $canAct = self::canUserApproveTakeoff($user);
            }
            
            if ($canAct) {
                $nextWorkflow = self::TAKEOFF_WORKFLOW[$nextStatus];
                $actions[] = [
                    'status' => $nextStatus,
                    'label' => 'Mark as ' . $nextWorkflow['label'],
                    'icon' => $nextWorkflow['icon'],
                    'color' => $nextWorkflow['color'],
                ];
            }
        }
        
        return $actions;
    }
}
