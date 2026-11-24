<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\Project;
use App\Models\PmScheduleItem;
use App\Models\EquipmentBreakdownItem;
use App\Models\EquipmentMaintenanceLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EquipmentController extends Controller
{
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

        $query = Equipment::with('project');

        // Project scoping for non-admin users
        if (!$user->isAdmin() && !$user->is_super_admin) {
            $query->where(function ($q) use ($userProjectIds) {
                $q->whereIn('project_id', $userProjectIds)
                  ->orWhereNull('project_id');
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
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
                $q->where('plate_serial_no', 'like', "%{$s}%")
                  ->orWhere('equipment_type', 'like', "%{$s}%")
                  ->orWhere('make', 'like', "%{$s}%")
                  ->orWhere('model', 'like', "%{$s}%")
                  ->orWhere('location_name', 'like', "%{$s}%")
                  ->orWhere('code', 'like', "%{$s}%");
            });
        }

        $equipments = $query->orderBy('category')->orderBy('equipment_type')->paginate(20)->withQueryString();

        // Calculate KPI Stats scoped to permitted fleet
        $statsQuery = Equipment::query();
        if (!$user->isAdmin() && !$user->is_super_admin) {
            $statsQuery->where(function ($q) use ($userProjectIds) {
                $q->whereIn('project_id', $userProjectIds)
                  ->orWhereNull('project_id');
            });
        }
        $allEquipments = $statsQuery->get();
        $totalCount = $allEquipments->count();
        $activeCount = $allEquipments->where('status', 'active')->count();
        $downCount = $allEquipments->whereIn('status', ['breakdown', 'under_maintenance'])->count();
        
        $dueCount = $allEquipments->filter(function($e) {
            $rem = $e->remain_reading;
            return $rem !== null && $rem >= 0 && $rem <= 50;
        })->count();

        $overdueCount = $allEquipments->filter(function($e) {
            $rem = $e->remain_reading;
            return $rem !== null && $rem < 0;
        })->count();

        $categories = Equipment::CATEGORIES;

        return view('equipment.index', compact(
            'equipments',
            'totalCount',
            'activeCount',
            'downCount',
            'dueCount',
            'overdueCount',
            'projects',
            'categories'
        ));
    }

    public function create()
    {
        $projects = $this->getUserProjects();
        $categories = Equipment::CATEGORIES;
        return view('equipment.create', compact('projects', 'categories'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $validated = $request->validate([
            'equipment_type' => 'required|string|max:100',
            'category' => 'required|string|max:100',
            'make' => 'nullable|string|max:100',
            'model' => 'nullable|string|max:100',
            'plate_serial_no' => 'required|string|max:100|unique:equipment,plate_serial_no',
            'serial_no' => 'nullable|string|max:100',
            'project_id' => 'nullable|exists:projects,id',
            'location_name' => 'nullable|string|max:150',
            'reading_unit' => 'required|in:hr,km',
            'current_reading' => 'required|numeric|min:0',
            'current_reading_date' => 'nullable|date',
            'last_service_reading' => 'nullable|numeric|min:0',
            'last_service_date' => 'nullable|date',
            'last_service_type' => 'nullable|string|max:50',
            'next_service_reading' => 'nullable|numeric|min:0',
            'next_service_type' => 'nullable|string|max:50',
            'status' => 'required|in:active,due_service,under_maintenance,breakdown,idle',
            'remarks' => 'nullable|string',
        ]);

        if (!empty($validated['project_id']) && !$user->isAdmin() && !$user->is_super_admin) {
            if (!in_array($validated['project_id'], $this->getUserProjectIds())) {
                abort(403, 'Unauthorized: You are not assigned to this project.');
            }
        }

        $validated['created_by'] = Auth::id();
        $validated['code'] = $validated['plate_serial_no'] ?: ('EQ-' . rand(1000, 9999));
        $validated['current_reading_date'] = $validated['current_reading_date'] ?? now()->toDateString();

        $equipment = Equipment::create($validated);

        return redirect()->route('equipment.show', $equipment)
            ->with('success', "Equipment '{$equipment->full_display_name}' created successfully.");
    }

    public function show(Equipment $equipment)
    {
        $user = Auth::user();
        if ($equipment->project_id && !$user->isAdmin() && !$user->is_super_admin && !in_array($equipment->project_id, $this->getUserProjectIds())) {
            abort(403, 'Unauthorized access to this equipment record.');
        }

        $equipment->load('project', 'creator');
        
        $pmItems = PmScheduleItem::with('schedule')
            ->where('equipment_id', $equipment->id)
            ->latest()
            ->take(10)
            ->get();

        $breakdowns = EquipmentBreakdownItem::with('report')
            ->where('equipment_id', $equipment->id)
            ->latest()
            ->take(10)
            ->get();

        $maintenanceLogs = EquipmentMaintenanceLog::where('equipment_id', $equipment->id)
            ->orWhere('equipment_plate', $equipment->plate_serial_no)
            ->latest('log_date')
            ->take(15)
            ->get();

        $totalSpent = EquipmentMaintenanceLog::where('equipment_id', $equipment->id)
            ->orWhere('equipment_plate', $equipment->plate_serial_no)
            ->sum('total_cost_with_vat');

        return view('equipment.show', compact('equipment', 'pmItems', 'breakdowns', 'maintenanceLogs', 'totalSpent'));
    }

    public function edit(Equipment $equipment)
    {
        $user = Auth::user();
        if ($equipment->project_id && !$user->isAdmin() && !$user->is_super_admin && !in_array($equipment->project_id, $this->getUserProjectIds())) {
            abort(403, 'Unauthorized access to this equipment record.');
        }

        $projects = $this->getUserProjects();
        $categories = Equipment::CATEGORIES;
        return view('equipment.edit', compact('equipment', 'projects', 'categories'));
    }

    public function update(Request $request, Equipment $equipment)
    {
        $user = Auth::user();
        if ($equipment->project_id && !$user->isAdmin() && !$user->is_super_admin && !in_array($equipment->project_id, $this->getUserProjectIds())) {
            abort(403, 'Unauthorized access to this equipment record.');
        }

        $validated = $request->validate([
            'equipment_type' => 'required|string|max:100',
            'category' => 'required|string|max:100',
            'make' => 'nullable|string|max:100',
            'model' => 'nullable|string|max:100',
            'plate_serial_no' => 'required|string|max:100|unique:equipment,plate_serial_no,' . $equipment->id,
            'serial_no' => 'nullable|string|max:100',
            'project_id' => 'nullable|exists:projects,id',
            'location_name' => 'nullable|string|max:150',
            'reading_unit' => 'required|in:hr,km',
            'current_reading' => 'required|numeric|min:0',
            'current_reading_date' => 'nullable|date',
            'last_service_reading' => 'nullable|numeric|min:0',
            'last_service_date' => 'nullable|date',
            'last_service_type' => 'nullable|string|max:50',
            'next_service_reading' => 'nullable|numeric|min:0',
            'next_service_type' => 'nullable|string|max:50',
            'status' => 'required|in:active,due_service,under_maintenance,breakdown,idle',
            'remarks' => 'nullable|string',
        ]);

        if (!empty($validated['project_id']) && !$user->isAdmin() && !$user->is_super_admin) {
            if (!in_array($validated['project_id'], $this->getUserProjectIds())) {
                abort(403, 'Unauthorized: You are not assigned to this project.');
            }
        }

        $equipment->update($validated);

        return redirect()->route('equipment.show', $equipment)
            ->with('success', "Equipment '{$equipment->full_display_name}' updated successfully.");
    }

    public function updateReading(Request $request, Equipment $equipment)
    {
        $user = Auth::user();
        if ($equipment->project_id && !$user->isAdmin() && !$user->is_super_admin && !in_array($equipment->project_id, $this->getUserProjectIds())) {
            abort(403, 'Unauthorized access to this equipment record.');
        }

        $validated = $request->validate([
            'current_reading' => 'required|numeric|min:' . (float)$equipment->current_reading,
            'current_reading_date' => 'required|date',
            'location_name' => 'nullable|string|max:150',
            'remarks' => 'nullable|string',
        ]);

        $equipment->update($validated);

        return back()->with('success', "Current meter reading updated to {$equipment->current_reading} {$equipment->reading_unit}.");
    }

    public function destroy(Equipment $equipment)
    {
        $user = Auth::user();
        if (!$user) {
            abort(401);
        }

        if ($equipment->project_id && !$user->isAdmin() && !$user->is_super_admin && !in_array($equipment->project_id, $this->getUserProjectIds())) {
            abort(403, 'Unauthorized access to this equipment record.');
        }

        $hasPermission = $user->isAdmin() || $user->is_super_admin || $user->hasPermission('equipment.delete');
        if (!$hasPermission) {
            return back()->with('error', 'Unauthorized: You do not have permission to delete equipment.');
        }

        $name = $equipment->full_display_name;
        $equipment->delete();
        return redirect()->route('equipment.index')->with('success', "Equipment '{$name}' removed from active fleet.");
    }
}
