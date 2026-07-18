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
    private function getUserProjectIds(): array
    {
        $user = Auth::user();
        if (! $user) {
            return [];
        }
        if ($user->isAdmin()) {
            return Project::pluck('id')->toArray();
        }
        return $user->projects()
                    ->where('project_user.is_active', true)
                    ->pluck('projects.id')
                    ->toArray();
    }

    private function getUserProjects()
    {
        $user = Auth::user();
        if ($user->isAdmin()) {
            return Project::all();
        }
        $ids = $this->getUserProjectIds();
        return Project::whereIn('id', $ids)->get();
    }

    // ------------------------------------------------------------------
    // LIST
    // ------------------------------------------------------------------
    public function index(Request $request)
    {
        $projects = $this->getUserProjects();
        $query    = Ipc::with(['project', 'subcontractor'])
                       ->whereIn('project_id', $this->getUserProjectIds());

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $query->where('ipc_number', 'LIKE', "%{$request->search}%");
        }
        if ($request->filled('date_from')) {
            $query->where('ipc_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('ipc_date', '<=', $request->date_to);
        }

        $ipcs = $query->orderBy('ipc_date', 'desc')
                      ->paginate(15)
                      ->appends($request->query());

        return view('ipcs.index', compact('ipcs', 'projects'));
    }

    // ------------------------------------------------------------------
    // CREATE
    // ------------------------------------------------------------------
    public function create(Request $request)
    {
        $projectId      = $request->get('project_id');
        $projects       = $this->getUserProjects();
        $subcontractors = collect();
        $boqItems       = collect();

        if ($projectId && in_array($projectId, $this->getUserProjectIds())) {
            $project       = Project::find($projectId);
            $subcontractors = $project ? $project->subcontractors : collect();
            $boqItems       = BoqItem::where('project_id', $projectId)
                                    ->where('is_parent', false)
                                    ->orderBy('item_number')
                                    ->get();
        } else {
            $subcontractors = Subcontractor::where('is_active', true)->get();
        }

        return view('ipcs.create', compact('projects', 'subcontractors', 'boqItems', 'projectId'));
    }

    // ------------------------------------------------------------------
    // STORE
    // ------------------------------------------------------------------
    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id'          => 'required|exists:projects,id',
            'subcontractor_id'    => 'required|exists:subcontractors,id',
            'ipc_number'          => 'required|string|max:50',
            'ipc_date'            => 'required|date',
            'period_start_date'   => 'required|date',
            'period_end_date'     => 'required|date|after:period_start_date',
            'retention_percentage'=> 'nullable|numeric|min:0|max:100',
            'items'               => 'required|array',
            'items.*.boq_item_id' => 'required|exists:boq_items,id',
            'items.*.current_quantity' => 'required|numeric|min:0',
        ]);

        if (! in_array($validated['project_id'], $this->getUserProjectIds())) {
            return back()->with('error', 'You are not assigned to this project.')->withInput();
        }

        DB::beginTransaction();
        try {
            $ipc = Ipc::create([
                'project_id'          => $validated['project_id'],
                'subcontractor_id'    => $validated['subcontractor_id'],
                'ipc_number'          => $validated['ipc_number'],
                'ipc_date'            => $validated['ipc_date'],
                'period_start_date'   => $validated['period_start_date'],
                'period_end_date'     => $validated['period_end_date'],
                'retention_percentage'=> $validated['retention_percentage'] ?? 5,
                'status'              => 'draft',
            ]);

            $total = 0;
            foreach ($validated['items'] as $itemData) {
                $boqItem = BoqItem::find($itemData['boq_item_id']);
                $amount  = $itemData['current_quantity'] * $boqItem->unit_rate;

                IpcItem::create([
                    'ipc_id'              => $ipc->id,
                    'boq_item_id'         => $itemData['boq_item_id'],
                    'contract_quantity'   => $boqItem->quantity,
                    'contract_amount'     => $boqItem->revenue_amount,
                    'previous_quantity'   => 0,
                    'previous_amount'     => 0,
                    'current_quantity'    => $itemData['current_quantity'],
                    'current_amount'      => $amount,
                    'to_date_quantity'    => $itemData['current_quantity'],
                    'to_date_amount'      => $amount,
                    'percentage_complete' => $boqItem->quantity > 0
                        ? ($itemData['current_quantity'] / $boqItem->quantity) * 100
                        : 0,
                ]);
                $total += $amount;
            }

            $retentionAmount = $total * ($ipc->retention_percentage / 100);
            $ipc->update([
                'total_current_amount' => $total,
                'retention_amount'     => $retentionAmount,
                'net_payment_amount'   => $total - $retentionAmount,
            ]);

            DB::commit();
            return redirect()->route('ipcs.show', $ipc)->with('success', 'IPC created successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    // ------------------------------------------------------------------
    // SHOW
    // ------------------------------------------------------------------
    public function show(Ipc $ipc)
    {
        if (! in_array($ipc->project_id, $this->getUserProjectIds())) {
            abort(403);
        }
        $ipc->load(['project', 'subcontractor', 'ipcItems.boqItem']);
        $users = \App\Models\User::where('is_active', true)->orderBy('name')->get();
        return view('ipcs.show', compact('ipc', 'users'));
    }

    // ------------------------------------------------------------------
    // EDIT / UPDATE (locked after submit)
    // ------------------------------------------------------------------
    public function edit(Ipc $ipc)
    {
        if (! in_array($ipc->project_id, $this->getUserProjectIds())) {
            abort(403);
        }
        // Lock: only draft / prepared / checked can be edited (admin can always edit)
        if (! in_array($ipc->status, ['draft', 'prepared', 'checked']) && ! Auth::user()->isAdmin()) {
            return back()->with('error',
                'Cannot edit an IPC in "' . ucfirst($ipc->status) . '" status. Only draft, prepared or checked certificates can be edited.');
        }
        return view('ipcs.edit', compact('ipc'));
    }

    public function update(Request $request, Ipc $ipc)
    {
        if (! in_array($ipc->project_id, $this->getUserProjectIds())) {
            abort(403);
        }
        if (! in_array($ipc->status, ['draft', 'prepared', 'checked']) && ! Auth::user()->isAdmin()) {
            return back()->with('error',
                'Cannot update an IPC in "' . ucfirst($ipc->status) . '" status.');
        }
        $data = $request->validate([
            'status'  => 'required|string',
            'remarks' => 'nullable|string|max:1000',
        ]);
        $ipc->update($data);
        return redirect()->route('ipcs.show', $ipc)->with('success', 'IPC updated.');
    }

    // ------------------------------------------------------------------
    // DELETE (locked for approved / paid)
    // ------------------------------------------------------------------
    public function destroy(Ipc $ipc)
    {
        if (! in_array($ipc->project_id, $this->getUserProjectIds())) {
            abort(403);
        }
        // Lock: approved & paid cannot be deleted (admin override)
        if (in_array($ipc->status, ['approved', 'paid']) && ! Auth::user()->isAdmin()) {
            return back()->with('error',
                'Cannot delete an IPC in "' . ucfirst($ipc->status) . '" status. Only admin can delete approved/paid certificates.');
        }
        $ipc->delete();
        return redirect()->route('ipcs.index')->with('success', 'IPC deleted.');
    }

    // ------------------------------------------------------------------
    // PRINT / PDF
    // ------------------------------------------------------------------
    public function print(Ipc $ipc)
    {
        if (! in_array($ipc->project_id, $this->getUserProjectIds())) {
            abort(403);
        }
        return view('ipcs.print', compact('ipc'));
    }

    public function downloadCertificate(Ipc $ipc)
    {
        if (! in_array($ipc->project_id, $this->getUserProjectIds())) {
            abort(403);
        }
        $ipc->load(['project', 'subcontractor', 'ipcItems.boqItem']);
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('ipcs.certificate-pdf', compact('ipc'));
        $pdf->setPaper('A4');
        return $pdf->download('Certificate-' . $ipc->ipc_number . '.pdf');
    }

    // ==================================================================
    // WORKFLOW ACTIONS
    // ==================================================================

    public function prepare(Ipc $ipc)
    {
        $ipc->update([
            'prepared_by' => Auth::user()->name,
            'prepared_at' => now(),
            'status'      => 'prepared',
        ]);
        return back()->with('success', 'Certificate marked as Prepared by ' . Auth::user()->name);
    }

    public function check(Ipc $ipc)
    {
        $ipc->update([
            'checked_by' => Auth::user()->name,
            'checked_at' => now(),
            'status'     => 'checked',
        ]);
        return back()->with('success', 'Certificate marked as Checked by ' . Auth::user()->name);
    }

    public function submit(Ipc $ipc)
    {
        $ipc->update([
            'submitted_by' => Auth::user()->name,
            'submitted_at' => now(),
            'status'       => 'submitted',
        ]);
        return back()->with('success', 'Certificate submitted for approval by ' . Auth::user()->name);
    }

    public function approve(Ipc $ipc)
    {
        $ipc->update([
            'approved_by' => Auth::user()->name,
            'approved_at' => now(),
            'status'      => 'approved',
        ]);
        return back()->with('success', 'Certificate approved by ' . Auth::user()->name);
    }

    public function reject(Ipc $ipc)
    {
        $ipc->update([
            'rejected_by' => Auth::user()->name,
            'rejected_at' => now(),
            'status'      => 'rejected',
        ]);
        return back()->with('success', 'Certificate rejected.');
    }

    public function markPaid(Ipc $ipc)
    {
        $ipc->update([
            'paid_by' => Auth::user()->name,
            'paid_at' => now(),
            'status'  => 'paid',
        ]);
        return back()->with('success', 'Certificate marked as Paid by ' . Auth::user()->name);
    }
}
