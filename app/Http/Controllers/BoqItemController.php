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
    private function getUserProjectIds(): array
    {
        $user = Auth::user();
        if (! $user) return [];
        if ($user->isAdmin()) return Project::pluck('id')->toArray();
        return $user->projects()->where('project_user.is_active', true)->pluck('projects.id')->toArray();
    }

    public function index(Request $request)
    {
        $projects = Auth::user()->isAdmin() ? Project::all() : Project::whereIn('id', $this->getUserProjectIds())->get();
        $query = BoqItem::with(['project','costCategory'])->whereIn('project_id', $this->getUserProjectIds());
        if ($request->filled('project_id')) $query->where('project_id', $request->project_id);
        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('search')) { $s = $request->search; $query->where(function($q) use ($s) { $q->where('item_number','LIKE',"%{$s}%")->orWhere('description','LIKE',"%{$s}%"); }); }
        $boqItems = $query->orderBy('item_number')->paginate(20)->appends($request->query());
        return view('boq-items.index', compact('boqItems','projects'));
    }

    public function create(Request $request)
    {
        $projectId = $request->get('project_id');
        $projects = Auth::user()->isAdmin() ? Project::all() : Project::whereIn('id', $this->getUserProjectIds())->get();
        $costCategories = $projectId ? CostCategory::where('project_id', $projectId)->get() : CostCategory::all();
        return view('boq-items.create', compact('projects', 'costCategories', 'projectId'));
    }

    public function store(Request $request) { /* same as before */ }

    public function quickStore(Request $request)
    {
        $validated = $request->validate([
            "project_id" => "required|exists:projects,id",
            "item_number" => "required|string",
            "description" => "required|string",
            "unit" => "required|string",
            "quantity" => "required|numeric|min:0",
            "unit_rate" => "required|numeric|min:0",
        ]);
        
        $item = BoqItem::create($validated);
        
        return response()->json([
            "success" => true,
            "item" => $item
        ]);
    }

    public function show(BoqItem $boqItem)
    {
        $boqItem->load(['project','costCategory','laborResources','materialResources','equipmentResources','ipcItems.ipc']);
        return view('boq-items.show', compact('boqItem'));
    }

    public function edit(BoqItem $boqItem)
    {
        $projects = Project::all();
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
            'labor' => 'nullable|array',
            'materials' => 'nullable|array',
            'equipment' => 'nullable|array',
        ]);
        
        $validated['revenue_amount'] = $validated['quantity'] * $validated['unit_rate'];
        
        DB::beginTransaction();
        try {
            $boqItem->update($validated);

            // UPDATE LABOR RESOURCES
            if ($request->has('labor')) {
                $boqItem->laborResources()->delete();
                foreach ($request->labor as $l) {
                    if (!empty($l['trade_name'])) {
                        $boqItem->laborResources()->create([
                            'trade_name' => $l['trade_name'],
                            'number_of_workers' => $l['number_of_workers'] ?? 0,
                            'total_hours' => $l['total_hours'] ?? 0,
                            'wage_per_day' => $l['wage_per_day'] ?? 0,
                            'amount' => ($l['number_of_workers']??0) * ($l['total_hours']??0) * ($l['wage_per_day']??0) / 8,
                        ]);
                    }
                }
            }

            // UPDATE MATERIAL RESOURCES
            if ($request->has('materials')) {
                $boqItem->materialResources()->delete();
                foreach ($request->materials as $m) {
                    if (!empty($m['description'])) {
                        $boqItem->materialResources()->create([
                            'description' => $m['description'],
                            'unit' => $m['unit'] ?? '',
                            'quantity' => $m['quantity'] ?? 0,
                            'unit_rate' => $m['unit_rate'] ?? 0,
                            'amount' => ($m['quantity']??0) * ($m['unit_rate']??0),
                        ]);
                    }
                }
            }

            // UPDATE EQUIPMENT RESOURCES
            if ($request->has('equipment')) {
                $boqItem->equipmentResources()->delete();
                foreach ($request->equipment as $e) {
                    if (!empty($e['description'])) {
                        $boqItem->equipmentResources()->create([
                            'description' => $e['description'],
                            'duration_days' => $e['duration_days'] ?? 0,
                            'number_of_units' => $e['number_of_units'] ?? 1,
                            'total_hours' => $e['total_hours'] ?? 0,
                            'rate_per_hour' => $e['rate_per_hour'] ?? 0,
                            'amount' => ($e['total_hours']??0) * ($e['rate_per_hour']??0),
                        ]);
                    }
                }
            }

            DB::commit();
            return redirect()->route('boq-items.show', $boqItem)->with('success', 'BOQ Item updated with resources.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(BoqItem $boqItem) { /* same */ }
}
