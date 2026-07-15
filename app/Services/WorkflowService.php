<?php
namespace App\Services;

use App\Models\Ipc;
use App\Models\Notification;
use App\Models\User;
use App\Models\WorkflowPermission;

class WorkflowService
{
    const STATUS_DRAFT = 'draft';
    const STATUS_PREPARED = 'prepared';
    const STATUS_CHECKED = 'checked';
    const STATUS_SUBMITTED = 'submitted';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_PAID = 'paid';

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

    const ROUTE_MAP = [
        'prepared' => 'ipcs.prepare',
        'checked' => 'ipcs.check',
        'submitted' => 'ipcs.submit',
        'approved' => 'ipcs.approve',
        'rejected' => 'ipcs.reject',
        'paid' => 'ipcs.mark-paid',
    ];

    public static function getStatusLabel($status): string
    {
        return self::WORKFLOW[$status]['label'] ?? ucfirst($status);
    }

    public static function getStatusColor($status): string
    {
        return self::WORKFLOW[$status]['color'] ?? 'secondary';
    }

    public static function getStatusIcon($status): string
    {
        return self::WORKFLOW[$status]['icon'] ?? 'circle';
    }

    public static function canTransition($currentStatus, $newStatus): bool
    {
        $allowed = self::WORKFLOW[$currentStatus]['next'] ?? [];
        return in_array($newStatus, $allowed);
    }

    /**
     * Check if user has permission for this workflow step
     */
    public static function canUserPerformStep(User $user, string $step): bool
    {
        // Admin can do everything
        if ($user->isAdmin()) return true;
        
        // Check specific workflow permission
        return WorkflowPermission::canUserAct($user->id, $step);
    }

    /**
     * Get available actions for user on this IPC
     */
    public static function getAvailableActions(Ipc $ipc, User $user): array
    {
        $actions = [];
        $nextStatuses = self::WORKFLOW[$ipc->status]['next'] ?? [];

        foreach ($nextStatuses as $nextStatus) {
            if (self::canUserPerformStep($user, $nextStatus)) {
                $actions[] = [
                    'status' => $nextStatus,
                    'label' => 'Mark as ' . self::WORKFLOW[$nextStatus]['label'],
                    'icon' => self::WORKFLOW[$nextStatus]['icon'],
                    'color' => self::WORKFLOW[$nextStatus]['color'],
                    'route_name' => self::ROUTE_MAP[$nextStatus] ?? 'ipcs.' . $nextStatus,
                ];
            }
        }
        return $actions;
    }

    /**
     * Get users who can perform a specific step (for selection)
     */
    public static function getUsersForStep(string $step): array
    {
        $users = WorkflowPermission::getUsersForStep($step);
        if ($users->isEmpty()) {
            // Fallback: get all active users
            $users = User::where('is_active', true)->get();
        }
        return $users->toArray();
    }

    public static function transition(Ipc $ipc, string $newStatus, User $user): void
    {
        $fieldMap = [
            'prepared' => 'prepared_by', 'checked' => 'checked_by',
            'submitted' => 'submitted_by', 'approved' => 'approved_by',
            'rejected' => 'rejected_by', 'paid' => 'paid_by',
        ];
        $timeMap = [
            'prepared' => 'prepared_at', 'checked' => 'checked_at',
            'submitted' => 'submitted_at', 'approved' => 'approved_at',
            'rejected' => 'rejected_at', 'paid' => 'paid_at',
        ];

        $updateData = ['status' => $newStatus];
        if (isset($fieldMap[$newStatus])) {
            $updateData[$fieldMap[$newStatus]] = request($fieldMap[$newStatus] . '_name', $user->name);
        }
        if (isset($timeMap[$newStatus])) {
            $updateData[$timeMap[$newStatus]] = now();
        }

        $ipc->update($updateData);
        self::sendNotification($ipc, $newStatus, $user);
    }

    private static function sendNotification(Ipc $ipc, string $newStatus, User $user): void
    {
        $messages = [
            'prepared' => ['manager', 'Certificate Prepared', "IPC {$ipc->ipc_number} prepared by {$user->name}"],
            'checked' => ['manager', 'Certificate Checked', "IPC {$ipc->ipc_number} checked by {$user->name}"],
            'submitted' => ['manager', 'Certificate Submitted', "IPC {$ipc->ipc_number} submitted for approval"],
            'approved' => ['engineer', 'Certificate Approved', "IPC {$ipc->ipc_number} has been approved"],
            'rejected' => ['engineer', 'Certificate Rejected', "IPC {$ipc->ipc_number} has been rejected"],
            'paid' => ['engineer', 'Payment Released', "Payment for IPC {$ipc->ipc_number} released"],
        ];

        if (isset($messages[$newStatus])) {
            [$role, $title, $message] = $messages[$newStatus];
            Notification::sendToRole($role, 'ipc_' . $newStatus, $title, $message, route('ipcs.show', $ipc));
        }
    }
}
