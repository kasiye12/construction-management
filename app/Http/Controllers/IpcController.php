<?php
namespace App\Http\Controllers;

use App\Models\Ipc;
use App\Models\Project;
use App\Models\Subcontractor;
use App\Models\BoqItem;
use App\Models\IpcItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class IpcController extends Controller
{
    private function getUserProjectIds()
    {
        $user = Auth::user();
        if ($user->isAdmin()) return Project::pluck('id')->toArray();
        return $user->projects()->where('project_user.is_active', true)->pluck('projects.id')->toArray();
    }

    public function index(Request $request)
    {
        $projectId = $request->get('project_id');
        $user = Auth::user();
        $userProjectIds = $this->getUserProjectIds();
        $projects = $user->isAdmin() ? Project::all() : $user->projects()->where('project_user.is_active', true)->get();
        $query = Ipc::with(['project', 'subcontractor'])->whereIn('project_id', $userProjectIds);
        if ($projectId) $query->where('project_id', $projectId);
        $ipcs = $query->orderBy('ipc_date', 'desc')->paginate(15);
        return view('ipcs.index', compact('ipcs', 'projects', 'projectId'));
    }

    public function create(Request $request)
    {
        $projectId = $request->get('project_id');
        $user = Auth::user();
        $userProjectIds = $this->getUserProjectIds();
        $projects = $user->isAdmin() ? Project::all() : $user->projects()->where('project_user.is_active', true)->get();
        if ($projectId && in_array($projectId, $userProjectIds)) {
            $project = Project::find($projectId);
            $subcontractors = $project ? $project->subcontractors : collect();
            $boqItems = BoqItem::where('project_id', $projectId)->where('is_parent', false)->orderBy('item_number')->get();
        } else {
            $subcontractors = Subcontractor::where('is_active', true)->get();
            $boqItems = collect();
        }
        return view('ipcs.create', compact('projects', 'subcontractors', 'boqItems', 'projectId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'subcontractor_id' => 'required|exists:subcontractors,id',
            'ipc_number' => 'required|string|max:50',
            'ipc_date' => 'required|date',
            'period_start_date' => 'required|date',
            'period_end_date' => 'required|date|after:period_start_date',
            'retention_percentage' => 'nullable|numeric|min:0|max:100',
            'items' => 'required|array',
            'items.*.boq_item_id' => 'required|exists:boq_items,id',
            'items.*.current_quantity' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $ipc = Ipc::create([
                'project_id' => $validated['project_id'],
                'subcontractor_id' => $validated['subcontractor_id'],
                'ipc_number' => $validated['ipc_number'],
                'ipc_date' => $validated['ipc_date'],
                'period_start_date' => $validated['period_start_date'],
                'period_end_date' => $validated['period_end_date'],
                'retention_percentage' => $validated['retention_percentage'] ?? 5,
                'status' => 'draft'
            ]);

            $total = 0;
            foreach ($validated['items'] as $itemData) {
                $boqItem = BoqItem::find($itemData['boq_item_id']);
                $amount = $itemData['current_quantity'] * $boqItem->unit_rate;
                IpcItem::create([
                    'ipc_id' => $ipc->id, 'boq_item_id' => $itemData['boq_item_id'],
                    'contract_quantity' => $boqItem->quantity, 'contract_amount' => $boqItem->revenue_amount,
                    'previous_quantity' => 0, 'previous_amount' => 0,
                    'current_quantity' => $itemData['current_quantity'], 'current_amount' => $amount,
                    'to_date_quantity' => $itemData['current_quantity'], 'to_date_amount' => $amount,
                    'percentage_complete' => $boqItem->quantity > 0 ? ($itemData['current_quantity']/$boqItem->quantity)*100 : 0,
                ]);
                $total += $amount;
            }
            $ipc->update(['total_current_amount' => $total, 'retention_amount' => $total * ($ipc->retention_percentage/100), 'net_payment_amount' => $total - ($total * ($ipc->retention_percentage/100))]);
            DB::commit();
            return redirect()->route('ipcs.show', $ipc)->with('success', 'IPC created.');
        } catch (\Exception $e) { DB::rollback(); return back()->with('error', $e->getMessage())->withInput(); }
    }

    public function show(Ipc $ipc)
    {
        $ipc->refresh(); // Reload from database to get latest data
        $ipc->load(['project', 'subcontractor', 'ipcItems.boqItem']);
        $users = \App\Models\User::where('is_active', true)->orderBy('name')->get();
        return view('ipcs.show', compact('ipc', 'users'));
    }

    public function edit(Ipc $ipc) { return view('ipcs.edit', compact('ipc')); }
    public function update(Request $request, Ipc $ipc) { $ipc->update($request->validate(['status' => 'required|string', 'remarks' => 'nullable|string'])); return redirect()->route('ipcs.show', $ipc)->with('success', 'IPC updated.'); }
    public function destroy(Ipc $ipc) { $ipc->delete(); return redirect()->route('ipcs.index')->with('success', 'IPC deleted.'); }
    public function print(Ipc $ipc) { return view('ipcs.print', compact('ipc')); }

    // ============ WORKFLOW ACTIONS ============
    
    public function prepare(Request $request, Ipc $ipc)
    {
        $name = auth()->user()->name;
        \Log::info("PREPARE: IPC #{$ipc->id} by {$name}");
        
        $ipc->update([
            'prepared_by' => $name,
            'prepared_at' => now(),
            'status' => 'prepared'
        ]);
        
        $ipc->refresh();
        \Log::info("PREPARE RESULT: prepared_by = " . $ipc->prepared_by);
        
        return back()->with('success', "Marked as Prepared by {$name}.");
    }

    public function check(Request $request, Ipc $ipc)
    {
        $name = auth()->user()->name;
        $ipc->update(['checked_by' => $name, 'checked_at' => now(), 'status' => 'checked']);
        return back()->with('success', "Marked as Checked by {$name}.");
    }

    public function submit(Ipc $ipc)
    {
        $name = auth()->user()->name;
        $ipc->update(['submitted_by' => $name, 'submitted_at' => now(), 'status' => 'submitted']);
        return back()->with('success', "Submitted by {$name}.");
    }

    public function approve(Request $request, Ipc $ipc)
    {
        $name = auth()->user()->name;
        $ipc->update(['approved_by' => $name, 'approved_at' => now(), 'status' => 'approved']);
        return back()->with('success', "Approved by {$name}.");
    }

    public function reject(Ipc $ipc)
    {
        $name = auth()->user()->name;
        $ipc->update(['rejected_by' => $name, 'rejected_at' => now(), 'status' => 'rejected']);
        return back()->with('success', "Rejected by {$name}.");
    }

    public function markPaid(Request $request, Ipc $ipc)
    {
        $name = auth()->user()->name;
        $ipc->update(['paid_by' => $name, 'paid_at' => now(), 'status' => 'paid']);
        return back()->with('success', "Marked as Paid by {$name}.");
    }

    public function downloadCertificate(Ipc $ipc)
    {
        $ipc->load(['project', 'subcontractor', 'ipcItems.boqItem']);
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('ipcs.certificate-pdf', compact('ipc'));
        $pdf->setPaper('A4');
        return $pdf->download('Certificate-' . $ipc->ipc_number . '.pdf');
    }
}
