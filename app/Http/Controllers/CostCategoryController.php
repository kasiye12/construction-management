<?php
namespace App\Http\Controllers;

use App\Models\CostCategory;
use App\Models\Project;
use Illuminate\Http\Request;

class CostCategoryController extends Controller
{
    public function index(Request $request)
    {
        $projectId = $request->get('project_id');
        $projects = Project::all();
        
        $query = CostCategory::with('project');
        if ($projectId) {
            $query->where('project_id', $projectId);
        }
        
        $costCategories = $query->orderBy('display_order')->paginate(15);
        return view('cost-categories.index', compact('costCategories', 'projects', 'projectId'));
    }

    public function create()
    {
        $projects = Project::all();
        return view('cost-categories.create', compact('projects'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'code' => 'nullable|string|max:10',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'display_order' => 'integer|min:0'
        ]);

        CostCategory::create($validated);
        return redirect()->route('cost-categories.index')->with('success', 'Cost Category created successfully.');
    }

    public function show(CostCategory $costCategory)
    {
        $costCategory->load(['project', 'boqItems']);
        return view('cost-categories.show', compact('costCategory'));
    }

    public function edit(CostCategory $costCategory)
    {
        $projects = Project::all();
        return view('cost-categories.edit', compact('costCategory', 'projects'));
    }

    public function update(Request $request, CostCategory $costCategory)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'code' => 'nullable|string|max:10',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'display_order' => 'integer|min:0'
        ]);

        $costCategory->update($validated);
        return redirect()->route('cost-categories.index')->with('success', 'Cost Category updated successfully.');
    }

    public function destroy(CostCategory $costCategory)
    {
        $costCategory->delete();
        return redirect()->route('cost-categories.index')->with('success', 'Cost Category deleted successfully.');
    }
}
