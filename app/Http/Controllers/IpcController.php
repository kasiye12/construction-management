<?php
namespace App\Http\Controllers;

use App\Models\Ipc;
use App\Models\Project;
use App\Models\Subcontractor;
use App\Models\BoqItem;
use App\Models\IpcItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IpcController extends Controller
{
    public function index(Request $request)
    {
        $projectId = $request->get('project_id');
        $projects = Project::all();
        
        $query = Ipc::with(['project', 'subcontractor']);
        if ($projectId) {
            $query->where('project_id', $projectId);
        }
        
        $ipcs = $query->orderBy('ipc_date', 'desc')->paginate(15);
        return view('ipcs.index', compact('ipcs', 'projects', 'projectId'));
    }

    public function create(Request $request)
    {
        $projectId = $request->get('project_id');
        $projects = Project::all();
        $subcontractors = collect();
        $boqItems = collect();
        
        if ($projectId) {
            $project = Project::find($projectId);
            if ($project) {
                $subcontractors = $project->subcontractors;
                $boqItems = BoqItem::where('project_id', $projectId)
                                  ->where('is_parent', false)
                                  ->orderBy('item_number')
                                  ->get();
            }
        }
        
        return view('ipcs.create', compact('projects', 'subcontractors', 'boqItems', 'projectId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'subcontractor_id' => 'required|exists:subcontractors,id',
            'ipc_number' => 'required|string|max:50',
            'issue_number' => 'required|integer|min:1',
            'ipc_date' => 'required|date',
            'period_start_date' => 'required|date',
            'period_end_date' => 'required|date|after:period_start_date',
            'retention_percentage' => 'nullable|numeric|min:0|max:100',
            'remarks' => 'nullable|string',
            'items' => 'required|array',
            'items.*.boq_item_id' => 'required|exists:boq_items,id',
            'items.*.contract_quantity' => 'required|numeric|min:0',
            'items.*.contract_amount' => 'required|numeric|min:0',
            'items.*.previous_quantity' => 'required|numeric|min:0',
            'items.*.previous_amount' => 'required|numeric|min:0',
            'items.*.current_quantity' => 'required|numeric|min:0'
        ]);

        DB::beginTransaction();
        try {
            $ipc = Ipc::create([
                'project_id' => $validated['project_id'],
                'subcontractor_id' => $validated['subcontractor_id'],
                'ipc_number' => $validated['ipc_number'],
                'issue_number' => $validated['issue_number'],
                'ipc_date' => $validated['ipc_date'],
                'period_start_date' => $validated['period_start_date'],
                'period_end_date' => $validated['period_end_date'],
                'retention_percentage' => $validated['retention_percentage'] ?? 5,
                'remarks' => $validated['remarks'] ?? null
            ]);

            $totalCurrentAmount = 0;
            $totalPreviousAmount = 0;

            foreach ($validated['items'] as $itemData) {
                $boqItem = BoqItem::find($itemData['boq_item_id']);
                $currentAmount = $itemData['current_quantity'] * $boqItem->unit_rate;
                $toDateQuantity = $itemData['previous_quantity'] + $itemData['current_quantity'];
                $toDateAmount = $itemData['previous_amount'] + $currentAmount;
                $percentageComplete = $itemData['contract_quantity'] > 0 ? 
                    ($toDateQuantity / $itemData['contract_quantity']) * 100 : 0;

                IpcItem::create([
                    'ipc_id' => $ipc->id,
                    'boq_item_id' => $itemData['boq_item_id'],
                    'contract_quantity' => $itemData['contract_quantity'],
                    'contract_amount' => $itemData['contract_amount'],
                    'previous_quantity' => $itemData['previous_quantity'],
                    'previous_amount' => $itemData['previous_amount'],
                    'current_quantity' => $itemData['current_quantity'],
                    'current_amount' => $currentAmount,
                    'to_date_quantity' => $toDateQuantity,
                    'to_date_amount' => $toDateAmount,
                    'percentage_complete' => $percentageComplete,
                    'remark' => $itemData['remark'] ?? null
                ]);

                $totalCurrentAmount += $currentAmount;
                $totalPreviousAmount += $itemData['previous_amount'];
            }

            $ipc->total_previous_amount = $totalPreviousAmount;
            $ipc->total_current_amount = $totalCurrentAmount;
            $ipc->total_to_date_amount = $totalPreviousAmount + $totalCurrentAmount;
            $ipc->retention_amount = $ipc->total_to_date_amount * ($ipc->retention_percentage / 100);
            $ipc->net_payment_amount = $ipc->total_to_date_amount - $ipc->retention_amount;
            $ipc->save();

            DB::commit();
            return redirect()->route('ipcs.show', $ipc)->with('success', 'IPC created successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error: ' . $e->getMessage())->withInput();
        }
    }

    public function show(Ipc $ipc)
    {
        $ipc->load(['project', 'subcontractor', 'ipcItems.boqItem']);
        return view('ipcs.show', compact('ipc'));
    }

    public function edit(Ipc $ipc)
    {
        $projects = Project::all();
        $subcontractors = Subcontractor::all();
        $ipc->load('ipcItems.boqItem');
        return view('ipcs.edit', compact('ipc', 'projects', 'subcontractors'));
    }

    public function update(Request $request, Ipc $ipc)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'subcontractor_id' => 'required|exists:subcontractors,id',
            'ipc_number' => 'required|string|max:50',
            'ipc_date' => 'required|date',
            'retention_percentage' => 'nullable|numeric|min:0|max:100',
            'status' => 'required|in:draft,submitted,approved,paid',
            'remarks' => 'nullable|string'
        ]);

        $ipc->update($validated);
        return redirect()->route('ipcs.show', $ipc)->with('success', 'IPC updated successfully.');
    }

    public function destroy(Ipc $ipc)
    {
        $ipc->delete();
        return redirect()->route('ipcs.index')->with('success', 'IPC deleted successfully.');
    }

    public function print(Ipc $ipc)
    {
        $ipc->load(['project', 'subcontractor', 'ipcItems.boqItem']);
        return view('ipcs.print', compact('ipc'));
    }

    public function approve(Ipc $ipc)
    {
        $ipc->update(['status' => 'approved']);
        return redirect()->route('ipcs.show', $ipc)->with('success', 'IPC approved successfully.');
    }
}
