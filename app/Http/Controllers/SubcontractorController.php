<?php
namespace App\Http\Controllers;

use App\Models\Subcontractor;
use App\Models\Project;
use Illuminate\Http\Request;

class SubcontractorController extends Controller
{
    public function index()
    {
        $subcontractors = Subcontractor::withCount('projects')->orderBy('name')->paginate(15);
        return view('subcontractors.index', compact('subcontractors'));
    }

    public function create()
    {
        return view('subcontractors.create');
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
        return redirect()->route('subcontractors.index')->with('success', 'Subcontractor created.');
    }

    public function show(Subcontractor $subcontractor)
    {
        $subcontractor->load(['projects', 'ipcs' => function($q) { $q->latest()->take(10); }]);
        return view('subcontractors.show', compact('subcontractor'));
    }

    public function edit(Subcontractor $subcontractor)
    {
        return view('subcontractors.edit', compact('subcontractor'));
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
        return redirect()->route('subcontractors.index')->with('success', 'Subcontractor updated.');
    }

    public function destroy(Subcontractor $subcontractor)
    {
        $subcontractor->delete();
        return redirect()->route('subcontractors.index')->with('success', 'Subcontractor deleted.');
    }
}
