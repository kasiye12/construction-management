<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\EquipmentBreakdownReport;
use App\Models\EquipmentBreakdownItem;
use App\Models\Project;
use App\Services\EquipmentMaintenanceExcelService;
use App\Services\WorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EquipmentBreakdownReportController extends Controller
{
    protected EquipmentMaintenanceExcelService $excelService;

    public function __construct(EquipmentMaintenanceExcelService $excelService)
    {
        $this->excelService = $excelService;
    }

    private function getUserProjectIds(): array
    {
        $user = Auth::user();
        if (!$user) return [];
        if ($user->isAdmin() || $user->is_super_admin) {
            return Project::withTrashed()->pluck('id')->toArray();
        }
        return $user->projects()
            ->withTrashed()
            ->where('project_user.is_active', true)
            ->pluck('projects.id')
            ->toArray();
    }

    private function getUserProjects()
    {
        $user = Auth::user();
        if (!$user) return collect();
        if ($user->isAdmin() || $user->is_super_admin) {
            return Project::orderBy('name')->get();
        }
        $ids = $this->getUserProjectIds();
        return Project::whereIn('id', $ids)->orderBy('name')->get();
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $userProjectIds = $this->getUserProjectIds();
        $projects = $this->getUserProjects();

        $query = EquipmentBreakdownReport::with(['project', 'preparedByUser', 'checkedByUser', 'approvedByUser']);

        // Scope to assigned projects (plus unassigned central workshop)
        if (!$user->isAdmin() && !$user->is_super_admin) {
            $query->where(function ($q) use ($userProjectIds) {
                $q->whereIn('project_id', $userProjectIds)
                  ->orWhereNull('project_id');
            });
        }

        if ($request->filled('project_id')) {
            $pId = $request->project_id;
            if ($pId === 'central') {
                $query->whereNull('project_id');
            } elseif ($user->isAdmin() || $user->is_super_admin || in_array($pId, $userProjectIds)) {
                $query->where('project_id', $pId);
            }
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $reports = $query->latest('report_date')->paginate(15)->withQueryString();

        $canPrepare = WorkflowService::canUserPrepareMaintenanceReport($user);
        $canCheck = WorkflowService::canUserCheckMaintenanceReport($user);
        $canApprove = WorkflowService::canUserApproveMaintenanceReport($user);

        return view('equipment-breakdowns.index', compact(
            'reports',
            'projects',
            'canPrepare',
            'canCheck',
            'canApprove'
        ));
    }

    public function create()
    {
        $user = Auth::user();
        if (!WorkflowService::canUserPrepareMaintenanceReport($user)) {
            return redirect()->route('equipment-breakdowns.index')
                ->with('error', 'You do not have permission to prepare Maintenance Reports.');
        }

        $projects = $this->getUserProjects();
        $equipments = Equipment::orderBy('category')->orderBy('equipment_type')->get();
        $categories = EquipmentBreakdownReport::CATEGORIES;
        $repairStatuses = EquipmentBreakdownReport::REPAIR_STATUSES;
        $code = EquipmentBreakdownReport::generateNextReportCode();

        return view('equipment-breakdowns.create', compact('projects', 'equipments', 'categories', 'repairStatuses', 'code'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        if (!WorkflowService::canUserPrepareMaintenanceReport($user)) {
            abort(403, 'Unauthorized.');
        }

        $validated = $request->validate([
            'report_code' => 'required|string|max:50',
            'title' => 'required|string|max:255',
            'report_date' => 'required|date',
            'project_id' => 'nullable|exists:projects,id',
            'location_name' => 'nullable|string|max:150',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.equipment_id' => 'nullable|exists:equipment,id',
            'items.*.category' => 'required|string',
            'items.*.equipment_type' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.plate_no_sn' => 'nullable|string',
            'items.*.down_date' => 'nullable|date',
            'items.*.reason_of_down' => 'nullable|string',
            'items.*.action_taken' => 'nullable|string',
            'items.*.location' => 'nullable|string',
            'items.*.repair_status' => 'required|string',
            'items.*.finished_date' => 'nullable|date',
            'items.*.remarks' => 'nullable|string',
        ]);

        // Validate user project assignment
        if (!empty($validated['project_id']) && !$user->isAdmin() && !$user->is_super_admin) {
            if (!in_array($validated['project_id'], $this->getUserProjectIds())) {
                abort(403, 'Unauthorized: You are not assigned to this project.');
            }
        }

        // Ensure collision-free report code
        $reportCode = $validated['report_code'] ?? null;
        if (empty($reportCode) || EquipmentBreakdownReport::withTrashed()->where('report_code', $reportCode)->exists()) {
            $reportCode = EquipmentBreakdownReport::generateNextReportCode();
        }

        DB::beginTransaction();
        try {
            $report = EquipmentBreakdownReport::create([
                'report_code' => $reportCode,
                'title' => $validated['title'],
                'document_code' => 'OF/TNT/ EAMD/030',
                'issue_no' => '1',
                'page_no' => 'Page 1 of 1',
                'report_date' => $validated['report_date'],
                'project_id' => $validated['project_id'] ?? null,
                'location_name' => $validated['location_name'] ?? null,
                'status' => 'draft',
                'prepared_by' => $user->id,
                'prepared_name' => $user->name,
                'prepared_at' => now(),
                'notes' => $validated['notes'] ?? null,
                'created_by' => $user->id,
            ]);

            $sort = 0;
            foreach ($validated['items'] as $itemData) {
                EquipmentBreakdownItem::create([
                    'breakdown_report_id' => $report->id,
                    'equipment_id' => $itemData['equipment_id'] ?? null,
                    'category' => $itemData['category'],
                    'equipment_type' => $itemData['equipment_type'],
                    'quantity' => $itemData['quantity'] ?? 1,
                    'plate_no_sn' => $itemData['plate_no_sn'] ?? null,
                    'down_date' => $itemData['down_date'] ?? null,
                    'reason_of_down' => $itemData['reason_of_down'] ?? null,
                    'action_taken' => $itemData['action_taken'] ?? null,
                    'location' => $itemData['location'] ?? null,
                    'repair_status' => $itemData['repair_status'] ?? 'down',
                    'finished_date' => $itemData['finished_date'] ?? null,
                    'remarks' => $itemData['remarks'] ?? null,
                    'sort_order' => $sort++,
                ]);

                // Update equipment status if linked
                if (!empty($itemData['equipment_id'])) {
                    $eq = Equipment::find($itemData['equipment_id']);
                    if ($eq) {
                        $st = strtolower($itemData['repair_status']);
                        if ($st === 'repaired' || $st === 'completed') {
                            $eq->update(['status' => 'active']);
                        } else {
                            $eq->update(['status' => 'breakdown']);
                        }
                    }
                }
            }

            DB::commit();

            return redirect()->route('equipment-breakdowns.show', $report)
                ->with('success', "Maintenance Report '{$report->report_code}' created successfully.");
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error creating breakdown report: ' . $e->getMessage());
        }
    }

    public function show(EquipmentBreakdownReport $equipmentBreakdown)
    {
        $user = Auth::user();
        if ($equipmentBreakdown->project_id && !$user->isAdmin() && !$user->is_super_admin && !in_array($equipmentBreakdown->project_id, $this->getUserProjectIds())) {
            abort(403, 'Unauthorized access to this project report.');
        }

        $equipmentBreakdown->load(['items.equipment', 'project', 'preparedByUser', 'checkedByUser', 'approvedByUser']);
        
        $canSubmit = ($equipmentBreakdown->isDraft() || $equipmentBreakdown->isRejected()) && WorkflowService::canUserPrepareMaintenanceReport($user);
        $canCheck = $equipmentBreakdown->isSubmitted() && WorkflowService::canUserCheckMaintenanceReport($user);
        $canApprove = $equipmentBreakdown->isChecked() && WorkflowService::canUserApproveMaintenanceReport($user);
        $canReject = ($equipmentBreakdown->isSubmitted() || $equipmentBreakdown->isChecked()) && WorkflowService::canUserRejectMaintenanceReport($user);
        $canEdit = ($equipmentBreakdown->isDraft() || $equipmentBreakdown->isRejected()) || $user->isAdmin();
        $canDelete = ($equipmentBreakdown->isDraft() || $equipmentBreakdown->isRejected()) && ($user->isAdmin() || $user->is_super_admin || $user->hasPermission('equipment-breakdowns.delete'));

        $itemsGrouped = $equipmentBreakdown->items->groupBy('category');

        return view('equipment-breakdowns.show', compact(
            'equipmentBreakdown',
            'itemsGrouped',
            'canSubmit',
            'canCheck',
            'canApprove',
            'canReject',
            'canEdit',
            'canDelete'
        ));
    }

    public function edit(EquipmentBreakdownReport $equipmentBreakdown)
    {
        $user = Auth::user();
        if ($equipmentBreakdown->project_id && !$user->isAdmin() && !$user->is_super_admin && !in_array($equipmentBreakdown->project_id, $this->getUserProjectIds())) {
            abort(403, 'Unauthorized access to this project report.');
        }

        if (!$equipmentBreakdown->isDraft() && !$equipmentBreakdown->isRejected() && !$user->isAdmin()) {
            return redirect()->route('equipment-breakdowns.show', $equipmentBreakdown)
                ->with('error', 'Only draft or rejected reports can be edited.');
        }

        $equipmentBreakdown->load('items');
        $projects = $this->getUserProjects();
        $equipments = Equipment::orderBy('category')->orderBy('equipment_type')->get();
        $categories = EquipmentBreakdownReport::CATEGORIES;
        $repairStatuses = EquipmentBreakdownReport::REPAIR_STATUSES;

        return view('equipment-breakdowns.edit', compact(
            'equipmentBreakdown',
            'projects',
            'equipments',
            'categories',
            'repairStatuses'
        ));
    }

    public function update(Request $request, EquipmentBreakdownReport $equipmentBreakdown)
    {
        $user = Auth::user();
        if ($equipmentBreakdown->project_id && !$user->isAdmin() && !$user->is_super_admin && !in_array($equipmentBreakdown->project_id, $this->getUserProjectIds())) {
            abort(403, 'Unauthorized access to this project report.');
        }

        if (!$equipmentBreakdown->isDraft() && !$equipmentBreakdown->isRejected() && !$user->isAdmin()) {
            abort(403, 'Unauthorized: Only draft or rejected reports can be edited.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'report_date' => 'required|date',
            'project_id' => 'nullable|exists:projects,id',
            'location_name' => 'nullable|string|max:150',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.category' => 'required|string',
            'items.*.equipment_type' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.plate_no_sn' => 'nullable|string',
            'items.*.down_date' => 'nullable|date',
            'items.*.reason_of_down' => 'nullable|string',
            'items.*.action_taken' => 'nullable|string',
            'items.*.location' => 'nullable|string',
            'items.*.repair_status' => 'required|string',
            'items.*.finished_date' => 'nullable|date',
            'items.*.remarks' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $equipmentBreakdown->update([
                'title' => $validated['title'],
                'report_date' => $validated['report_date'],
                'project_id' => $validated['project_id'] ?? null,
                'location_name' => $validated['location_name'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            $equipmentBreakdown->items()->delete();

            $sort = 0;
            foreach ($validated['items'] as $itemData) {
                EquipmentBreakdownItem::create([
                    'breakdown_report_id' => $equipmentBreakdown->id,
                    'equipment_id' => $itemData['equipment_id'] ?? null,
                    'category' => $itemData['category'],
                    'equipment_type' => $itemData['equipment_type'],
                    'quantity' => $itemData['quantity'] ?? 1,
                    'plate_no_sn' => $itemData['plate_no_sn'] ?? null,
                    'down_date' => $itemData['down_date'] ?? null,
                    'reason_of_down' => $itemData['reason_of_down'] ?? null,
                    'action_taken' => $itemData['action_taken'] ?? null,
                    'location' => $itemData['location'] ?? null,
                    'repair_status' => $itemData['repair_status'] ?? 'down',
                    'finished_date' => $itemData['finished_date'] ?? null,
                    'remarks' => $itemData['remarks'] ?? null,
                    'sort_order' => $sort++,
                ]);
            }

            DB::commit();

            return redirect()->route('equipment-breakdowns.show', $equipmentBreakdown)
                ->with('success', 'Maintenance Report updated successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error updating report: ' . $e->getMessage());
        }
    }

    public function submit(EquipmentBreakdownReport $equipmentBreakdown)
    {
        $user = Auth::user();
        if ($equipmentBreakdown->project_id && !$user->isAdmin() && !$user->is_super_admin && !in_array($equipmentBreakdown->project_id, $this->getUserProjectIds())) {
            abort(403, 'Unauthorized access to this project report.');
        }

        if (!WorkflowService::canUserPrepareMaintenanceReport($user)) {
            abort(403, 'Unauthorized.');
        }

        $equipmentBreakdown->update([
            'status' => 'submitted',
            'prepared_by' => $user->id,
            'prepared_name' => $user->name,
            'prepared_at' => now(),
            'rejection_reason' => null,
        ]);

        return back()->with('success', "Report '{$equipmentBreakdown->report_code}' submitted for checking.");
    }

    public function check(EquipmentBreakdownReport $equipmentBreakdown)
    {
        $user = Auth::user();
        if ($equipmentBreakdown->project_id && !$user->isAdmin() && !$user->is_super_admin && !in_array($equipmentBreakdown->project_id, $this->getUserProjectIds())) {
            abort(403, 'Unauthorized access to this project report.');
        }

        if (!WorkflowService::canUserCheckMaintenanceReport($user)) {
            abort(403, 'Unauthorized.');
        }

        $equipmentBreakdown->update([
            'status' => 'checked',
            'checked_by' => $user->id,
            'checked_name' => $user->name,
            'checked_at' => now(),
        ]);

        return back()->with('success', "Report '{$equipmentBreakdown->report_code}' checked and verified.");
    }

    public function approve(EquipmentBreakdownReport $equipmentBreakdown)
    {
        $user = Auth::user();
        if ($equipmentBreakdown->project_id && !$user->isAdmin() && !$user->is_super_admin && !in_array($equipmentBreakdown->project_id, $this->getUserProjectIds())) {
            abort(403, 'Unauthorized access to this project report.');
        }

        if (!WorkflowService::canUserApproveMaintenanceReport($user)) {
            abort(403, 'Unauthorized.');
        }

        $equipmentBreakdown->update([
            'status' => 'approved',
            'approved_by' => $user->id,
            'approved_name' => $user->name,
            'approved_at' => now(),
        ]);

        return back()->with('success', "Report '{$equipmentBreakdown->report_code}' approved officially.");
    }

    public function reject(Request $request, EquipmentBreakdownReport $equipmentBreakdown)
    {
        $user = Auth::user();
        if ($equipmentBreakdown->project_id && !$user->isAdmin() && !$user->is_super_admin && !in_array($equipmentBreakdown->project_id, $this->getUserProjectIds())) {
            abort(403, 'Unauthorized access to this project report.');
        }

        if (!WorkflowService::canUserRejectMaintenanceReport($user)) {
            abort(403, 'Unauthorized.');
        }

        $request->validate(['rejection_reason' => 'required|string|max:1000']);

        $equipmentBreakdown->update([
            'status' => 'rejected',
            'rejection_reason' => $request->rejection_reason,
        ]);

        return back()->with('warning', "Report '{$equipmentBreakdown->report_code}' rejected with comments.");
    }

    public function revert(EquipmentBreakdownReport $equipmentBreakdown)
    {
        $user = Auth::user();
        if (!$user->isAdmin()) {
            abort(403, 'Only administrators can revert reports.');
        }

        $equipmentBreakdown->update(['status' => 'draft']);

        return back()->with('info', "Report '{$equipmentBreakdown->report_code}' reverted to Draft.");
    }

    public function export(EquipmentBreakdownReport $equipmentBreakdown)
    {
        $user = Auth::user();
        if ($equipmentBreakdown->project_id && !$user->isAdmin() && !$user->is_super_admin && !in_array($equipmentBreakdown->project_id, $this->getUserProjectIds())) {
            abort(403, 'Unauthorized access to this project report.');
        }

        $equipmentBreakdown->load(['items.equipment', 'project', 'preparedByUser', 'checkedByUser', 'approvedByUser']);
        $filePath = $this->excelService->exportBreakdownReport($equipmentBreakdown);
        $fileName = "TNT-OF-EAMD-030-MR-{$equipmentBreakdown->report_code}.xlsx";

        return response()->download($filePath, $fileName)->deleteFileAfterSend(true);
    }

    public function import(Request $request)
    {
        $user = Auth::user();
        if (!WorkflowService::canUserPrepareMaintenanceReport($user)) {
            abort(403, 'Unauthorized.');
        }

        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls|max:20480',
            'project_id' => 'nullable|exists:projects,id',
        ]);

        if ($request->filled('project_id') && !$user->isAdmin() && !$user->is_super_admin) {
            if (!in_array($request->project_id, $this->getUserProjectIds())) {
                abort(403, 'Unauthorized: You are not assigned to this project.');
            }
        }

        try {
            $result = $this->excelService->importBreakdownReport(
                $request->file('excel_file'),
                $request->project_id ? (int)$request->project_id : null,
                $user->id
            );

            return redirect()->route('equipment-breakdowns.show', $result['report'])
                ->with('success', "Import successful: {$result['items_count']} breakdown items recorded.");
        } catch (\Throwable $e) {
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    public function print(EquipmentBreakdownReport $equipmentBreakdown)
    {
        $user = Auth::user();
        if ($equipmentBreakdown->project_id && !$user->isAdmin() && !$user->is_super_admin && !in_array($equipmentBreakdown->project_id, $this->getUserProjectIds())) {
            abort(403, 'Unauthorized access to this project report.');
        }

        $equipmentBreakdown->load(['items.equipment', 'project', 'preparedByUser', 'checkedByUser', 'approvedByUser']);
        $itemsGrouped = $equipmentBreakdown->items->groupBy('category');

        return view('equipment-breakdowns.print', compact('equipmentBreakdown', 'itemsGrouped'));
    }

    public function destroy(EquipmentBreakdownReport $equipmentBreakdown)
    {
        $user = Auth::user();
        if (!$user) {
            abort(401);
        }

        // 1. Project authorization check
        $userProjectIds = $this->getUserProjectIds();
        if ($equipmentBreakdown->project_id && !$user->isAdmin() && !$user->is_super_admin && !in_array($equipmentBreakdown->project_id, $userProjectIds)) {
            abort(403, 'Unauthorized access to this project report.');
        }

        // 2. Strict permission check: Must have equipment-breakdowns.delete permission or be Admin
        $hasPermission = $user->isAdmin() || $user->is_super_admin || $user->hasPermission('equipment-breakdowns.delete');
        if (!$hasPermission) {
            return back()->with('error', 'Unauthorized: You do not have permission to delete breakdown reports.');
        }

        // 3. Strict status check: Reports that are submitted, checked, or approved CANNOT be deleted
        if (!$equipmentBreakdown->isDraft() && !$equipmentBreakdown->isRejected()) {
            $statusLabel = ucfirst($equipmentBreakdown->status);
            return back()->with('error', "Deletion blocked: Cannot delete a {$statusLabel} breakdown report. Only reports in 'Draft' status can be removed from the system.");
        }

        $code = $equipmentBreakdown->report_code;
        $equipmentBreakdown->delete();

        return redirect()->route('equipment-breakdowns.index')
            ->with('success', "Breakdown report '{$code}' has been removed successfully.");
    }
}
