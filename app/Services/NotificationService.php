<?php
namespace App\Services;

use App\Models\Notification;
use App\Models\Ipc;
use App\Models\BoqItem;
use App\Models\Project;
use App\Models\ActualCost;
use App\Models\ContractualReport;
use App\Models\ContractualClaimEvent;
use App\Models\QuantityTakeoff;
use App\Models\TakeoffSheet;
use App\Models\MaterialDelivery;
use App\Models\PmScheduleItem;
use App\Models\EquipmentBreakdownItem;
use App\Models\Document;
use App\Models\User;

class NotificationService
{
    /**
     * Get live document counts and statuses filtered by user permissions
     */
    public function getDocumentStats(User $user): array
    {
        $categories = [];
        $actionRequired = 0;
        $urgentAlerts = 0;
        $totalMonitored = 0;

        // 1. Contractual Follow-up Reports (CAD-01)
        if ($user->hasPermission('contractual-reports.view')) {
            $totalCad = ContractualReport::count();
            $pendingCad = ContractualReport::whereIn('status', ['submitted', 'checked'])->count();
            $approvedCad = ContractualReport::where('status', 'approved')->count();
            $claimsCount = ContractualClaimEvent::count();

            $actionRequired += $pendingCad;
            $totalMonitored += $totalCad;

            $categories['cad01'] = [
                'key' => 'cad01',
                'title' => 'Contractual Follow-up (CAD-01)',
                'subtitle' => 'Weekly operational & claim reports',
                'icon' => 'fas fa-file-contract',
                'color' => 'danger',
                'route' => route('contractual-reports.index'),
                'total' => $totalCad,
                'pending' => $pendingCad,
                'approved' => $approvedCad,
                'extra_label' => 'Logged Claims',
                'extra_value' => $claimsCount,
                'status_summary' => $pendingCad > 0 ? "{$pendingCad} Pending Review" : 'All Reports Current',
                'status_color' => $pendingCad > 0 ? 'warning' : 'success',
            ];
        }

        // 2. Payment Certificates (IPCs)
        if ($user->hasPermission('ipc.view')) {
            $totalIpc = Ipc::count();
            $pendingIpc = Ipc::where('status', 'submitted')->count();
            $approvedIpc = Ipc::where('status', 'approved')->count();
            $paidIpc = Ipc::where('status', 'paid')->count();

            $actionRequired += $pendingIpc;
            $totalMonitored += $totalIpc;

            $categories['ipc'] = [
                'key' => 'ipc',
                'title' => 'Payment Certificates (IPCs)',
                'subtitle' => 'Subcontractor valuations & billing',
                'icon' => 'fas fa-file-invoice-dollar',
                'color' => 'success',
                'route' => route('ipcs.index'),
                'total' => $totalIpc,
                'pending' => $pendingIpc,
                'approved' => $approvedIpc,
                'extra_label' => 'Certified & Paid',
                'extra_value' => $paidIpc,
                'status_summary' => $pendingIpc > 0 ? "{$pendingIpc} Awaiting Approval" : 'All Certificates Paid',
                'status_color' => $pendingIpc > 0 ? 'warning' : 'success',
            ];
        }

        // 3. Plant & Equipment PM Schedules (OF/074)
        if ($user->hasPermission('pm-schedules.view')) {
            $totalPm = PmScheduleItem::count();
            $overduePm = PmScheduleItem::where('service_status', 'Overdue')->count();
            $duePm = PmScheduleItem::where('service_status', 'Due')->count();
            $normalPm = PmScheduleItem::where('service_status', 'Normal')->count();

            $urgentAlerts += $overduePm;
            $actionRequired += $overduePm;
            $totalMonitored += $totalPm;

            $categories['pm_schedules'] = [
                'key' => 'pm_schedules',
                'title' => 'PM Maintenance (OF/074)',
                'subtitle' => 'Equipment preventative service schedule',
                'icon' => 'fas fa-calendar-check',
                'color' => 'warning',
                'route' => route('pm-schedules.index'),
                'total' => $totalPm,
                'pending' => $overduePm,
                'approved' => $normalPm,
                'extra_label' => 'Due Next 7 Days',
                'extra_value' => $duePm,
                'status_summary' => $overduePm > 0 ? "{$overduePm} Overdue Maintenance!" : 'Fleet Maintenance OK',
                'status_color' => $overduePm > 0 ? 'danger' : 'success',
            ];
        }

        // 4. Equipment Breakdown Incidents (OF/030)
        if ($user->hasPermission('equipment-breakdowns.view')) {
            $totalBreakdowns = EquipmentBreakdownItem::count();
            $downBreakdowns = EquipmentBreakdownItem::whereIn('repair_status', ['down', 'under_maintenance', 'under maintenance', 'proforma', 'purchase'])->count();
            $repairedBreakdowns = EquipmentBreakdownItem::where('repair_status', 'repaired')->count();

            $urgentAlerts += $downBreakdowns;
            $actionRequired += $downBreakdowns;
            $totalMonitored += $totalBreakdowns;

            $categories['breakdowns'] = [
                'key' => 'breakdowns',
                'title' => 'Breakdown Reports (OF/030)',
                'subtitle' => 'Site machine repairs & outage logs',
                'icon' => 'fas fa-tools',
                'color' => 'danger',
                'route' => route('equipment-breakdowns.index'),
                'total' => $totalBreakdowns,
                'pending' => $downBreakdowns,
                'approved' => $repairedBreakdowns,
                'extra_label' => 'Total Incidents',
                'extra_value' => $totalBreakdowns,
                'status_summary' => $downBreakdowns > 0 ? "{$downBreakdowns} Machines Down" : 'All Machines Operational',
                'status_color' => $downBreakdowns > 0 ? 'danger' : 'success',
            ];
        }

        // 5. Quantity Take-Off (QTO)
        if ($user->hasPermission('takeoff.view')) {
            $totalQto = QuantityTakeoff::count();
            $draftQto = QuantityTakeoff::whereIn('status', ['draft', 'submitted'])->count();
            $verifiedQto = QuantityTakeoff::where('status', 'verified')->count();

            $actionRequired += $draftQto;
            $totalMonitored += $totalQto;

            $categories['takeoff'] = [
                'key' => 'takeoff',
                'title' => 'Quantity Take-Off (QTO)',
                'subtitle' => 'Dimensional structural measurements',
                'icon' => 'fas fa-ruler-combined',
                'color' => 'primary',
                'route' => route('quantity-takeoffs.index'),
                'total' => $totalQto,
                'pending' => $draftQto,
                'approved' => $verifiedQto,
                'extra_label' => 'Verified Takeoffs',
                'extra_value' => $verifiedQto,
                'status_summary' => $draftQto > 0 ? "{$draftQto} Draft QTO" : 'All Takeoffs Verified',
                'status_color' => $draftQto > 0 ? 'warning' : 'success',
            ];
        }

        // 6. Material Deliveries
        if ($user->hasPermission('deliveries.view')) {
            $totalDeliveries = MaterialDelivery::count();
            $pendingDeliveries = MaterialDelivery::whereIn('status', ['received', 'inspected'])->count();
            $acceptedDeliveries = MaterialDelivery::where('status', 'accepted')->count();

            $actionRequired += $pendingDeliveries;
            $totalMonitored += $totalDeliveries;

            $categories['deliveries'] = [
                'key' => 'deliveries',
                'title' => 'Material Deliveries',
                'subtitle' => 'Site material gate passes & delivery notes',
                'icon' => 'fas fa-truck-loading',
                'color' => 'info',
                'route' => route('material-deliveries.index'),
                'total' => $totalDeliveries,
                'pending' => $pendingDeliveries,
                'approved' => $acceptedDeliveries,
                'extra_label' => 'Accepted Orders',
                'extra_value' => $acceptedDeliveries,
                'status_summary' => $pendingDeliveries > 0 ? "{$pendingDeliveries} Pending Inspection" : 'Deliveries Verified',
                'status_color' => $pendingDeliveries > 0 ? 'warning' : 'success',
            ];
        }

        // 7. Construction Projects & Deadlines
        if ($user->hasPermission('projects.view')) {
            $totalProjects = Project::count();
            $activeProjects = Project::where('status', 'active')->count();
            $nearDeadline = Project::where('status', 'active')
                ->whereNotNull('end_date')
                ->where('end_date', '<=', now()->addDays(30))
                ->count();

            $totalMonitored += $totalProjects;
            if ($nearDeadline > 0) $urgentAlerts += $nearDeadline;

            $categories['projects'] = [
                'key' => 'projects',
                'title' => 'Construction Projects',
                'subtitle' => 'Active construction project portfolio',
                'icon' => 'fas fa-hard-hat',
                'color' => 'primary',
                'route' => route('projects.index'),
                'total' => $totalProjects,
                'pending' => $nearDeadline,
                'approved' => $activeProjects,
                'extra_label' => 'Active Sites',
                'extra_value' => $activeProjects,
                'status_summary' => $nearDeadline > 0 ? "{$nearDeadline} Projects Near Deadline" : "{$activeProjects} Active Sites",
                'status_color' => $nearDeadline > 0 ? 'warning' : 'success',
            ];
        }

        // 8. Documents Repository
        if ($user->hasPermission('documents.view')) {
            $totalDocs = Document::count();
            $totalMonitored += $totalDocs;

            $categories['documents'] = [
                'key' => 'documents',
                'title' => 'Project Documents',
                'subtitle' => 'Drawings, contracts, and transmittals',
                'icon' => 'fas fa-folder-open',
                'color' => 'secondary',
                'route' => route('projects.index'),
                'total' => $totalDocs,
                'pending' => 0,
                'approved' => $totalDocs,
                'extra_label' => 'Files Stored',
                'extra_value' => $totalDocs,
                'status_summary' => "{$totalDocs} Official Files",
                'status_color' => 'info',
            ];
        }

        return [
            'categories' => $categories,
            'action_required_count' => $actionRequired,
            'urgent_alerts_count' => $urgentAlerts,
            'total_monitored_documents' => $totalMonitored,
        ];
    }

