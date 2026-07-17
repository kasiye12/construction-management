<?php
namespace App\Http\Controllers;

use App\Models\BoqItem;
use App\Models\Project;
use App\Models\CostCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class BoqItemController extends Controller
{
    public function index(Request $request)
    {
        $projectId = $request->get('project_id');
        $user = Auth::user();
        
        // Filter by user's projects if not admin
        if ($user && !$user->isAdmin()) {
            $userProjectIds = $user->projects()->where('project_user.is_active', true)->pluck('projects.id')->toArray();
            $projects = Project::whereIn('id', $userProjectIds)->get();
        } else {
            $projects = Project::all();
        }
        
        $query = BoqItem::with(['project','costCategory','laborResources','materialResources','equipmentResources']);
        
        if ($projectId) $query->where('project_id', $projectId);
        
        // Non-admin only see their projects' BOQ
        if ($user && !$user->isAdmin()) {
            $query->whereIn('project_id', $userProjectIds);
        }
        
        $boqItems = $query->orderBy('item_number')->paginate(20);
        return view('boq-items.index', compact('boqItems','projects','projectId'));
    }

    public function create(Request $request)
    {
        $projectId = $request->get('project_id');
        $user = Auth::user();
        
        if ($user && $user->isAdmin()) {
            $projects = Project::all();
        } else {
            $projects = $user->projects()->where('project_user.is_active', true)->get();
        }
        
        $costCategories = $projectId ? CostCategory::where('project_id', $projectId)->get() : CostCategory::all();
        return view('boq-items.create', compact('projects', 'costCategories', 'projectId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'cost_category_id' => 'nullable|exists:cost_categories,id',
            'item_number' => 'required|string|max:50',
            'description' => 'required|string|max:1000',
            'unit' => 'required|string|max:50',
            'quantity' => 'required|numeric|min:0',
            'unit_rate' => 'required|numeric|min:0',
            'duration_days' => 'nullable|integer|min:0',
            'planned_start_date' => 'nullable|date',
            'planned_end_date' => 'nullable|date',
            'is_parent' => 'boolean',
            'labor' => 'nullable|array',
            'materials' => 'nullable|array',
            'equipment' => 'nullable|array',
        ]);

        $validated['revenue_amount'] = $validated['quantity'] * $validated['unit_rate'];

        DB::beginTransaction();
        try {
            $boqItem = BoqItem::create($validated);

            // Save Labor
            if ($request->has('labor')) {
                foreach ($request->labor as $labor) {
                    if (!empty($labor['trade_name'])) {
                        $boqItem->laborResources()->create([
                            'trade_name' => $labor['trade_name'],
                            'number_of_workers' => $labor['number_of_workers'] ?? 0,
                            'total_hours' => $labor['total_hours'] ?? 0,
                            'wage_per_day' => $labor['wage_per_day'] ?? 0,
                            'amount' => ($labor['number_of_workers'] ?? 0) * ($labor['total_hours'] ?? 0) * ($labor['wage_per_day'] ?? 0) / 8,
                        ]);
                    }
                }
            }

            // Save Materials
            if ($request->has('materials')) {
                foreach ($request->materials as $material) {
                    if (!empty($material['description'])) {
                        $boqItem->materialResources()->create([
                            'description' => $material['description'],
                            'unit' => $material['unit'] ?? '',
                            'quantity' => $material['quantity'] ?? 0,
                            'unit_rate' => $material['unit_rate'] ?? 0,
                            'amount' => ($material['quantity'] ?? 0) * ($material['unit_rate'] ?? 0),
                        ]);
                    }
                }
            }

            // Save Equipment
            if ($request->has('equipment')) {
                foreach ($request->equipment as $equipment) {
                    if (!empty($equipment['description'])) {
                        $boqItem->equipmentResources()->create([
                            'description' => $equipment['description'],
                            'duration_days' => $equipment['duration_days'] ?? 0,
                            'number_of_units' => $equipment['number_of_units'] ?? 1,
                            'total_hours' => $equipment['total_hours'] ?? 0,
                            'rate_per_hour' => $equipment['rate_per_hour'] ?? 0,
                            'amount' => ($equipment['total_hours'] ?? 0) * ($equipment['rate_per_hour'] ?? 0),
                        ]);
                    }
                }
            }

            DB::commit();
            return redirect()->route('boq-items.show', $boqItem)->with('success', 'BOQ Item created.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function show(BoqItem $boqItem)
    {
        $boqItem->load(['project','costCategory','parent','children','laborResources','materialResources','equipmentResources','ipcItems.ipc']);
        return view('boq-items.show', compact('boqItem'));
    }

    public function edit(BoqItem $boqItem)
    {
        $user = Auth::user();
        if ($user->isAdmin()) {
            $projects = Project::all();
        } else {
            $projects = $user->projects()->where('project_user.is_active', true)->get();
        }
        $costCategories = CostCategory::where('project_id', $boqItem->project_id)->get();
        $boqItem->load(['laborResources','materialResources','equipmentResources']);
        return view('boq-items.edit', compact('boqItem','projects','costCategories'));
    }

    public function update(Request $request, BoqItem $boqItem)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'cost_category_id' => 'nullable|exists:cost_categories,id',
            'item_number' => 'required|string|max:50',
            'description' => 'required|string|max:1000',
            'unit' => 'required|string|max:50',
            'quantity' => 'required|numeric|min:0',
            'unit_rate' => 'required|numeric|min:0',
            'duration_days' => 'nullable|integer|min:0',
            'is_parent' => 'boolean',
            'status' => 'required|in:pending,in_progress,completed',
        ]);
        $validated['revenue_amount'] = $validated['quantity'] * $validated['unit_rate'];
        
        DB::beginTransaction();
        try {
            $boqItem->update($validated);

            if ($request->has('labor')) {
                $boqItem->laborResources()->delete();
                foreach ($request->labor as $labor) {
                    if (!empty($labor['trade_name'])) {
                        $boqItem->laborResources()->create([
                            'trade_name' => $labor['trade_name'],
                            'number_of_workers' => $labor['number_of_workers'] ?? 0,
                            'total_hours' => $labor['total_hours'] ?? 0,
                            'wage_per_day' => $labor['wage_per_day'] ?? 0,
                            'amount' => ($labor['number_of_workers'] ?? 0) * ($labor['total_hours'] ?? 0) * ($labor['wage_per_day'] ?? 0) / 8,
                        ]);
                    }
                }
            }

            if ($request->has('materials')) {
                $boqItem->materialResources()->delete();
                foreach ($request->materials as $material) {
                    if (!empty($material['description'])) {
                        $boqItem->materialResources()->create([
                            'description' => $material['description'],
                            'unit' => $material['unit'] ?? '',
                            'quantity' => $material['quantity'] ?? 0,
                            'unit_rate' => $material['unit_rate'] ?? 0,
                            'amount' => ($material['quantity'] ?? 0) * ($material['unit_rate'] ?? 0),
                        ]);
                    }
                }
            }

            if ($request->has('equipment')) {
                $boqItem->equipmentResources()->delete();
                foreach ($request->equipment as $equipment) {
                    if (!empty($equipment['description'])) {
                        $boqItem->equipmentResources()->create([
                            'description' => $equipment['description'],
                            'duration_days' => $equipment['duration_days'] ?? 0,
                            'number_of_units' => $equipment['number_of_units'] ?? 1,
                            'total_hours' => $equipment['total_hours'] ?? 0,
                            'rate_per_hour' => $equipment['rate_per_hour'] ?? 0,
                            'amount' => ($equipment['total_hours'] ?? 0) * ($equipment['rate_per_hour'] ?? 0),
                        ]);
                    }
                }
            }

            DB::commit();
            return redirect()->route('boq-items.show', $boqItem)->with('success', 'BOQ Item updated.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', $e->getMessage());
        }
    }

    public function destroy(BoqItem $boqItem) { $boqItem->delete(); return redirect()->route('boq-items.index')->with('success', 'BOQ Item deleted.'); }
}
