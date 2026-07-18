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
        return $user->projects()
                    ->where('project_user.is_active', true)
                    ->pluck('projects.id')
                    ->toArray();
    }

    private function getUserProjects()
    {
        $user = Auth::user();
        return $user->isAdmin()
            ? Project::all()
            : Project::whereIn('id', $this->getUserProjectIds())->get();
    }

    // ------------------------------------------------------------------
    // INDEX
    // ------------------------------------------------------------------
    public function index(Request $request)
    {
        $projects = $this->getUserProjects();
        $query    = BoqItem::with(['project', 'costCategory', 'laborResources', 'materialResources', 'equipmentResources'])
                           ->whereIn('project_id', $this->getUserProjectIds());

        if ($request->filled('project_id')) $query->where('project_id', $request->project_id);
        if ($request->filled('status'))     $query->where('status', $request->status);
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('item_number', 'LIKE', "%{$s}%")
                  ->orWhere('description', 'LIKE', "%{$s}%");
            });
        }
        if ($request->filled('date_from')) $query->where('planned_start_date', '>=', $request->date_from);
        if ($request->filled('date_to'))   $query->where('planned_start_date', '<=', $request->date_to);

        $boqItems = $query->orderBy('item_number')->paginate(20)->appends($request->query());
        return view('boq-items.index', compact('boqItems', 'projects'));
    }

    // ------------------------------------------------------------------
    // CREATE / STORE
    // ------------------------------------------------------------------
    public function create(Request $request)
    {
        $projectId      = $request->get('project_id');
        $projects       = $this->getUserProjects();
        $costCategories = $projectId
            ? CostCategory::where('project_id', $projectId)->get()
            : CostCategory::all();
        return view('boq-items.create', compact('projects', 'costCategories', 'projectId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id'        => 'required|exists:projects,id',
            'cost_category_id'  => 'nullable|exists:cost_categories,id',
            'item_number'       => 'required|string|max:50',
            'description'       => 'required|string|max:1000',
            'unit'              => 'required|string|max:50',
            'quantity'          => 'required|numeric|min:0',
            'unit_rate'         => 'required|numeric|min:0',
            'duration_days'     => 'nullable|integer|min:0',
            'planned_start_date'=> 'nullable|date',
            'planned_end_date'  => 'nullable|date',
            'is_parent'         => 'boolean',
            'labor'             => 'nullable|array',
            'materials'         => 'nullable|array',
            'equipment'         => 'nullable|array',
        ]);

        if (! in_array($validated['project_id'], $this->getUserProjectIds())) {
            return back()->with('error', 'You are not assigned to this project.')->withInput();
        }

        $validated['revenue_amount'] = $validated['quantity'] * $validated['unit_rate'];

        DB::beginTransaction();
        try {
            $boqItem = BoqItem::create($validated);

            // Labor
            if ($request->has('labor')) {
                foreach ($request->labor as $l) {
                    if (! empty($l['trade_name'])) {
                        $boqItem->laborResources()->create([
                            'trade_name'        => $l['trade_name'],
                            'number_of_workers' => $l['number_of_workers'] ?? 0,
                            'total_hours'       => $l['total_hours'] ?? 0,
                            'wage_per_day'      => $l['wage_per_day'] ?? 0,
                            'amount'            => ($l['number_of_workers'] ?? 0) * ($l['total_hours'] ?? 0) * ($l['wage_per_day'] ?? 0) / 8,
                        ]);
                    }
                }
            }

            // Materials
            if ($request->has('materials')) {
                foreach ($request->materials as $m) {
                    if (! empty($m['description'])) {
                        $boqItem->materialResources()->create([
                            'description' => $m['description'],
                            'unit'        => $m['unit'] ?? '',
                            'quantity'    => $m['quantity'] ?? 0,
                            'unit_rate'   => $m['unit_rate'] ?? 0,
                            'amount'      => ($m['quantity'] ?? 0) * ($m['unit_rate'] ?? 0),
                        ]);
                    }
                }
            }

            // Equipment
            if ($request->has('equipment')) {
                foreach ($request->equipment as $e) {
                    if (! empty($e['description'])) {
                        $boqItem->equipmentResources()->create([
                            'description'     => $e['description'],
                            'duration_days'   => $e['duration_days'] ?? 0,
                            'number_of_units' => $e['number_of_units'] ?? 1,
                            'total_hours'     => $e['total_hours'] ?? 0,
                            'rate_per_hour'   => $e['rate_per_hour'] ?? 0,
                            'amount'          => ($e['total_hours'] ?? 0) * ($e['rate_per_hour'] ?? 0),
                        ]);
                    }
                }
            }

            DB::commit();
            return redirect()->route('boq-items.show', $boqItem)->with('success', 'BOQ Item created successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    // ------------------------------------------------------------------
    // SHOW
    // ------------------------------------------------------------------
    public function show(BoqItem $boqItem)
    {
        $boqItem->load([
            'project', 'costCategory', 'laborResources',
            'materialResources', 'equipmentResources',
            'ipcItems.ipc',
        ]);
        return view('boq-items.show', compact('boqItem'));
    }

    // ------------------------------------------------------------------
    // EDIT / UPDATE
    // ------------------------------------------------------------------
    public function edit(BoqItem $boqItem)
    {
        $projects       = Project::all();
        $costCategories = CostCategory::where('project_id', $boqItem->project_id)->get();
        $boqItem->load(['laborResources', 'materialResources', 'equipmentResources']);
        return view('boq-items.edit', compact('boqItem', 'projects', 'costCategories'));
    }

    public function update(Request $request, BoqItem $boqItem)
    {
        $validated = $request->validate([
            'project_id'   => 'required|exists:projects,id',
            'item_number'  => 'required|string|max:50',
            'description'  => 'required|string|max:1000',
            'unit'         => 'required|string|max:50',
            'quantity'     => 'required|numeric|min:0',
            'unit_rate'    => 'required|numeric|min:0',
            'duration_days'=> 'nullable|integer|min:0',
            'is_parent'    => 'boolean',
            'status'       => 'required|in:pending,in_progress,completed',
        ]);
        $validated['revenue_amount'] = $validated['quantity'] * $validated['unit_rate'];
        $boqItem->update($validated);
        return redirect()->route('boq-items.show', $boqItem)->with('success', 'BOQ Item updated.');
    }

    // ------------------------------------------------------------------
    // DELETE (locked if linked to IPCs)
    // ------------------------------------------------------------------
    public function destroy(BoqItem $boqItem)
    {
        $ipcCount = $boqItem->ipcItems()->count();

        if ($ipcCount > 0) {
            $ipcNumbers = $boqItem->ipcItems()
                ->with('ipc')
                ->get()
                ->pluck('ipc.ipc_number')
                ->unique()
                ->implode(', ');

            return back()->with('error',
                "❌ <strong>Cannot delete this BOQ item!</strong><br><br>" .
                "<strong>Reason:</strong> Linked to <strong>{$ipcCount} Payment Certificate(s)</strong>: {$ipcNumbers}<br><br>" .
                "<strong>Action Required:</strong><br>" .
                "1. First delete or unlink the Payment Certificates (IPCs)<br>" .
                "2. Then delete this BOQ item<br><br>" .
                "<a href='" . route('ipcs.index', ['project_id' => $boqItem->project_id]) . "' class='btn btn-warning btn-sm' target='_blank'>" .
                "<i class='fas fa-file-invoice me-1'></i> View Linked IPCs</a>"
            );
        }

        $boqItem->delete();
        return redirect()->route('boq-items.index')->with('success', 'BOQ Item deleted successfully.');
    }
}
