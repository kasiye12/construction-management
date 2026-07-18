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
        $users = User::with('workflowPermissions')->orderBy('name')->get();
        return view('admin.workflow.permissions', compact('users'));
    }

    /**
     * Update all workflow permissions at once
     */
    public function update(Request $request)
    {
        $permissions = $request->input('permissions', []);
        
        foreach ($permissions as $userId => $steps) {
            // Only sync the steps that were actually submitted (checked or unchecked)
            WorkflowPermission::syncForUser($userId, $steps);
        }

        return back()->with('success', '✅ All workflow permissions updated successfully!');
    }

    /**
     * Apply preset permission templates
     */
    public function applyPreset(Request $request)
    {
        $preset = $request->input('preset', 'standard');
        $users = User::all();
        
        $presets = [
            'standard' => [
                'admin' => [
                    'prepare','check','submit','approve','reject','pay',
                    'verify_takeoff','approve_takeoff',
                    'record_delivery','confirm_delivery'
                ],
                'manager' => [
                    'prepare','check','submit','approve','reject',
                    'verify_takeoff','approve_takeoff',
                    'record_delivery','confirm_delivery'
                ],
                'engineer' => [
                    'prepare','check','submit',
                    'verify_takeoff',
                    'record_delivery'
                ],
                'finance' => [
                    'approve','reject','pay',
                    'confirm_delivery'
                ],
                'viewer' => [],
            ],
            'all_managers' => [
                'admin' => ['prepare','check','submit','approve','reject','pay','verify_takeoff','approve_takeoff','record_delivery','confirm_delivery'],
                'manager' => ['prepare','check','submit','approve','reject','pay','verify_takeoff','approve_takeoff','record_delivery','confirm_delivery'],
                'engineer' => ['prepare','check','submit','verify_takeoff','record_delivery'],
                'finance' => ['approve','reject','pay','confirm_delivery'],
                'viewer' => [],
            ],
        ];

        $selectedPreset = $presets[$preset] ?? $presets['standard'];

        foreach ($users as $user) {
            $role = $user->getRoleName();
            $allowedSteps = $selectedPreset[$role] ?? [];
            
            // Build permissions array for all workflow steps
            $allSteps = [
                'prepare','check','submit','approve','reject','pay',
                'verify_takeoff','approve_takeoff',
                'record_delivery','confirm_delivery'
            ];
            
            $userPermissions = [];
            foreach ($allSteps as $step) {
                $userPermissions[$step] = in_array($step, $allowedSteps);
            }
            
            WorkflowPermission::syncForUser($user->id, $userPermissions);
        }

        return back()->with('success', "✅ Preset '{$preset}' applied successfully!");
    }
}
