<?php

namespace App\Http\Controllers;

use App\Models\TakeoffSheet;
use App\Models\Project;
use App\Models\WorkflowPermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TakeoffSheetController extends Controller
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

    /**
     * Can edit? Only Draft + Creator (or Admin)
     */
    private function canModify(TakeoffSheet $sheet): bool
    {
        $user = Auth::user();
        if ($user->isAdmin()) return true;
        if ($sheet->status === 'draft') {
            if ($sheet->created_by === $user->id) return true;
            if ($sheet->measured_by === $user->name) return true;
        }
        return false;
    }

    private function canDelete(TakeoffSheet $sheet): bool
    {
        return $this->canModify($sheet);
    }

    /**
     * Can revert to draft?
     * Verified -> Draft: Verifier or Admin
     * Approved -> Draft: ONLY Admin (not verifier, not approver)
     */
    private function canRevertToDraft(TakeoffSheet $sheet): bool
    {
        $user = Auth::user();
        
        // Admin can revert verified or approved to draft
        if ($user->isAdmin()) return in_array($sheet->status, ['verified', 'approved']);
        
        // Verifier can revert verified back to draft
        if ($sheet->status === 'verified' && WorkflowPermission::canUserAct($user->id, 'verify_takeoff_sheet')) {
            return true;
        }
        
        // Approved: ONLY Admin can revert (not verifier, not approver)
        return false;
    }

    /**
     * Can revert to verified?
     * Approved -> Verified: ONLY Admin (not approver)
     */
    private function canRevertToVerified(TakeoffSheet $sheet): bool
    {
        $user = Auth::user();
        
        // ONLY Admin can revert approved to verified
        if ($user->isAdmin() && $sheet->status === 'approved') return true;
        
        return false;
    }

    public function index(Request $request)
    {
        $userProjectIds = $this->getUserProjectIds();
        $projects = $this->getUserProjects();
        $projectId = $request->get('project_id');
        
        $query = TakeoffSheet::with(['project', 'items'])->whereIn('project_id', $userProjectIds);
        if ($projectId) $query->where('project_id', $projectId);
        if ($request->filled('status')) $query->where('status', $request->status);
        
        $sheets = $query->orderBy('created_at', 'desc')->paginate(20)->appends($request->query());
        
        return view('takeoff-sheets.index', compact('sheets', 'projects', 'projectId'));
    }

    public function create(Request $request)
    {
        $projectId = $request->get('project_id');
        $projects = $this->getUserProjects();
        $boqItems = collect();
        
        if ($projectId) {
            $boqItems = \App\Models\BoqItem::where('project_id', $projectId)
                ->where('is_parent', false)->get();
        }
        
        return view('takeoff-sheets.create', compact('projects', 'boqItems', 'projectId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'sheet_number' => 'required|string|max:255',
            'page_no' => 'nullable|integer|min:1',
            'division' => 'nullable|string|max:255',
            'measurement_date' => 'required|date',
            'measured_by' => 'nullable|string|max:255',
            'items' => 'nullable|array',
            'items.*.item_number' => 'required|string',
            'items.*.boq_item_id' => 'nullable|exists:boq_items,id',
            'items.*.description' => 'nullable|string',
            'items.*.left_desc' => 'nullable|string',
            'items.*.right_desc' => 'nullable|string',
        ]);

        $validated['measured_by'] = $validated['measured_by'] ?? Auth::user()->name;
        $validated['created_by'] = Auth::id();
        $validated['status'] = 'draft';
        $sheet = TakeoffSheet::create($validated);
        $this->saveItems($sheet, $request->items ?? []);

        return redirect()->route('takeoff-sheets.show', $sheet)
            ->with('success', 'Takeoff sheet created successfully!');
    }

    public function show(TakeoffSheet $takeoffSheet)
    {
        $sheet = $takeoffSheet;
        $sheet->load(['project', 'items.descriptions.measurements']);
        
        $user = Auth::user();
        $canVerify = $user->isAdmin() || WorkflowPermission::canUserAct($user->id, 'verify_takeoff_sheet');
        $canApprove = $user->isAdmin() || WorkflowPermission::canUserAct($user->id, 'approve_takeoff_sheet');
        $canEdit = $this->canModify($sheet);
        $canDelete = $this->canDelete($sheet);
        $canRevertToDraft = $this->canRevertToDraft($sheet);
        $canRevertToVerified = $this->canRevertToVerified($sheet);
        
        return view('takeoff-sheets.show', compact(
            'sheet', 'canVerify', 'canApprove', 'canEdit', 'canDelete',
            'canRevertToDraft', 'canRevertToVerified'
        ));
    }

    public function edit(Request $request, TakeoffSheet $takeoffSheet)
    {
        if (!$this->canModify($takeoffSheet)) {
            return back()->with('error', 'Cannot edit. Sheet must be Draft and you must be the creator.');
        }
        
        $sheet = $takeoffSheet;
        $sheet->load(['project', 'items.descriptions.measurements']);
        $projectId = $request->get('project_id', $sheet->project_id);
        $projects = Project::all();
        $boqItems = \App\Models\BoqItem::where('project_id', $projectId)
            ->where('is_parent', false)->get();
        
        return view('takeoff-sheets.edit', compact('sheet', 'projects', 'boqItems', 'projectId'));
    }

    public function update(Request $request, TakeoffSheet $takeoffSheet)
    {
        if (!$this->canModify($takeoffSheet)) {
            return back()->with('error', 'Cannot update. Sheet must be Draft and you must be the creator.');
        }
        
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'sheet_number' => 'required|string|max:255',
            'page_no' => 'nullable|integer|min:1',
            'division' => 'nullable|string|max:255',
            'measurement_date' => 'required|date',
            'measured_by' => 'nullable|string|max:255',
            'remarks' => 'nullable|string',
            'items' => 'nullable|array',
            'items.*.item_number' => 'required|string',
            'items.*.boq_item_id' => 'nullable|exists:boq_items,id',
            'items.*.description' => 'nullable|string',
            'items.*.left_desc' => 'nullable|string',
            'items.*.right_desc' => 'nullable|string',
        ]);

        $validated['status'] = 'draft';
        $validated['verified_by'] = null;
        $validated['approved_by'] = null;
        
        $takeoffSheet->update($validated);
        $takeoffSheet->items()->delete();
        $this->saveItems($takeoffSheet, $request->items ?? []);

        return redirect()->route('takeoff-sheets.show', $takeoffSheet)
            ->with('success', 'Updated. Status reset to Draft.');
    }

    public function destroy(TakeoffSheet $takeoffSheet)
    {
        if (!$this->canDelete($takeoffSheet)) {
            return back()->with('error', 'Cannot delete.');
        }
        $takeoffSheet->delete();
        return redirect()->route('takeoff-sheets.index')->with('success', 'Deleted.');
    }

    public function print(TakeoffSheet $takeoffSheet)
    {
        $sheet = $takeoffSheet;
        $sheet->load(['project', 'items.descriptions.measurements']);
        return view('takeoff-sheets.print', compact('sheet'));
    }

    public function verify(TakeoffSheet $takeoffSheet)
    {
        if (!Auth::user()->isAdmin() && !WorkflowPermission::canUserAct(Auth::id(), 'verify_takeoff_sheet')) {
            return back()->with('error', 'No permission to verify.');
        }
        $takeoffSheet->update(['status' => 'verified', 'verified_by' => Auth::user()->name]);
        return back()->with('success', 'Sheet verified!');
    }

    public function approve(TakeoffSheet $takeoffSheet)
    {
        if (!Auth::user()->isAdmin() && !WorkflowPermission::canUserAct(Auth::id(), 'approve_takeoff_sheet')) {
            return back()->with('error', 'No permission to approve.');
        }
        $takeoffSheet->update(['status' => 'approved', 'approved_by' => Auth::user()->name]);
        return back()->with('success', 'Sheet approved!');
    }

    /**
     * Revert Approved -> Verified (ONLY Admin)
     */
    public function revertToVerified(TakeoffSheet $takeoffSheet)
    {
        if (!Auth::user()->isAdmin()) {
            return back()->with('error', 'Only system admin can revert approved documents.');
        }
        $takeoffSheet->update(['status' => 'verified', 'approved_by' => null]);
        return back()->with('success', 'Reverted to Verified (Admin action).');
    }

    /**
     * Revert to Draft
     * Verified -> Draft: Verifier or Admin
     * Approved -> Draft: ONLY Admin
     */
    public function revertToDraft(TakeoffSheet $takeoffSheet)
    {
        $user = Auth::user();
        
        // Approved -> Draft: ONLY Admin
        if ($takeoffSheet->status === 'approved' && !$user->isAdmin()) {
            return back()->with('error', 'Only system admin can revert approved documents.');
        }
        
        // Verified -> Draft: Verifier or Admin
        if ($takeoffSheet->status === 'verified') {
            if (!$user->isAdmin() && !WorkflowPermission::canUserAct($user->id, 'verify_takeoff_sheet')) {
                return back()->with('error', 'No permission to revert.');
            }
        }
        
        $takeoffSheet->update(['status' => 'draft', 'verified_by' => null, 'approved_by' => null]);
        return back()->with('success', 'Reverted to Draft. Creator can now edit.');
    }

    private function saveItems(TakeoffSheet $sheet, array $items)
    {
        foreach ($items as $index => $itemData) {
            $item = $sheet->items()->create([
                'item_number' => $itemData['item_number'],
                'boq_item_id' => $itemData['boq_item_id'] ?? null,
                'description' => $itemData['description'] ?? null,
                'display_order' => $index,
            ]);

            if (!empty($itemData['left_desc']) || !empty($itemData['left_measurements'])) {
                $leftDesc = $item->descriptions()->create([
                    'side' => 'left', 'description' => $itemData['left_desc'] ?? '', 'display_order' => 0,
                ]);
                if (!empty($itemData['left_measurements'])) {
                    foreach ($itemData['left_measurements'] as $mIndex => $m) {
                        if (!empty($m['length']) || !empty($m['qty'])) {
                            $leftDesc->measurements()->create([
                                'quantity_count' => $m['qty'] ?? 1, 'length' => $m['length'] ?? 0,
                                'width' => $m['width'] ?? 1, 'height_depth' => $m['height'] ?? 1,
                                'description' => $m['description'] ?? null, 'display_order' => $mIndex,
                            ]);
                        }
                    }
                }
            }

            if (!empty($itemData['right_desc']) || !empty($itemData['right_measurements'])) {
                $rightDesc = $item->descriptions()->create([
                    'side' => 'right', 'description' => $itemData['right_desc'] ?? '', 'display_order' => 1,
                ]);
                if (!empty($itemData['right_measurements'])) {
                    foreach ($itemData['right_measurements'] as $mIndex => $m) {
                        if (!empty($m['length']) || !empty($m['qty'])) {
                            $rightDesc->measurements()->create([
                                'quantity_count' => $m['qty'] ?? 1, 'length' => $m['length'] ?? 0,
                                'width' => $m['width'] ?? 1, 'height_depth' => $m['height'] ?? 1,
                                'description' => $m['description'] ?? null, 'display_order' => $mIndex,
                            ]);
                        }
                    }
                }
            }
        }
    }
}
