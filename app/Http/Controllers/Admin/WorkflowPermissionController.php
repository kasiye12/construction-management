<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WorkflowPermission;
use App\Models\User;
use Illuminate\Http\Request;

class WorkflowPermissionController extends Controller
{
    public function index()
    {
        return view('admin.workflow.permissions');
    }

    public function update(Request $request)
    {
        $permissions = $request->input('permissions', []);
        
        foreach ($permissions as $userId => $steps) {
            WorkflowPermission::syncForUser($userId, $steps);
        }

        return back()->with('success', 'Workflow permissions updated successfully.');
    }

    public function applyPreset(Request $request)
    {
        $preset = $request->input('preset');
        $users = User::all();
        
        $presets = [
            'standard' => [
                'admin' => ['prepare','check','submit','approve','reject','pay'],
                'manager' => ['prepare','check','submit','approve','reject'],
                'engineer' => ['prepare','check','submit'],
                'finance' => ['approve','reject','pay'],
                'viewer' => [],
            ],
            'all_managers' => [
                'admin' => ['prepare','check','submit','approve','reject','pay'],
                'manager' => ['prepare','check','submit','approve','reject','pay'],
                'engineer' => ['prepare','check','submit'],
                'finance' => ['approve','reject','pay'],
                'viewer' => [],
            ],
            'engineers_only' => [
                'admin' => ['prepare','check','submit','approve','reject','pay'],
                'manager' => ['approve','reject'],
                'engineer' => ['prepare','check','submit'],
                'finance' => ['approve','reject','pay'],
                'viewer' => [],
            ],
            'finance_approve' => [
                'admin' => ['prepare','check','submit','approve','reject','pay'],
                'manager' => ['prepare','check','submit'],
                'engineer' => ['prepare','check','submit'],
                'finance' => ['approve','reject','pay'],
                'viewer' => [],
            ],
        ];

        $selectedPreset = $presets[$preset] ?? $presets['standard'];

        foreach ($users as $user) {
            $role = $user->getRoleName();
            $steps = $selectedPreset[$role] ?? [];
            $permissions = [];
            foreach (['prepare','check','submit','approve','reject','pay'] as $step) {
                $permissions[$step] = in_array($step, $steps);
            }
            WorkflowPermission::syncForUser($user->id, $permissions);
        }

        return back()->with('success', 'Preset "' . $preset . '" applied successfully.');
    }
}
