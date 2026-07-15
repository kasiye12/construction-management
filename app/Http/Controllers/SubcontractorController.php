<?php
namespace App\Http\Controllers;

use App\Models\Subcontractor;
use App\Models\Project;
use Illuminate\Http\Request;

class SubcontractorController extends Controller
{
    public function index()
    {
        $subcontractors = Subcontractor::with('projects')->orderBy('name')->paginate(10);
        return view('subcontractors.index', compact('subcontractors'));
    }

    public function create()
    {
        $projects = Project::all();
        return view('subcontractors.create', compact('projects'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|min:2',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'tax_id' => 'nullable|string|max:50',
            'is_active' => 'boolean'
        ]);

        Subcontractor::create($validated);
        return redirect()->route('subcontractors.index')->with('success', 'Subcontractor created successfully.');
    }

    public function show(Subcontractor $subcontractor)
    {
        $subcontractor->load(['projects', 'ipcs.ipcItems.boqItem']);
        return view('subcontractors.show', compact('subcontractor'));
    }

    public function edit(Subcontractor $subcontractor)
    {
        $projects = Project::all();
        return view('subcontractors.edit', compact('subcontractor', 'projects'));
    }

    public function update(Request $request, Subcontractor $subcontractor)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|min:2',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'tax_id' => 'nullable|string|max:50',
            'is_active' => 'boolean'
        ]);

        $subcontractor->update($validated);
        return redirect()->route('subcontractors.index')->with('success', 'Subcontractor updated successfully.');
    }

    public function destroy(Subcontractor $subcontractor)
    {
        $subcontractor->delete();
        return redirect()->route('subcontractors.index')->with('success', 'Subcontractor deleted successfully.');
    }
}
