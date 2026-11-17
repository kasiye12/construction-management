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

    public function update(Request $request)
    {
        $permissions = $request->input('permissions', []);
        
        foreach ($permissions as $userId => $steps) {
            WorkflowPermission::syncForUser($userId, $steps);
        }

        return back()->with('success', 'All workflow permissions updated successfully!');
    }

    public function applyPreset(Request $request)
    {
        $preset = $request->input('preset', 'standard');
        $users = User::all();
        
        $presets = [
            'standard' => [
                'admin' => [
                    'prepare','check','submit','approve','reject','pay',
                    'verify_takeoff','approve_takeoff',
                    'verify_takeoff_sheet','approve_takeoff_sheet',
                    'record_delivery','confirm_delivery',
                    'prepare_contractual_report','check_contractual_report','approve_contractual_report','reject_contractual_report',
                    'prepare_equipment_schedule','check_equipment_schedule','approve_equipment_schedule','reject_equipment_schedule',
                    'prepare_maintenance_report','check_maintenance_report','approve_maintenance_report','reject_maintenance_report',
                ],
                'manager' => [
                    'prepare','check','submit','approve','reject',
                    'verify_takeoff','approve_takeoff',
                    'verify_takeoff_sheet','approve_takeoff_sheet',
                    'record_delivery','confirm_delivery',
                    'prepare_contractual_report','check_contractual_report','approve_contractual_report','reject_contractual_report',
                    'prepare_equipment_schedule','check_equipment_schedule','approve_equipment_schedule','reject_equipment_schedule',
                    'prepare_maintenance_report','check_maintenance_report','approve_maintenance_report','reject_maintenance_report',
                ],
                'engineer' => [
                    'prepare','check','submit',
                    'verify_takeoff',
                    'verify_takeoff_sheet',
                    'record_delivery',
                    'prepare_contractual_report','check_contractual_report',
                    'prepare_equipment_schedule','check_equipment_schedule',
                    'prepare_maintenance_report','check_maintenance_report',
                ],
                'finance' => [
                    'approve','reject','pay',
                    'confirm_delivery',
                    'approve_contractual_report','reject_contractual_report',
                    'approve_equipment_schedule','reject_equipment_schedule',
                    'approve_maintenance_report','reject_maintenance_report',
                ],
                'viewer' => [],
            ],
            'all_managers' => [
                'admin' => [
                    'prepare','check','submit','approve','reject','pay',
                    'verify_takeoff','approve_takeoff',
                    'verify_takeoff_sheet','approve_takeoff_sheet',
                    'record_delivery','confirm_delivery',
                    'prepare_contractual_report','check_contractual_report','approve_contractual_report','reject_contractual_report',
                    'prepare_equipment_schedule','check_equipment_schedule','approve_equipment_schedule','reject_equipment_schedule',
                    'prepare_maintenance_report','check_maintenance_report','approve_maintenance_report','reject_maintenance_report',
                ],
                'manager' => [
                    'prepare','check','submit','approve','reject','pay',
                    'verify_takeoff','approve_takeoff',
                    'verify_takeoff_sheet','approve_takeoff_sheet',
                    'record_delivery','confirm_delivery',
                    'prepare_contractual_report','check_contractual_report','approve_contractual_report','reject_contractual_report',
                    'prepare_equipment_schedule','check_equipment_schedule','approve_equipment_schedule','reject_equipment_schedule',
                    'prepare_maintenance_report','check_maintenance_report','approve_maintenance_report','reject_maintenance_report',
                ],
                'engineer' => [
                    'prepare','check','submit',
                    'verify_takeoff',
                    'verify_takeoff_sheet',
                    'record_delivery',
                    'prepare_contractual_report','check_contractual_report',
                    'prepare_equipment_schedule','check_equipment_schedule',
                    'prepare_maintenance_report','check_maintenance_report',
                ],
                'finance' => [
                    'approve','reject','pay',
                    'confirm_delivery',
                    'approve_contractual_report','reject_contractual_report',
                    'approve_equipment_schedule','reject_equipment_schedule',
                    'approve_maintenance_report','reject_maintenance_report',
                ],
                'viewer' => [],
            ],
        ];

        $selectedPreset = $presets[$preset] ?? $presets['standard'];

        foreach ($users as $user) {
            $role = $user->getRoleName();
            $allowedSteps = $selectedPreset[$role] ?? [];
            
            $allSteps = [
                'prepare','check','submit','approve','reject','pay',
                'verify_takeoff','approve_takeoff',
                'verify_takeoff_sheet','approve_takeoff_sheet',
                'record_delivery','confirm_delivery',
                'prepare_contractual_report','check_contractual_report','approve_contractual_report','reject_contractual_report',
                'prepare_equipment_schedule','check_equipment_schedule','approve_equipment_schedule','reject_equipment_schedule',
                'prepare_maintenance_report','check_maintenance_report','approve_maintenance_report','reject_maintenance_report',
            ];
            
            $userPermissions = [];
            foreach ($allSteps as $step) {
                $userPermissions[$step] = in_array($step, $allowedSteps);
            }
            
            WorkflowPermission::syncForUser($user->id, $userPermissions);
        }

        return back()->with('success', "Preset '{$preset}' applied successfully!");
    }
}
