<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\PmSchedule;
use App\Models\PmScheduleItem;
use App\Models\Project;
use App\Services\EquipmentMaintenanceExcelService;
use App\Services\WorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PmScheduleController extends Controller
{
    protected EquipmentMaintenanceExcelService $excelService;

    public function __construct(EquipmentMaintenanceExcelService $excelService)
    {
        $this->excelService = $excelService;
    }

    public function index(Request $request)
    {
        $query = PmSchedule::with(['project', 'preparedByUser', 'checkedByUser', 'approvedByUser']);

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $schedules = $query->latest('schedule_date')->paginate(15)->withQueryString();
        $projects = Project::orderBy('name')->get();

        $user = Auth::user();
        $canPrepare = WorkflowService::canUserPrepareEquipmentSchedule($user);
        $canCheck = WorkflowService::canUserCheckEquipmentSchedule($user);
        $canApprove = WorkflowService::canUserApproveEquipmentSchedule($user);

        return view('pm-schedules.index', compact(
            'schedules',
            'projects',
            'canPrepare',
            'canCheck',
            'canApprove'
        ));
    }

    public function create(Request $request)
    {
        $user = Auth::user();
        if (!WorkflowService::canUserPrepareEquipmentSchedule($user)) {
            return redirect()->route('pm-schedules.index')
                ->with('error', 'You do not have permission to prepare Preventive Maintenance Schedules.');
        }

        $projects = Project::orderBy('name')->get();
        $equipments = Equipment::where('status', '!=', 'idle')->orderBy('category')->orderBy('equipment_type')->get();
        $code = 'PMS-' . date('Y-') . str_pad(PmSchedule::count() + 1, 3, '0', STR_PAD_LEFT);

        return view('pm-schedules.create', compact('projects', 'equipments', 'code'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        if (!WorkflowService::canUserPrepareEquipmentSchedule($user)) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'schedule_code' => 'required|string|max:50|unique:pm_schedules,schedule_code',
            'title' => 'required|string|max:255',
            'project_id' => 'nullable|exists:projects,id',
            'project_location' => 'nullable|string|max:150',
            'schedule_date' => 'required|date',
            'period_start' => 'nullable|date',
            'period_end' => 'nullable|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.equipment_id' => 'nullable|exists:equipment,id',
            'items.*.category' => 'required|string',
            'items.*.equipment_type' => 'required|string',
            'items.*.make' => 'nullable|string',
            'items.*.model' => 'nullable|string',
            'items.*.plate_serial_no' => 'nullable|string',
            'items.*.serial_no' => 'nullable|string',
            'items.*.location' => 'nullable|string',
            'items.*.last_service_reading' => 'nullable|numeric',
            'items.*.last_service_date' => 'nullable|date',
            'items.*.last_service_type' => 'nullable|string',
            'items.*.next_service_reading' => 'nullable|numeric',
            'items.*.next_service_type' => 'nullable|string',
            'items.*.current_reading' => 'nullable|numeric',
            'items.*.current_reading_date' => 'nullable|date',
            'items.*.remark' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $schedule = PmSchedule::create([
                'schedule_code' => $validated['schedule_code'],
                'title' => $validated['title'],
                'document_code' => 'OF/EAMD/074',
                'issue_no' => '1',
                'page_no' => 'Page 1 of 1',
                'project_id' => $validated['project_id'] ?? null,
                'project_location' => $validated['project_location'] ?? null,
                'schedule_date' => $validated['schedule_date'],
                'period_start' => $validated['period_start'] ?? null,
                'period_end' => $validated['period_end'] ?? null,
                'status' => 'draft',
                'prepared_by' => $user->id,
                'prepared_name' => $user->name,
                'prepared_at' => now(),
                'notes' => $validated['notes'] ?? null,
                'created_by' => $user->id,
            ]);

            $sort = 0;
            foreach ($validated['items'] as $itemData) {
                $curr = isset($itemData['current_reading']) && is_numeric($itemData['current_reading']) ? (float)$itemData['current_reading'] : null;
                $next = isset($itemData['next_service_reading']) && is_numeric($itemData['next_service_reading']) ? (float)$itemData['next_service_reading'] : null;
                
                $remain = ($next !== null && $curr !== null) ? ($next - $curr) : null;
                $svcStatus = 'Normal';
                if ($remain !== null) {
                    if ($remain < 0) $svcStatus = 'Overdue';
                    elseif ($remain <= 50) $svcStatus = 'Due';
                }

                PmScheduleItem::create([
                    'pm_schedule_id' => $schedule->id,
                    'equipment_id' => $itemData['equipment_id'] ?? null,
                    'category' => $itemData['category'],
                    'equipment_type' => $itemData['equipment_type'],
                    'make' => $itemData['make'] ?? null,
                    'model' => $itemData['model'] ?? null,
                    'plate_serial_no' => $itemData['plate_serial_no'] ?? null,
                    'serial_no' => $itemData['serial_no'] ?? null,
                    'location' => $itemData['location'] ?? null,
                    'last_service_reading' => $itemData['last_service_reading'] ?? null,
                    'last_service_date' => $itemData['last_service_date'] ?? null,
                    'last_service_type' => $itemData['last_service_type'] ?? null,
                    'next_service_reading' => $next,
                    'next_service_type' => $itemData['next_service_type'] ?? null,
                    'current_reading' => $curr,
                    'current_reading_date' => $itemData['current_reading_date'] ?? null,
                    'remain_reading' => $remain,
                    'service_status' => $svcStatus,
                    'remark' => $itemData['remark'] ?? null,
                    'sort_order' => $sort++,
                ]);
            }

            DB::commit();

            return redirect()->route('pm-schedules.show', $schedule)
                ->with('success', "Preventive Maintenance Schedule '{$schedule->schedule_code}' created successfully.");
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error saving schedule: ' . $e->getMessage());
        }
    }

    public function show(PmSchedule $pmSchedule)
    {
        $pmSchedule->load(['items.equipment', 'project', 'preparedByUser', 'checkedByUser', 'approvedByUser']);
        
        $user = Auth::user();
        $canSubmit = $pmSchedule->isDraft() && WorkflowService::canUserPrepareEquipmentSchedule($user);
        $canCheck = $pmSchedule->isSubmitted() && WorkflowService::canUserCheckEquipmentSchedule($user);
        $canApprove = $pmSchedule->isChecked() && WorkflowService::canUserApproveEquipmentSchedule($user);
        $canReject = ($pmSchedule->isSubmitted() || $pmSchedule->isChecked()) && WorkflowService::canUserRejectEquipmentSchedule($user);
        $canEdit = $pmSchedule->isDraft() || $user->isAdmin();

        $itemsGrouped = $pmSchedule->items->groupBy('category');

        return view('pm-schedules.show', compact(
            'pmSchedule',
            'itemsGrouped',
            'canSubmit',
            'canCheck',
            'canApprove',
            'canReject',
            'canEdit'
        ));
    }

    public function edit(PmSchedule $pmSchedule)
    {
        $user = Auth::user();
        if (!$pmSchedule->isDraft() && !$user->isAdmin()) {
            return redirect()->route('pm-schedules.show', $pmSchedule)
                ->with('error', 'Only draft schedules can be edited.');
        }

        $pmSchedule->load('items');
        $projects = Project::orderBy('name')->get();
        $equipments = Equipment::orderBy('category')->orderBy('equipment_type')->get();

        return view('pm-schedules.edit', compact('pmSchedule', 'projects', 'equipments'));
    }

    public function update(Request $request, PmSchedule $pmSchedule)
    {
        $user = Auth::user();
        if (!$pmSchedule->isDraft() && !$user->isAdmin()) {
            abort(403, 'Unauthorized.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'project_id' => 'nullable|exists:projects,id',
            'project_location' => 'nullable|string|max:150',
            'schedule_date' => 'required|date',
            'period_start' => 'nullable|date',
            'period_end' => 'nullable|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.category' => 'required|string',
            'items.*.equipment_type' => 'required|string',
            'items.*.make' => 'nullable|string',
            'items.*.model' => 'nullable|string',
            'items.*.plate_serial_no' => 'nullable|string',
            'items.*.serial_no' => 'nullable|string',
            'items.*.location' => 'nullable|string',
            'items.*.last_service_reading' => 'nullable|numeric',
            'items.*.last_service_date' => 'nullable|date',
            'items.*.last_service_type' => 'nullable|string',
            'items.*.next_service_reading' => 'nullable|numeric',
            'items.*.next_service_type' => 'nullable|string',
            'items.*.current_reading' => 'nullable|numeric',
            'items.*.current_reading_date' => 'nullable|date',
            'items.*.remark' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $pmSchedule->update([
                'title' => $validated['title'],
                'project_id' => $validated['project_id'] ?? null,
                'project_location' => $validated['project_location'] ?? null,
                'schedule_date' => $validated['schedule_date'],
                'period_start' => $validated['period_start'] ?? null,
                'period_end' => $validated['period_end'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            $pmSchedule->items()->delete();

            $sort = 0;
            foreach ($validated['items'] as $itemData) {
                $curr = isset($itemData['current_reading']) && is_numeric($itemData['current_reading']) ? (float)$itemData['current_reading'] : null;
                $next = isset($itemData['next_service_reading']) && is_numeric($itemData['next_service_reading']) ? (float)$itemData['next_service_reading'] : null;
                $remain = ($next !== null && $curr !== null) ? ($next - $curr) : null;
                $svcStatus = 'Normal';
                if ($remain !== null) {
                    if ($remain < 0) $svcStatus = 'Overdue';
                    elseif ($remain <= 50) $svcStatus = 'Due';
                }

                PmScheduleItem::create([
                    'pm_schedule_id' => $pmSchedule->id,
                    'equipment_id' => $itemData['equipment_id'] ?? null,
                    'category' => $itemData['category'],
                    'equipment_type' => $itemData['equipment_type'],
                    'make' => $itemData['make'] ?? null,
                    'model' => $itemData['model'] ?? null,
                    'plate_serial_no' => $itemData['plate_serial_no'] ?? null,
                    'serial_no' => $itemData['serial_no'] ?? null,
                    'location' => $itemData['location'] ?? null,
                    'last_service_reading' => $itemData['last_service_reading'] ?? null,
                    'last_service_date' => $itemData['last_service_date'] ?? null,
                    'last_service_type' => $itemData['last_service_type'] ?? null,
                    'next_service_reading' => $next,
                    'next_service_type' => $itemData['next_service_type'] ?? null,
                    'current_reading' => $curr,
                    'current_reading_date' => $itemData['current_reading_date'] ?? null,
                    'remain_reading' => $remain,
                    'service_status' => $svcStatus,
                    'remark' => $itemData['remark'] ?? null,
                    'sort_order' => $sort++,
                ]);
            }

            DB::commit();

            return redirect()->route('pm-schedules.show', $pmSchedule)
                ->with('success', 'Preventive Maintenance Schedule updated successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error updating schedule: ' . $e->getMessage());
        }
    }

    public function submit(PmSchedule $pmSchedule)
    {
        $user = Auth::user();
        if (!WorkflowService::canUserPrepareEquipmentSchedule($user)) {
            abort(403, 'You do not have permission to submit PM schedules.');
        }

        $pmSchedule->update([
            'status' => 'submitted',
            'prepared_by' => $user->id,
            'prepared_name' => $user->name,
            'prepared_at' => now(),
            'rejection_reason' => null,
        ]);

        return back()->with('success', "Schedule '{$pmSchedule->schedule_code}' submitted for checking and verification.");
    }

    public function check(PmSchedule $pmSchedule)
    {
        $user = Auth::user();
        if (!WorkflowService::canUserCheckEquipmentSchedule($user)) {
            abort(403, 'You do not have permission to check and verify PM schedules.');
        }

        $pmSchedule->update([
            'status' => 'checked',
            'checked_by' => $user->id,
            'checked_name' => $user->name,
            'checked_at' => now(),
        ]);

        return back()->with('success', "Schedule '{$pmSchedule->schedule_code}' marked as Checked and verified.");
    }

    public function approve(PmSchedule $pmSchedule)
    {
        $user = Auth::user();
        if (!WorkflowService::canUserApproveEquipmentSchedule($user)) {
            abort(403, 'You do not have permission to approve PM schedules.');
        }

        $pmSchedule->update([
            'status' => 'approved',
            'approved_by' => $user->id,
            'approved_name' => $user->name,
            'approved_at' => now(),
        ]);

        return back()->with('success', "Schedule '{$pmSchedule->schedule_code}' officially Approved.");
    }

    public function reject(Request $request, PmSchedule $pmSchedule)
    {
        $user = Auth::user();
        if (!WorkflowService::canUserRejectEquipmentSchedule($user)) {
            abort(403, 'You do not have permission to reject PM schedules.');
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        $pmSchedule->update([
            'status' => 'rejected',
            'rejection_reason' => $request->rejection_reason,
        ]);

        return back()->with('warning', "Schedule '{$pmSchedule->schedule_code}' has been rejected and returned to Draft with comments.");
    }

    public function revert(PmSchedule $pmSchedule)
    {
        $user = Auth::user();
        if (!$user->isAdmin()) {
            abort(403, 'Only administrators can revert approved or verified schedules.');
        }

        $pmSchedule->update(['status' => 'draft']);

        return back()->with('info', "Schedule '{$pmSchedule->schedule_code}' reverted to Draft status.");
    }

    public function export(PmSchedule $pmSchedule)
    {
        $pmSchedule->load(['items.equipment', 'project', 'preparedByUser', 'checkedByUser', 'approvedByUser']);
        $filePath = $this->excelService->exportPmSchedule($pmSchedule);
        $fileName = "TNT-OF-EAMD-074-PMS-{$pmSchedule->schedule_code}.xlsx";

        return response()->download($filePath, $fileName)->deleteFileAfterSend(true);
    }

    public function import(Request $request)
    {
        $user = Auth::user();
        if (!WorkflowService::canUserPrepareEquipmentSchedule($user)) {
            abort(403, 'Unauthorized.');
        }

        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls|max:20480',
            'project_id' => 'nullable|exists:projects,id',
        ]);

        try {
            $result = $this->excelService->importPmSchedule(
                $request->file('excel_file'),
                $request->project_id ? (int)$request->project_id : null,
                $user->id
            );

            return redirect()->route('pm-schedules.show', $result['schedule'])
                ->with('success', "Import successful: {$result['items_count']} schedule items and {$result['equipment_count']} equipment records updated.");
        } catch (\Throwable $e) {
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        $path = $this->excelService->getTemplatePath();
        if (!file_exists($path)) {
            abort(404, 'Master template not found.');
        }

        return response()->download($path, 'TNT-OF-EAMD-074-Preventive-Maintenance-Schedule-Template.xlsx');
    }

    public function print(PmSchedule $pmSchedule)
    {
        $pmSchedule->load(['items.equipment', 'project', 'preparedByUser', 'checkedByUser', 'approvedByUser']);
        $itemsGrouped = $pmSchedule->items->groupBy('category');

        return view('pm-schedules.print', compact('pmSchedule', 'itemsGrouped'));
    }

    public function destroy(PmSchedule $pmSchedule)
    {
        $code = $pmSchedule->schedule_code;
        $pmSchedule->delete();

        return redirect()->route('pm-schedules.index')
            ->with('success', "Schedule '{$code}' deleted successfully.");
    }
}