    /**
     * Synchronize and auto-generate live document news notifications based on actual operational documents
     */
    public function syncDocumentNews(?User $targetUser = null): int
    {
        $users = $targetUser ? collect([$targetUser]) : User::where('is_active', true)->get();
        $syncedCount = 0;

        foreach ($users as $user) {
            // 1. Overdue PM Maintenance News
            if ($user->hasPermission('pm-schedules.view')) {
                $overduePm = PmScheduleItem::where('service_status', 'Overdue')->count();
                if ($overduePm > 0) {
                    Notification::sendOrUpdate(
                        $user->id,
                        'pm_overdue',
                        "⚠️ {$overduePm} Fleet Equipment Units Overdue for PM Maintenance (OF/074)",
                        "Critical fleet equipment has exceeded scheduled maintenance intervals. Immediate service scheduling required to prevent breakdown.",
                        route('pm-schedules.index')
                    );
                    $syncedCount++;
                }

                $duePm = PmScheduleItem::where('service_status', 'Due')->count();
                if ($duePm > 0) {
                    Notification::sendOrUpdate(
                        $user->id,
                        'pm_due_soon',
                        "📅 {$duePm} Equipment Units Scheduled for Service in Next 7 Days",
                        "Preventative maintenance is approaching for {$duePm} fleet assets. Verify service team availability.",
                        route('pm-schedules.index')
                    );
                    $syncedCount++;
                }
            }

            // 2. Equipment Breakdown News
            if ($user->hasPermission('equipment-breakdowns.view')) {
                $downCount = EquipmentBreakdownItem::whereIn('repair_status', ['down', 'under_maintenance', 'under maintenance', 'proforma', 'purchase'])->count();
                if ($downCount > 0) {
                    Notification::sendOrUpdate(
                        $user->id,
                        'breakdown_reported',
                        "🚨 {$downCount} Equipment Breakdown Incidents Under Active Repair (OF/030)",
                        "Site machines are recorded as DOWN or undergoing maintenance. Review repair status and expedited spare parts procurement.",
                        route('equipment-breakdowns.index')
                    );
                    $syncedCount++;
                }
            }

            // 3. Contractual Follow-up Reports (CAD-01) News
            if ($user->hasPermission('contractual-reports.view')) {
                $pendingCad = ContractualReport::whereIn('status', ['submitted', 'checked'])->count();
                if ($pendingCad > 0) {
                    Notification::sendOrUpdate(
                        $user->id,
                        'contractual_report_submitted',
                        "📄 {$pendingCad} Contractual Follow-up (CAD-01) Reports Awaiting Action",
                        "{$pendingCad} weekly/monthly contractual reports have been submitted and require site engineer check or PM approval.",
                        route('contractual-reports.index')
                    );
                    $syncedCount++;
                }
            }

            // 4. Payment Certificates (IPCs) News
            if ($user->hasPermission('ipc.view')) {
                $pendingIpc = Ipc::where('status', 'submitted')->count();
                if ($pendingIpc > 0) {
                    Notification::sendOrUpdate(
                        $user->id,
                        'ipc_submitted',
                        "💰 {$pendingIpc} Payment Certificates (IPCs) Awaiting Review",
                        "Subcontractor payment certificates have been submitted for engineering review and executive certification.",
                        route('ipcs.index')
                    );
                    $syncedCount++;
                }
            }

            // 5. Material Deliveries News
            if ($user->hasPermission('deliveries.view')) {
                $pendingDeliv = MaterialDelivery::whereIn('status', ['received', 'inspected'])->count();
                if ($pendingDeliv > 0) {
                    Notification::sendOrUpdate(
                        $user->id,
                        'delivery_inspected',
                        "🚚 {$pendingDeliv} Material Shipments Awaiting Confirmation",
                        "New material shipments have arrived on site and require gate pass confirmation and lab test review.",
                        route('material-deliveries.index')
                    );
                    $syncedCount++;
                }
            }

            // 6. Quantity Take-off (QTO) News
            if ($user->hasPermission('takeoff.view')) {
                $draftQto = QuantityTakeoff::whereIn('status', ['draft', 'submitted'])->count();
                if ($draftQto > 0) {
                    Notification::sendOrUpdate(
                        $user->id,
                        'takeoff_created',
                        "📐 {$draftQto} Quantity Take-offs Pending Verification",
                        "Measurement takeoffs have been prepared and require quantity surveyor verification.",
                        route('quantity-takeoffs.index')
                    );
                    $syncedCount++;
                }
            }

            // 7. Project Portfolio & Deadlines News
            if ($user->hasPermission('projects.view')) {
                $nearDeadline = Project::where('status', 'active')
                    ->whereNotNull('end_date')
                    ->where('end_date', '<=', now()->addDays(30))
                    ->count();

                if ($nearDeadline > 0) {
                    Notification::sendOrUpdate(
                        $user->id,
                        'deadline',
                        "⏱️ Project Milestone Alert: {$nearDeadline} Project(s) Near Deadline",
                        "{$nearDeadline} active construction projects have target completion dates within 30 days. Review critical path schedule.",
                        route('projects.index')
                    );
                    $syncedCount++;
                }
            }
        }

        return $syncedCount;
    }

    /**
     * Check and send IPC-related notifications
     */
    public function checkIpcNotifications(Ipc $ipc)
    {
        if ($ipc->status === 'submitted') {
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
