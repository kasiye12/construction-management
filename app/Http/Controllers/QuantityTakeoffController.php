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

    // ------------------------------------------------------------------
    // INDEX
    // ------------------------------------------------------------------
    public function index(Request $request)
    {
        $userProjectIds = $this->getUserProjectIds();
        $projects = $this->getUserProjects();
        $projectId = $request->get('project_id');
        
        $query = QuantityTakeoff::with(['project', 'boqItem'])
            ->whereIn('project_id', $userProjectIds);
        
        if ($projectId) $query->where('project_id', $projectId);
        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('date_from')) $query->where('measurement_date', '>=', $request->date_from);
        if ($request->filled('date_to')) $query->where('measurement_date', '<=', $request->date_to);
        
        $takeoffs = $query->orderBy('measurement_date', 'desc')->paginate(20)->appends($request->query());
        $totalMeasured = (clone $query)->sum('total_area_volume');
        
        return view('quantity-takeoffs.index', compact('takeoffs', 'projects', 'projectId', 'totalMeasured'));
    }

    // ------------------------------------------------------------------
    // CREATE / STORE
    // ------------------------------------------------------------------
    public function create(Request $request)
    {
        $projectId = $request->get('project_id');
        $projects = $this->getUserProjects();
        $boqItems = $projectId ? BoqItem::where('project_id', $projectId)->where('is_parent', false)->get() : collect();
        return view('quantity-takeoffs.create', compact('projects', 'boqItems', 'projectId'));
    }

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
        return redirect()->route('quantity-takeoffs.show', $takeoff)->with('success', 'Measurement recorded successfully.');
    }

    // ------------------------------------------------------------------
    // SHOW
    // ------------------------------------------------------------------
    public function show(QuantityTakeoff $quantityTakeoff)
    {
        $quantityTakeoff->load(['project', 'boqItem']);
        $canVerify = $this->canVerify();
        $canApprove = $this->canApprove();
        return view('quantity-takeoffs.show', compact('quantityTakeoff', 'canVerify', 'canApprove'));
    }

    // ------------------------------------------------------------------
    // EDIT / UPDATE (locked after verification)
    // ------------------------------------------------------------------
    public function edit(QuantityTakeoff $quantityTakeoff)
    {
        if ($quantityTakeoff->status === 'approved' && !Auth::user()->isAdmin()) {
            return back()->with('error', '❌ Cannot edit approved measurement. Only admin can edit.');
        }
        $projects = Project::all();
        $boqItems = BoqItem::where('project_id', $quantityTakeoff->project_id)->where('is_parent', false)->get();
        return view('quantity-takeoffs.edit', compact('quantityTakeoff', 'projects', 'boqItems'));
    }

    public function update(Request $request, QuantityTakeoff $quantityTakeoff)
    {
        if ($quantityTakeoff->status === 'approved' && !Auth::user()->isAdmin()) {
            return back()->with('error', '❌ Cannot update approved measurement.');
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
        return redirect()->route('quantity-takeoffs.index')->with('success', 'Measurement updated.');
    }

    // ------------------------------------------------------------------
    // DELETE (locked after approval)
    // ------------------------------------------------------------------
    public function destroy(QuantityTakeoff $quantityTakeoff)
    {
        if ($quantityTakeoff->status === 'approved' && !Auth::user()->isAdmin()) {
            return back()->with('error', '❌ Cannot delete approved measurement. Only admin can delete approved records.');
        }
        
        $quantityTakeoff->delete();
        return redirect()->route('quantity-takeoffs.index')->with('success', 'Measurement deleted.');
    }

    // ==================================================================
    // WORKFLOW ACTIONS
    // ==================================================================
    
    private function canVerify(): bool
    {
        $user = Auth::user();
        if ($user->isAdmin()) return true;
        return \App\Models\WorkflowPermission::canUserAct($user->id, 'verify_takeoff');
    }

    private function canApprove(): bool
    {
        $user = Auth::user();
        if ($user->isAdmin()) return true;
        return \App\Models\WorkflowPermission::canUserAct($user->id, 'approve_takeoff');
    }

    /**
     * Verify take-off measurement
     */
    public function verify(QuantityTakeoff $quantityTakeoff)
    {
        if (!$this->canVerify()) {
            return back()->with('error', '❌ You do not have permission to verify measurements.');
        }
        
        if ($quantityTakeoff->status !== 'draft') {
            return back()->with('error', '❌ Only draft measurements can be verified.');
        }
        
        $quantityTakeoff->update([
            'status' => 'verified',
            'verified_by' => Auth::user()->name,
        ]);
        
        return back()->with('success', '✅ Measurement verified by ' . Auth::user()->name);
    }

    /**
     * Approve take-off measurement
     */
    public function approve(QuantityTakeoff $quantityTakeoff)
    {
        if (!$this->canApprove()) {
            return back()->with('error', '❌ You do not have permission to approve measurements.');
        }
        
        if ($quantityTakeoff->status !== 'verified') {
            return back()->with('error', '❌ Only verified measurements can be approved.');
        }
        
        $quantityTakeoff->update([
            'status' => 'approved',
        ]);
        
        return back()->with('success', '✔️ Measurement approved successfully.');
    }

    /**
     * Revert to draft
     */
    public function revertToDraft(QuantityTakeoff $quantityTakeoff)
    {
        if (!Auth::user()->isAdmin()) {
            return back()->with('error', '❌ Only admin can revert measurements.');
        }
        
        $quantityTakeoff->update([
            'status' => 'draft',
            'verified_by' => null,
        ]);
        
        return back()->with('success', 'Measurement reverted to draft.');
    }
}
