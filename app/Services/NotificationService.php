<?php
namespace App\Services;

use App\Models\Notification;
use App\Models\Ipc;
use App\Models\BoqItem;
use App\Models\Project;
use App\Models\ActualCost;

class NotificationService
{
    /**
     * Check and send IPC-related notifications
     */
    public function checkIpcNotifications(Ipc $ipc)
    {
        if ($ipc->status === 'submitted') {
            // Notify managers and finance
            Notification::sendToRole('manager', 'ipc_submitted', 
                'IPC Submitted for Approval',
                "IPC {$ipc->ipc_number} has been submitted for approval.",
                route('ipcs.show', $ipc)
            );
            
            Notification::sendToRole('finance', 'ipc_submitted',
                'New IPC for Review',
                "IPC {$ipc->ipc_number} from {$ipc->subcontractor->name} requires review.",
                route('ipcs.show', $ipc)
            );
        }

        if ($ipc->status === 'approved') {
            // Notify the creator
            Notification::sendToRole('engineer', 'ipc_approved',
                'IPC Approved',
                "IPC {$ipc->ipc_number} has been approved.",
                route('ipcs.show', $ipc)
            );
        }
    }

    /**
     * Check budget vs actual and send alerts
     */
    public function checkBudgetAlerts($projectId)
    {
        $items = BoqItem::where('project_id', $projectId)
            ->where('is_parent', false)
            ->with('actualCosts')
            ->get();

        foreach ($items as $item) {
            $budget = $item->total_budget_cost;
            $actual = $item->actualCosts->sum('amount');
            
            if ($budget > 0 && $actual > $budget) {
                Notification::sendToRole('manager', 'budget_exceeded',
                    'Budget Exceeded!',
                    "Budget exceeded for {$item->item_number} - {$item->description}. Budget: " . number_format($budget, 2) . ", Actual: " . number_format($actual, 2),
                    route('boq-items.show', $item)
                );
            }
        }
    }

    /**
     * Check project deadlines
     */
    public function checkDeadlines()
    {
        $projects = Project::where('status', 'active')
            ->where('end_date', '<=', now()->addDays(30))
            ->get();

        foreach ($projects as $project) {
            $daysLeft = now()->diffInDays($project->end_date, false);
            
            if ($daysLeft <= 30 && $daysLeft > 0) {
                Notification::sendToRole('manager', 'deadline',
                    'Project Deadline Approaching',
                    "Project '{$project->name}' deadline in {$daysLeft} days.",
                    route('projects.show', $project)
                );
            }
        }
    }

    /**
     * Send welcome notification
     */
    public function welcomeUser($userId)
    {
        Notification::sendToUser($userId, 'default',
            'Welcome to CMS!',
            'Welcome to the Construction Management System. You can start by viewing projects.',
            route('dashboard')
        );
    }
}
