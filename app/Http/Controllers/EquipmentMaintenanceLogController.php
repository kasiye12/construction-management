<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\EquipmentMaintenanceLog;
use App\Models\Project;
use App\Services\EquipmentMaintenanceExcelService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EquipmentMaintenanceLogController extends Controller
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

        $query = EquipmentMaintenanceLog::with(['equipment', 'project', 'loggedByUser']);

        // Project scoping
        if (!$user->isAdmin() && !$user->is_super_admin) {
            $query->where(function ($q) use ($userProjectIds) {
                $q->whereIn('project_id', $userProjectIds)
                  ->orWhereNull('project_id');
            });
        }

        if ($request->filled('equipment_id')) {
            $query->where('equipment_id', $request->equipment_id);
        }
        if ($request->filled('project_id')) {
            $pId = $request->project_id;
            if ($pId === 'central') {
                $query->whereNull('project_id');
            } elseif ($user->isAdmin() || $user->is_super_admin || in_array($pId, $userProjectIds)) {
                $query->where('project_id', $pId);
            }
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('spare_part_name', 'like', "%{$s}%")
                  ->orWhere('po_no', 'like', "%{$s}%")
                  ->orWhere('equipment_plate', 'like', "%{$s}%")
                  ->orWhere('equipment_type', 'like', "%{$s}%")
                  ->orWhere('supplier', 'like', "%{$s}%");
            });
        }
        if ($request->filled('date_from')) {
            $query->where('log_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('log_date', '<=', $request->date_to);
        }

        $totalCost = (clone $query)->sum('total_cost_with_vat');
        $totalLogsCount = (clone $query)->count();

        $logs = $query->latest('log_date')->paginate(25)->withQueryString();
        $equipments = Equipment::orderBy('equipment_type')->get();

        return view('equipment-logs.index', compact(
            'logs',
            'totalCost',
            'totalLogsCount',
            'equipments',
            'projects'
        ));
    }

    public function create()
    {
        $equipments = Equipment::orderBy('category')->orderBy('equipment_type')->get();
        $projects = $this->getUserProjects();

        return view('equipment-logs.create', compact('equipments', 'projects'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $validated = $request->validate([
            'equipment_id' => 'nullable|exists:equipment,id',
            'equipment_type' => 'required|string|max:100',
            'equipment_plate' => 'nullable|string|max:100',
            'spare_part_name' => 'required|string|max:255',
            'po_no' => 'nullable|string|max:100',
            'unit' => 'required|string|max:30',
            'quantity' => 'required|numeric|min:0.01',
            'unit_cost' => 'required|numeric|min:0',
            'total_cost_with_vat' => 'nullable|numeric|min:0',
            'supplier' => 'nullable|string|max:150',
            'log_date' => 'required|date',
            'project_id' => 'nullable|exists:projects,id',
            'location_name' => 'nullable|string|max:150',
            'remarks' => 'nullable|string',
        ]);

        if (!empty($validated['project_id']) && !$user->isAdmin() && !$user->is_super_admin) {
            if (!in_array($validated['project_id'], $this->getUserProjectIds())) {
                abort(403, 'Unauthorized: You are not assigned to this project.');
            }
        }

        $validated['logged_by'] = Auth::id();

        if (empty($validated['total_cost_with_vat'])) {
            $validated['total_cost_with_vat'] = round($validated['quantity'] * $validated['unit_cost'] * 1.15, 2);
        }

        if (!empty($validated['equipment_id']) && empty($validated['equipment_plate'])) {
            $eq = Equipment::find($validated['equipment_id']);
            if ($eq) {
                $validated['equipment_plate'] = $eq->plate_serial_no;
                if (empty($validated['equipment_type'])) {
                    $validated['equipment_type'] = $eq->equipment_type;
                }
            }
        }

        $log = EquipmentMaintenanceLog::create($validated);

        return redirect()->route('equipment-logs.index')
            ->with('success', "Spare part / maintenance transaction recorded for {$log->equipment_plate}.");
    }

    public function edit(EquipmentMaintenanceLog $equipmentLog)
    {
        $user = Auth::user();
        if ($equipmentLog->project_id && !$user->isAdmin() && !$user->is_super_admin && !in_array($equipmentLog->project_id, $this->getUserProjectIds())) {
            abort(403, 'Unauthorized access to this maintenance log.');
        }

        $equipments = Equipment::orderBy('category')->orderBy('equipment_type')->get();
        $projects = $this->getUserProjects();

        return view('equipment-logs.edit', compact('equipmentLog', 'equipments', 'projects'));
    }

    public function update(Request $request, EquipmentMaintenanceLog $equipmentLog)
    {
        $user = Auth::user();
        if ($equipmentLog->project_id && !$user->isAdmin() && !$user->is_super_admin && !in_array($equipmentLog->project_id, $this->getUserProjectIds())) {
            abort(403, 'Unauthorized access to this maintenance log.');
        }

        $validated = $request->validate([
            'equipment_id' => 'nullable|exists:equipment,id',
            'equipment_type' => 'required|string|max:100',
            'equipment_plate' => 'nullable|string|max:100',
            'spare_part_name' => 'required|string|max:255',
            'po_no' => 'nullable|string|max:100',
            'unit' => 'required|string|max:30',
            'quantity' => 'required|numeric|min:0.01',
            'unit_cost' => 'required|numeric|min:0',
            'total_cost_with_vat' => 'nullable|numeric|min:0',
            'supplier' => 'nullable|string|max:150',
            'log_date' => 'required|date',
            'project_id' => 'nullable|exists:projects,id',
            'location_name' => 'nullable|string|max:150',
            'remarks' => 'nullable|string',
        ]);

        if (!empty($validated['project_id']) && !$user->isAdmin() && !$user->is_super_admin) {
            if (!in_array($validated['project_id'], $this->getUserProjectIds())) {
                abort(403, 'Unauthorized: You are not assigned to this project.');
            }
        }

        if (empty($validated['total_cost_with_vat'])) {
            $validated['total_cost_with_vat'] = round($validated['quantity'] * $validated['unit_cost'] * 1.15, 2);
        }

        $equipmentLog->update($validated);

        return redirect()->route('equipment-logs.index')
            ->with('success', 'Maintenance log record updated successfully.');
    }

    public function export(Request $request)
    {
        $user = Auth::user();
        $userProjectIds = $this->getUserProjectIds();

        $query = EquipmentMaintenanceLog::with(['equipment', 'project']);

        if (!$user->isAdmin() && !$user->is_super_admin) {
            $query->where(function ($q) use ($userProjectIds) {
                $q->whereIn('project_id', $userProjectIds)
                  ->orWhereNull('project_id');
            });
        }

        if ($request->filled('equipment_id')) {
            $query->where('equipment_id', $request->equipment_id);
        }
        if ($request->filled('project_id')) {
            $pId = $request->project_id;
            if ($pId === 'central') {
                $query->whereNull('project_id');
            } elseif ($user->isAdmin() || $user->is_super_admin || in_array($pId, $userProjectIds)) {
                $query->where('project_id', $pId);
            }
        }
        if ($request->filled('date_from')) {
            $query->where('log_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('log_date', '<=', $request->date_to);
        }

        $logs = $query->latest('log_date')->get();
        $filePath = $this->excelService->exportDailyReport($logs);
        $fileName = 'TNT-EAMD-Daily-Maintenance-Logs-' . date('Y-m-d') . '.xlsx';

        return response()->download($filePath, $fileName)->deleteFileAfterSend(true);
    }

    public function import(Request $request)
    {
        $user = Auth::user();
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
            $result = $this->excelService->importDailyLogs(
                $request->file('excel_file'),
                $request->project_id ? (int)$request->project_id : null,
                Auth::id()
            );

            return redirect()->route('equipment-logs.index')
                ->with('success', "Import successful: {$result['imported_count']} log entries recorded with total " . number_format($result['total_cost'], 2) . " ETB.");
        } catch (\Throwable $e) {
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    public function destroy(EquipmentMaintenanceLog $equipmentLog)
    {
        $user = Auth::user();
        if (!$user) {
            abort(401);
        }

        if ($equipmentLog->project_id && !$user->isAdmin() && !$user->is_super_admin && !in_array($equipmentLog->project_id, $this->getUserProjectIds())) {
            abort(403, 'Unauthorized access to this maintenance log.');
        }

        $hasPermission = $user->isAdmin() || $user->is_super_admin || $user->hasPermission('equipment-logs.delete');
        if (!$hasPermission) {
            return back()->with('error', 'Unauthorized: You do not have permission to delete maintenance log records.');
        }

        $equipmentLog->delete();
        return redirect()->route('equipment-logs.index')
            ->with('success', 'Maintenance log entry deleted.');
    }
}
