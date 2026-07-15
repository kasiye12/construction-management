<?php
namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Subcontractor;
use Illuminate\Http\Request;

class ProjectSubcontractorController extends Controller
{
    public function manage(Project $project)
    {
        $assignedSubs = $project->subcontractors;
        $availableSubs = Subcontractor::where('is_active', true)
            ->whereNotIn('id', $assignedSubs->pluck('id'))
            ->get();
        
        return view('projects.subcontractors', compact('project', 'assignedSubs', 'availableSubs'));
    }

    public function assign(Request $request, Project $project)
    {
        $validated = $request->validate([
            'subcontractor_id' => 'required|exists:subcontractors,id',
            'contract_amount' => 'nullable|numeric|min:0',
            'contract_start_date' => 'nullable|date',
            'contract_end_date' => 'nullable|date|after:contract_start_date',
            'scope_of_work' => 'nullable|string|max:1000',
        ]);

        // Check if already assigned
        if ($project->subcontractors()->where('subcontractor_id', $validated['subcontractor_id'])->exists()) {
            return back()->with('error', 'Subcontractor already assigned to this project.');
        }

        $project->subcontractors()->attach($validated['subcontractor_id'], [
            'contract_amount' => $validated['contract_amount'] ?? 0,
            'contract_start_date' => $validated['contract_start_date'] ?? null,
            'contract_end_date' => $validated['contract_end_date'] ?? null,
            'scope_of_work' => $validated['scope_of_work'] ?? null,
        ]);

        return back()->with('success', 'Subcontractor assigned to project successfully.');
    }

    public function remove(Project $project, Subcontractor $subcontractor)
    {
        $project->subcontractors()->detach($subcontractor->id);
        return back()->with('success', 'Subcontractor removed from project.');
    }

    public function updateContract(Request $request, Project $project, Subcontractor $subcontractor)
    {
        $validated = $request->validate([
            'contract_amount' => 'nullable|numeric|min:0',
            'contract_start_date' => 'nullable|date',
            'contract_end_date' => 'nullable|date',
            'scope_of_work' => 'nullable|string|max:1000',
        ]);

        $project->subcontractors()->updateExistingPivot($subcontractor->id, $validated);

        return back()->with('success', 'Contract details updated.');
    }
}
