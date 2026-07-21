<?php
namespace App\Http\Controllers;

use App\Models\QuantityTakeoff;
use App\Models\Project;
use App\Models\BoqItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuantityTakeoffController extends Controller
{
    private function getUserProjectIds(): array
    {
        $user = Auth::user();
        if (!$user) return [];
        if ($user->isAdmin()) return Project::pluck('id')->toArray();
        return $user->projects()->where('project_user.is_active', true)->pluck('projects.id')->toArray();
    }

    private function getUserProjects()
    {
        $user = Auth::user();
        return $user->isAdmin() ? Project::all() : Project::whereIn('id', $this->getUserProjectIds())->get();
    }

    public function index(Request $request)
    {
        $userProjectIds = $this->getUserProjectIds();
        $projects = $this->getUserProjects();
        $projectId = $request->get('project_id');
        
        $query = QuantityTakeoff::with(['project', 'boqItem'])->whereIn('project_id', $userProjectIds);
        if ($projectId) $query->where('project_id', $projectId);
        if ($request->filled('status')) $query->where('status', $request->status);
        
        $takeoffs = $query->orderBy('measurement_date', 'desc')->paginate(20)->appends($request->query());
        $totalMeasured = (clone $query)->sum('total_area_volume');
        
        return view('quantity-takeoffs.index', compact('takeoffs', 'projects', 'projectId', 'totalMeasured'));
    }

    public function create(Request $request)
    {
        $projectId = $request->get('project_id');
        $projects = $this->getUserProjects();
        $boqItems = $projectId ? BoqItem::where('project_id', $projectId)->where('is_parent', false)->get() : collect();
        return view('quantity-takeoffs.create', compact('projects', 'boqItems', 'projectId'));
    }

    /**
     * Store single measurement
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'boq_item_id' => 'required|exists:boq_items,id',
            'structure_type' => 'nullable|string|max:255',
            'element_id' => 'nullable|string|max:100',
            'location_axis' => 'nullable|string|max:500',
            'quantity_count' => 'required|integer|min:1',
            'length' => 'nullable|numeric|min:0',
            'width' => 'nullable|numeric|min:0',
            'height_depth' => 'nullable|numeric|min:0',
            'measurement_date' => 'required|date',
            'measured_by' => 'nullable|string|max:255',
            'remarks' => 'nullable|string',
        ]);

        $takeoff = QuantityTakeoff::create($validated);
        return redirect()->route('quantity-takeoffs.show', $takeoff)->with('success', 'Measurement saved.');
    }

    /**
     * Store MULTIPLE measurements (left + right arrays)
     */
    public function storeMultiple(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'boq_item_id' => 'required|exists:boq_items,id',
            'structure_type' => 'nullable|string|max:255',
            'location_axis' => 'nullable|string|max:500',
            'measurement_date' => 'required|date',
            'measured_by' => 'nullable|string|max:255',
            // Left side measurements (array)
            'left' => 'nullable|array',
            'left.*.element_id' => 'required_with:left|string|max:100',
            'left.*.quantity_count' => 'required_with:left|integer|min:1',
            'left.*.length' => 'required_with:left|numeric|min:0',
            'left.*.width' => 'nullable|numeric|min:0',
            'left.*.height_depth' => 'nullable|numeric|min:0',
            'left.*.remarks' => 'nullable|string',
            // Right side measurements (array)
            'right' => 'nullable|array',
            'right.*.element_id' => 'required_with:right|string|max:100',
            'right.*.quantity_count' => 'required_with:right|integer|min:1',
            'right.*.length' => 'required_with:right|numeric|min:0',
            'right.*.width' => 'nullable|numeric|min:0',
            'right.*.height_depth' => 'nullable|numeric|min:0',
            'right.*.remarks' => 'nullable|string',
        ]);

        $count = 0;

        // Save LEFT measurements
        if ($request->has('left')) {
            foreach ($request->left as $item) {
                if (!empty($item['element_id'])) {
                    QuantityTakeoff::create([
                        'project_id' => $validated['project_id'],
                        'boq_item_id' => $validated['boq_item_id'],
                        'structure_type' => $validated['structure_type'] ?? null,
                        'location_axis' => $validated['location_axis'] ?? null,
                        'element_id' => $item['element_id'],
                        'quantity_count' => $item['quantity_count'],
                        'length' => $item['length'],
                        'width' => $item['width'] ?? 1,
                        'height_depth' => $item['height_depth'] ?? 1,
                        'measurement_date' => $validated['measurement_date'],
                        'measured_by' => $validated['measured_by'] ?? Auth::user()->name,
                        'remarks' => $item['remarks'] ?? null,
                    ]);
                    $count++;
                }
            }
        }

        // Save RIGHT measurements
        if ($request->has('right')) {
            foreach ($request->right as $item) {
                if (!empty($item['element_id'])) {
                    QuantityTakeoff::create([
                        'project_id' => $validated['project_id'],
                        'boq_item_id' => $validated['boq_item_id'],
                        'structure_type' => $validated['structure_type'] ?? null,
                        'location_axis' => $validated['location_axis'] ?? null,
                        'element_id' => $item['element_id'],
                        'quantity_count' => $item['quantity_count'],
                        'length' => $item['length'],
                        'width' => $item['width'] ?? 1,
                        'height_depth' => $item['height_depth'] ?? 1,
                        'measurement_date' => $validated['measurement_date'],
                        'measured_by' => $validated['measured_by'] ?? Auth::user()->name,
                        'remarks' => $item['remarks'] ?? null,
                    ]);
                    $count++;
                }
            }
        }

        return redirect()->route('quantity-takeoffs.index', ['project_id' => $validated['project_id']])
            ->with('success', "✅ {$count} measurements saved successfully!");
    }

    public function show(QuantityTakeoff $quantityTakeoff)
    {
        $quantityTakeoff->load(['project', 'boqItem']);
        return view('quantity-takeoffs.show', compact('quantityTakeoff'));
    }

    public function edit(QuantityTakeoff $quantityTakeoff)
    {
        if ($quantityTakeoff->status === 'approved' && !Auth::user()->isAdmin()) {
            return back()->with('error', 'Cannot edit approved measurement.');
        }
        $projects = Project::all();
        $boqItems = BoqItem::where('project_id', $quantityTakeoff->project_id)->where('is_parent', false)->get();
        return view('quantity-takeoffs.edit', compact('quantityTakeoff', 'projects', 'boqItems'));
    }

    public function update(Request $request, QuantityTakeoff $quantityTakeoff)
    {
        if ($quantityTakeoff->status === 'approved' && !Auth::user()->isAdmin()) {
            return back()->with('error', 'Cannot update approved measurement.');
        }
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'boq_item_id' => 'required|exists:boq_items,id',
            'quantity_count' => 'required|integer|min:1',
            'length' => 'nullable|numeric|min:0',
            'width' => 'nullable|numeric|min:0',
            'height_depth' => 'nullable|numeric|min:0',
            'measurement_date' => 'required|date',
            'status' => 'required|in:draft,verified,approved',
            'remarks' => 'nullable|string',
        ]);
        $quantityTakeoff->update($validated);
        return redirect()->route('quantity-takeoffs.index')->with('success', 'Updated.');
    }

    public function destroy(QuantityTakeoff $quantityTakeoff)
    {
        if ($quantityTakeoff->status === 'approved' && !Auth::user()->isAdmin()) {
            return back()->with('error', 'Cannot delete approved measurement.');
        }
        $quantityTakeoff->delete();
        return redirect()->route('quantity-takeoffs.index')->with('success', 'Deleted.');
    }

    public function verify(QuantityTakeoff $quantityTakeoff)
    {
        if (!Auth::user()->isAdmin() && !\App\Models\WorkflowPermission::canUserAct(Auth::id(), 'verify_takeoff')) {
            return back()->with('error', 'No permission to verify.');
        }
        $quantityTakeoff->update(['status' => 'verified', 'verified_by' => Auth::user()->name]);
        return back()->with('success', 'Verified.');
    }

    public function approve(QuantityTakeoff $quantityTakeoff)
    {
        if (!Auth::user()->isAdmin() && !\App\Models\WorkflowPermission::canUserAct(Auth::id(), 'approve_takeoff')) {
            return back()->with('error', 'No permission to approve.');
        }
        $quantityTakeoff->update(['status' => 'approved']);
        return back()->with('success', 'Approved.');
    }

    public function revertToDraft(QuantityTakeoff $quantityTakeoff)
    {
        if (!Auth::user()->isAdmin()) return back()->with('error', 'Only admin can revert.');
        $quantityTakeoff->update(['status' => 'draft', 'verified_by' => null]);
        return back()->with('success', 'Reverted to draft.');
    }

    public function print(QuantityTakeoff $quantityTakeoff)
    {
        return view('quantity-takeoffs.print', compact('quantityTakeoff'));
    }
}
