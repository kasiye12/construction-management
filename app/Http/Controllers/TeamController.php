<?php
namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    public function manage(Project $project)
    {
        $teamMembers = $project->teamMembers;
        $availableUsers = User::where('is_active', true)
            ->whereNotIn('id', $teamMembers->pluck('id'))
            ->orderBy('name')
            ->get();

        $roles = [
            'project_manager' => 'Project Manager',
            'site_engineer' => 'Site Engineer',
            'quantity_surveyor' => 'Quantity Surveyor',
            'supervisor' => 'Supervisor',
            'foreman' => 'Foreman',
            'inspector' => 'Inspector',
            'draftsman' => 'Draftsman',
        ];

        return view('projects.team', compact('project', 'teamMembers', 'availableUsers', 'roles'));
    }

    public function assign(Request $request, Project $project)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|string|in:project_manager,site_engineer,quantity_surveyor,supervisor,foreman,inspector,draftsman',
            'responsibilities' => 'nullable|string|max:1000',
        ]);

        // Check if already assigned - use wherePivot to avoid column ambiguity
        $exists = $project->teamMembers()
            ->wherePivot('role', $validated['role'])
            ->where('user_id', $validated['user_id'])
            ->exists();

        if ($exists) {
            return back()->with('error', 'User already assigned to this role in this project.');
        }

        $project->teamMembers()->attach($validated['user_id'], [
            'role' => $validated['role'],
            'assigned_date' => now(),
            'is_active' => true,
            'responsibilities' => $validated['responsibilities'] ?? null,
        ]);

        return back()->with('success', 'Team member assigned successfully.');
    }

    public function remove(Project $project, User $user)
    {
        $project->teamMembers()->detach($user->id);
        return back()->with('success', 'Team member removed from project.');
    }

    public function update(Request $request, Project $project, User $user)
    {
        $validated = $request->validate([
            'role' => 'required|string|in:project_manager,site_engineer,quantity_surveyor,supervisor,foreman,inspector,draftsman',
            'is_active' => 'boolean',
            'responsibilities' => 'nullable|string|max:1000',
        ]);

        $project->teamMembers()->updateExistingPivot($user->id, $validated);

        return back()->with('success', 'Team member updated.');
    }
}
