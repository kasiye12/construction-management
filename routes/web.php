<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

// ==========================================
// PUBLIC ROUTES
// ==========================================
Route::get('/', [App\Http\Controllers\ProjectController::class, 'dashboard'])->name('dashboard');

Route::get('/login', function() { return view('auth.login'); })->name('login');
Route::post('/login', function(Request $request) {
    $credentials = $request->only('email', 'password');
    if (Auth::attempt($credentials)) {
        $user = Auth::user();
        $user->update(['last_login_at' => now(), 'last_login_ip' => $request->ip()]);
        return redirect()->intended('/');
    }
    return back()->withErrors(['email' => 'Invalid credentials.'])->withInput();
})->name('login.submit');

Route::post('/logout', function() { Auth::logout(); return redirect('/'); })->name('logout');

// ==========================================
// ALL AUTHENTICATED ROUTES
// ==========================================
Route::middleware(['auth'])->group(function () {
    
    // Notifications (accessible to all logged-in users)
    Route::get('/notifications', [App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/{notification}/read', [App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/mark-all-read', [App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::delete('/notifications/{notification}', [App\Http\Controllers\NotificationController::class, 'delete'])->name('notifications.delete');
    Route::delete('/notifications/delete-all-read', [App\Http\Controllers\NotificationController::class, 'deleteAllRead'])->name('notifications.delete-all-read');

    // User Profile (accessible to logged-in user)
    Route::get('/admin/profile', [App\Http\Controllers\Admin\UserController::class, 'profile'])->name('admin.profile');
    Route::put('/admin/profile', [App\Http\Controllers\Admin\UserController::class, 'updateProfile'])->name('admin.profile.update');
    Route::put('/admin/profile/password', [App\Http\Controllers\Admin\UserController::class, 'updatePassword'])->name('admin.profile.password');

    // Documents (protected per action)
    Route::middleware('permission:documents.upload')->group(function () {
        Route::post('/documents/upload', [App\Http\Controllers\DocumentController::class, 'upload'])->name('documents.upload');
    });
    Route::middleware('permission:documents.view')->group(function () {
        Route::get('/documents/{document}/download', [App\Http\Controllers\DocumentController::class, 'download'])->name('documents.download');
        Route::get('/documents/{document}/preview', [App\Http\Controllers\DocumentController::class, 'preview'])->name('documents.preview');
    });
    Route::middleware('permission:documents.delete')->group(function () {
        Route::delete('/documents/{document}', [App\Http\Controllers\DocumentController::class, 'destroy'])->name('documents.destroy');
    });

    // 1. Projects (create & import defined before {project} to prevent route model binding conflicts)
    Route::middleware('permission:projects.create')->group(function () {
        Route::get('/projects/create', [App\Http\Controllers\ProjectController::class, 'create'])->name('projects.create');
        Route::get('/projects/import', [App\Http\Controllers\ProjectController::class, 'importView'])->name('projects.import');
        Route::post('/projects/import', [App\Http\Controllers\ProjectController::class, 'import'])->name('projects.import.post');
        Route::get('/projects/template/download', [App\Http\Controllers\ProjectController::class, 'downloadTemplate'])->name('projects.template.download');
        Route::post('/projects', [App\Http\Controllers\ProjectController::class, 'store'])->name('projects.store');
        Route::post('/projects/{project}/subcontractors/assign', [App\Http\Controllers\ProjectSubcontractorController::class, 'assign'])->name('projects.subcontractors.assign');
        Route::post('/projects/{project}/team/assign', [App\Http\Controllers\TeamController::class, 'assign'])->name('projects.team.assign');
    });
    Route::middleware('permission:projects.edit')->group(function () {
        Route::get('/projects/{project}/edit', [App\Http\Controllers\ProjectController::class, 'edit'])->name('projects.edit');
        Route::match(['put', 'patch'], '/projects/{project}', [App\Http\Controllers\ProjectController::class, 'update'])->name('projects.update');
        Route::put('/projects/{project}/subcontractors/{subcontractor}', [App\Http\Controllers\ProjectSubcontractorController::class, 'updateContract'])->name('projects.subcontractors.update');
        Route::put('/projects/{project}/team/{user}', [App\Http\Controllers\TeamController::class, 'update'])->name('projects.team.update');
    });
    Route::middleware('permission:projects.delete')->group(function () {
        Route::delete('/projects/{project}', [App\Http\Controllers\ProjectController::class, 'destroy'])->name('projects.destroy');
        Route::delete('/projects/{project}/subcontractors/{subcontractor}', [App\Http\Controllers\ProjectSubcontractorController::class, 'remove'])->name('projects.subcontractors.remove');
        Route::delete('/projects/{project}/team/{user}', [App\Http\Controllers\TeamController::class, 'remove'])->name('projects.team.remove');
    });
    Route::middleware('permission:projects.view')->group(function () {
        Route::get('/projects', [App\Http\Controllers\ProjectController::class, 'index'])->name('projects.index');
        Route::get('/projects/{project}/subcontractors', [App\Http\Controllers\ProjectSubcontractorController::class, 'manage'])->name('projects.subcontractors');
        Route::get('/projects/{project}/team', [App\Http\Controllers\TeamController::class, 'manage'])->name('projects.team');
        Route::get('/projects/{project}', [App\Http\Controllers\ProjectController::class, 'show'])->name('projects.show');
    });

    // 2. BOQ Items (create defined before {boq_item})
    Route::middleware('permission:boq.create')->group(function () {
        Route::get('/boq-items/create', [App\Http\Controllers\BoqItemController::class, 'create'])->name('boq-items.create');
        Route::post('/boq-items', [App\Http\Controllers\BoqItemController::class, 'store'])->name('boq-items.store');
    });
    Route::middleware('permission:boq.edit')->group(function () {
        Route::get('/boq-items/{boq_item}/edit', [App\Http\Controllers\BoqItemController::class, 'edit'])->name('boq-items.edit');
        Route::match(['put', 'patch'], '/boq-items/{boq_item}', [App\Http\Controllers\BoqItemController::class, 'update'])->name('boq-items.update');
    });
    Route::middleware('permission:boq.delete')->group(function () {
        Route::delete('/boq-items/{boq_item}', [App\Http\Controllers\BoqItemController::class, 'destroy'])->name('boq-items.destroy');
    });
    Route::middleware('permission:boq.view')->group(function () {
        Route::get('/boq-items', [App\Http\Controllers\BoqItemController::class, 'index'])->name('boq-items.index');
        Route::get('/api/boq-items/project/{project}', [App\Http\Controllers\BoqItemController::class, 'getByProject']);
        Route::get('/boq-items/{boq_item}', [App\Http\Controllers\BoqItemController::class, 'show'])->name('boq-items.show');
    });

    // 3. Quantity Take-Offs (create defined before {quantityTakeoff})
    Route::middleware('permission:takeoff.create')->group(function () {
        Route::get('/quantity-takeoffs/create', [App\Http\Controllers\QuantityTakeoffController::class, 'create'])->name('quantity-takeoffs.create');
        Route::post('/quantity-takeoffs', [App\Http\Controllers\QuantityTakeoffController::class, 'store'])->name('quantity-takeoffs.store');
        Route::post('/quantity-takeoffs/store-multiple', [App\Http\Controllers\QuantityTakeoffController::class, 'storeMultiple'])->name('quantity-takeoffs.store-multiple');
    });
    Route::middleware('permission:takeoff.edit')->group(function () {
        Route::get('/quantity-takeoffs/{quantityTakeoff}/edit', [App\Http\Controllers\QuantityTakeoffController::class, 'edit'])->name('quantity-takeoffs.edit');
        Route::match(['put', 'patch'], '/quantity-takeoffs/{quantityTakeoff}', [App\Http\Controllers\QuantityTakeoffController::class, 'update'])->name('quantity-takeoffs.update');
        Route::post('/quantity-takeoffs/{quantityTakeoff}/verify', [App\Http\Controllers\QuantityTakeoffController::class, 'verify'])->name('quantity-takeoffs.verify');
        Route::post('/quantity-takeoffs/{quantityTakeoff}/approve', [App\Http\Controllers\QuantityTakeoffController::class, 'approve'])->name('quantity-takeoffs.approve');
        Route::post('/quantity-takeoffs/{quantityTakeoff}/revert', [App\Http\Controllers\QuantityTakeoffController::class, 'revertToDraft'])->name('quantity-takeoffs.revert');
    });
    Route::middleware('permission:takeoff.delete')->group(function () {
        Route::delete('/quantity-takeoffs/{quantityTakeoff}', [App\Http\Controllers\QuantityTakeoffController::class, 'destroy'])->name('quantity-takeoffs.destroy');
    });
    Route::middleware('permission:takeoff.view')->group(function () {
        Route::get('/quantity-takeoffs', [App\Http\Controllers\QuantityTakeoffController::class, 'index'])->name('quantity-takeoffs.index');
        Route::get('/quantity-takeoffs/{quantityTakeoff}', [App\Http\Controllers\QuantityTakeoffController::class, 'show'])->name('quantity-takeoffs.show');
    });

    // 4. Takeoff Sheets (create defined before {takeoff_sheet})
    Route::middleware('permission:takeoff-sheets.create')->group(function () {
        Route::get('/takeoff-sheets/create', [App\Http\Controllers\TakeoffSheetController::class, 'create'])->name('takeoff-sheets.create');
        Route::post('/takeoff-sheets', [App\Http\Controllers\TakeoffSheetController::class, 'store'])->name('takeoff-sheets.store');
    });
    Route::middleware('permission:takeoff-sheets.edit')->group(function () {
        Route::get('/takeoff-sheets/{takeoff_sheet}/edit', [App\Http\Controllers\TakeoffSheetController::class, 'edit'])->name('takeoff-sheets.edit');
        Route::match(['put', 'patch'], '/takeoff-sheets/{takeoff_sheet}', [App\Http\Controllers\TakeoffSheetController::class, 'update'])->name('takeoff-sheets.update');
        Route::post('/takeoff-sheets/{takeoff_sheet}/verify', [App\Http\Controllers\TakeoffSheetController::class, 'verify'])->name('takeoff-sheets.verify');
        Route::post('/takeoff-sheets/{takeoff_sheet}/approve', [App\Http\Controllers\TakeoffSheetController::class, 'approve'])->name('takeoff-sheets.approve');
        Route::post('/takeoff-sheets/{takeoff_sheet}/revert', [App\Http\Controllers\TakeoffSheetController::class, 'revertToDraft'])->name('takeoff-sheets.revert');
        Route::post('/takeoff-sheets/{takeoff_sheet}/revert-to-verified', [App\Http\Controllers\TakeoffSheetController::class, 'revertToVerified'])->name('takeoff-sheets.revert-to-verified');
    });
    Route::middleware('permission:takeoff-sheets.delete')->group(function () {
        Route::delete('/takeoff-sheets/{takeoff_sheet}', [App\Http\Controllers\TakeoffSheetController::class, 'destroy'])->name('takeoff-sheets.destroy');
    });
    Route::middleware('permission:takeoff-sheets.view')->group(function () {
        Route::get('/takeoff-sheets', [App\Http\Controllers\TakeoffSheetController::class, 'index'])->name('takeoff-sheets.index');
        Route::get('/takeoff-sheets/{takeoff_sheet}/print', [App\Http\Controllers\TakeoffSheetController::class, 'print'])->name('takeoff-sheets.print');
        Route::get('/takeoff-sheets/{takeoff_sheet}', [App\Http\Controllers\TakeoffSheetController::class, 'show'])->name('takeoff-sheets.show');
    });

    // 5. Contractual Follow-up Reports (OF/TNT/CAD/01)
    Route::middleware('permission:contractual-reports.export')->group(function () {
        Route::get('/contractual-reports/template/download', [App\Http\Controllers\ContractualReportController::class, 'downloadTemplate'])->name('contractual-reports.download-template');
        Route::get('/contractual-reports/{contractual_report}/excel', [App\Http\Controllers\ContractualReportController::class, 'exportExcel'])->name('contractual-reports.excel');
    });
    Route::middleware('permission:contractual-reports.create')->group(function () {
        Route::get('/contractual-reports/create', [App\Http\Controllers\ContractualReportController::class, 'create'])->name('contractual-reports.create');
        Route::post('/contractual-reports', [App\Http\Controllers\ContractualReportController::class, 'store'])->name('contractual-reports.store');
        Route::post('/contractual-reports/import', [App\Http\Controllers\ContractualReportController::class, 'importExcel'])->name('contractual-reports.import');
    });
    Route::middleware('permission:contractual-reports.edit')->group(function () {
        Route::get('/contractual-reports/{contractual_report}/edit', [App\Http\Controllers\ContractualReportController::class, 'edit'])->name('contractual-reports.edit');
        Route::match(['put', 'patch'], '/contractual-reports/{contractual_report}', [App\Http\Controllers\ContractualReportController::class, 'update'])->name('contractual-reports.update');
        Route::post('/contractual-reports/{contractual_report}/submit', [App\Http\Controllers\ContractualReportController::class, 'submit'])->name('contractual-reports.submit');
        Route::post('/contractual-reports/{contractual_report}/check', [App\Http\Controllers\ContractualReportController::class, 'check'])->name('contractual-reports.check');
        Route::post('/contractual-reports/{contractual_report}/approve', [App\Http\Controllers\ContractualReportController::class, 'approve'])->name('contractual-reports.approve');
        Route::post('/contractual-reports/{contractual_report}/reject', [App\Http\Controllers\ContractualReportController::class, 'reject'])->name('contractual-reports.reject');
        Route::post('/contractual-reports/{contractual_report}/revert', [App\Http\Controllers\ContractualReportController::class, 'revert'])->name('contractual-reports.revert');
    });
    Route::middleware('permission:contractual-reports.delete')->group(function () {
        Route::delete('/contractual-reports/{contractual_report}', [App\Http\Controllers\ContractualReportController::class, 'destroy'])->name('contractual-reports.destroy');
    });
    Route::middleware('permission:contractual-reports.view')->group(function () {
        Route::get('/contractual-reports', [App\Http\Controllers\ContractualReportController::class, 'index'])->name('contractual-reports.index');
        Route::get('/contractual-reports/{contractual_report}/print', [App\Http\Controllers\ContractualReportController::class, 'print'])->name('contractual-reports.print');
        Route::get('/contractual-reports/{contractual_report}', [App\Http\Controllers\ContractualReportController::class, 'show'])->name('contractual-reports.show');
    });

    // 6. Payment Certificates (IPCs)
    Route::middleware('permission:ipc.create')->group(function () {
        Route::get('/ipcs/create', [App\Http\Controllers\IpcController::class, 'create'])->name('ipcs.create');
        Route::post('/ipcs', [App\Http\Controllers\IpcController::class, 'store'])->name('ipcs.store');
        Route::post('/ipcs/{ipc}/prepare', [App\Http\Controllers\IpcController::class, 'prepare'])->name('ipcs.prepare');
    });
    Route::middleware('permission:ipc.edit')->group(function () {
        Route::get('/ipcs/{ipc}/edit', [App\Http\Controllers\IpcController::class, 'edit'])->name('ipcs.edit');
        Route::match(['put', 'patch'], '/ipcs/{ipc}', [App\Http\Controllers\IpcController::class, 'update'])->name('ipcs.update');
        Route::post('/ipcs/{ipc}/check', [App\Http\Controllers\IpcController::class, 'check'])->name('ipcs.check');
        Route::post('/ipcs/{ipc}/submit', [App\Http\Controllers\IpcController::class, 'submit'])->name('ipcs.submit');
    });
    Route::middleware('permission:ipc.approve')->group(function () {
        Route::post('/ipcs/{ipc}/approve', [App\Http\Controllers\IpcController::class, 'approve'])->name('ipcs.approve');
        Route::post('/ipcs/{ipc}/reject', [App\Http\Controllers\IpcController::class, 'reject'])->name('ipcs.reject');
        Route::post('/ipcs/{ipc}/mark-paid', [App\Http\Controllers\IpcController::class, 'markPaid'])->name('ipcs.mark-paid');
    });
    Route::middleware('permission:ipc.delete')->group(function () {
        Route::delete('/ipcs/{ipc}', [App\Http\Controllers\IpcController::class, 'destroy'])->name('ipcs.destroy');
    });
    Route::middleware('permission:ipc.view')->group(function () {
        Route::get('/ipcs', [App\Http\Controllers\IpcController::class, 'index'])->name('ipcs.index');
        Route::get('/ipcs/{ipc}/print', [App\Http\Controllers\IpcController::class, 'print'])->name('ipcs.print');
        Route::get('/ipcs/{ipc}/certificate', [App\Http\Controllers\IpcController::class, 'downloadCertificate'])->name('ipcs.certificate');
        Route::get('/ipcs/{ipc}', [App\Http\Controllers\IpcController::class, 'show'])->name('ipcs.show');
    });

    // 7. Material Deliveries
    Route::middleware('permission:deliveries.create')->group(function () {
        Route::get('/material-deliveries/create', [App\Http\Controllers\MaterialDeliveryController::class, 'create'])->name('material-deliveries.create');
        Route::post('/material-deliveries', [App\Http\Controllers\MaterialDeliveryController::class, 'store'])->name('material-deliveries.store');
    });
    Route::middleware('permission:deliveries.confirm')->group(function () {
        Route::post('/material-deliveries/{materialDelivery}/confirm', [App\Http\Controllers\MaterialDeliveryController::class, 'confirm'])->name('material-deliveries.confirm');
        Route::post('/material-deliveries/{materialDelivery}/revert', [App\Http\Controllers\MaterialDeliveryController::class, 'revertToRecorded'])->name('material-deliveries.revert');
    });
    Route::middleware('permission:deliveries.edit')->group(function () {
        Route::get('/material-deliveries/{materialDelivery}/edit', [App\Http\Controllers\MaterialDeliveryController::class, 'edit'])->name('material-deliveries.edit');
        Route::match(['put', 'patch'], '/material-deliveries/{materialDelivery}', [App\Http\Controllers\MaterialDeliveryController::class, 'update'])->name('material-deliveries.update');
    });
    Route::middleware('permission:deliveries.delete')->group(function () {
        Route::delete('/material-deliveries/{materialDelivery}', [App\Http\Controllers\MaterialDeliveryController::class, 'destroy'])->name('material-deliveries.destroy');
    });
    Route::middleware('permission:deliveries.view')->group(function () {
        Route::get('/material-deliveries', [App\Http\Controllers\MaterialDeliveryController::class, 'index'])->name('material-deliveries.index');
        Route::get('/material-deliveries/{materialDelivery}', [App\Http\Controllers\MaterialDeliveryController::class, 'show'])->name('material-deliveries.show');
    });

    // 8. Actual Costs
    Route::middleware('permission:actual-costs.create')->group(function () {
        Route::get('/actual-costs/create', [App\Http\Controllers\ActualCostController::class, 'create'])->name('actual-costs.create');
        Route::post('/actual-costs', [App\Http\Controllers\ActualCostController::class, 'store'])->name('actual-costs.store');
    });
    Route::middleware('permission:actual-costs.edit')->group(function () {
        Route::get('/actual-costs/{actual_cost}/edit', [App\Http\Controllers\ActualCostController::class, 'edit'])->name('actual-costs.edit');
        Route::match(['put', 'patch'], '/actual-costs/{actual_cost}', [App\Http\Controllers\ActualCostController::class, 'update'])->name('actual-costs.update');
    });
    Route::middleware('permission:actual-costs.delete')->group(function () {
        Route::delete('/actual-costs/{actual_cost}', [App\Http\Controllers\ActualCostController::class, 'destroy'])->name('actual-costs.destroy');
    });
    Route::middleware('permission:actual-costs.view')->group(function () {
        Route::get('/actual-costs', [App\Http\Controllers\ActualCostController::class, 'index'])->name('actual-costs.index');
        Route::get('/reports/variance', [App\Http\Controllers\ActualCostController::class, 'varianceReport'])->name('actual-costs.variance');
        Route::get('/actual-costs/{actual_cost}', [App\Http\Controllers\ActualCostController::class, 'show'])->name('actual-costs.show');
    });

    // 9. Gantt Timeline Schedule
    Route::middleware('permission:gantt.view')->group(function () {
        Route::get('/gantt', [App\Http\Controllers\GanttController::class, 'index'])->name('gantt.index');
        Route::get('/gantt/project/{project}', [App\Http\Controllers\GanttController::class, 'projectTimeline'])->name('gantt.project');
    });

    // 10. Subcontractors Management
    Route::middleware('permission:subcontractors.create')->group(function () {
        Route::get('/subcontractors/create', [App\Http\Controllers\SubcontractorController::class, 'create'])->name('subcontractors.create');
        Route::post('/subcontractors', [App\Http\Controllers\SubcontractorController::class, 'store'])->name('subcontractors.store');
    });
    Route::middleware('permission:subcontractors.edit')->group(function () {
        Route::get('/subcontractors/{subcontractor}/edit', [App\Http\Controllers\SubcontractorController::class, 'edit'])->name('subcontractors.edit');
        Route::match(['put', 'patch'], '/subcontractors/{subcontractor}', [App\Http\Controllers\SubcontractorController::class, 'update'])->name('subcontractors.update');
    });
    Route::middleware('permission:subcontractors.delete')->group(function () {
        Route::delete('/subcontractors/{subcontractor}', [App\Http\Controllers\SubcontractorController::class, 'destroy'])->name('subcontractors.destroy');
    });
    Route::middleware('permission:subcontractors.view')->group(function () {
        Route::get('/subcontractors', [App\Http\Controllers\SubcontractorController::class, 'index'])->name('subcontractors.index');
        Route::get('/subcontractors/{subcontractor}', [App\Http\Controllers\SubcontractorController::class, 'show'])->name('subcontractors.show');
    });

    // 11. Cost Categories
    Route::middleware('permission:cost-categories.create')->group(function () {
        Route::get('/cost-categories/create', [App\Http\Controllers\CostCategoryController::class, 'create'])->name('cost-categories.create');
        Route::post('/cost-categories', [App\Http\Controllers\CostCategoryController::class, 'store'])->name('cost-categories.store');
    });
    Route::middleware('permission:cost-categories.edit')->group(function () {
        Route::get('/cost-categories/{cost_category}/edit', [App\Http\Controllers\CostCategoryController::class, 'edit'])->name('cost-categories.edit');
        Route::match(['put', 'patch'], '/cost-categories/{cost_category}', [App\Http\Controllers\CostCategoryController::class, 'update'])->name('cost-categories.update');
    });
    Route::middleware('permission:cost-categories.delete')->group(function () {
        Route::delete('/cost-categories/{cost_category}', [App\Http\Controllers\CostCategoryController::class, 'destroy'])->name('cost-categories.destroy');
    });
    Route::middleware('permission:cost-categories.view')->group(function () {
        Route::get('/cost-categories', [App\Http\Controllers\CostCategoryController::class, 'index'])->name('cost-categories.index');
        Route::get('/cost-categories/{cost_category}', [App\Http\Controllers\CostCategoryController::class, 'show'])->name('cost-categories.show');
    });

    // 12. Equipment Fleet Registry (EAMD)
    Route::middleware('permission:equipment.create')->group(function () {
        Route::get('/equipment/create', [App\Http\Controllers\EquipmentController::class, 'create'])->name('equipment.create');
        Route::post('/equipment', [App\Http\Controllers\EquipmentController::class, 'store'])->name('equipment.store');
    });
    Route::middleware('permission:equipment.edit')->group(function () {
        Route::get('/equipment/{equipment}/edit', [App\Http\Controllers\EquipmentController::class, 'edit'])->name('equipment.edit');
        Route::match(['put', 'patch'], '/equipment/{equipment}', [App\Http\Controllers\EquipmentController::class, 'update'])->name('equipment.update');
        Route::patch('/equipment/{equipment}/reading', [App\Http\Controllers\EquipmentController::class, 'updateReading'])->name('equipment.update-reading');
    });
    Route::middleware('permission:equipment.delete')->group(function () {
        Route::delete('/equipment/{equipment}', [App\Http\Controllers\EquipmentController::class, 'destroy'])->name('equipment.destroy');
    });
    Route::middleware('permission:equipment.view')->group(function () {
        Route::get('/equipment', [App\Http\Controllers\EquipmentController::class, 'index'])->name('equipment.index');
        Route::get('/equipment/{equipment}', [App\Http\Controllers\EquipmentController::class, 'show'])->name('equipment.show');
    });

    // 13. Preventive Maintenance Schedule (OF/EAMD/074)
    Route::middleware('permission:pm-schedules.export')->group(function () {
        Route::get('/pm-schedules/template/download', [App\Http\Controllers\PmScheduleController::class, 'downloadTemplate'])->name('pm-schedules.download-template');
        Route::get('/pm-schedules/{pm_schedule}/export', [App\Http\Controllers\PmScheduleController::class, 'export'])->name('pm-schedules.export');
    });
    Route::middleware('permission:pm-schedules.create')->group(function () {
        Route::get('/pm-schedules/create', [App\Http\Controllers\PmScheduleController::class, 'create'])->name('pm-schedules.create');
        Route::post('/pm-schedules', [App\Http\Controllers\PmScheduleController::class, 'store'])->name('pm-schedules.store');
        Route::post('/pm-schedules/import', [App\Http\Controllers\PmScheduleController::class, 'import'])->name('pm-schedules.import');
    });
    Route::middleware('permission:pm-schedules.edit')->group(function () {
        Route::get('/pm-schedules/{pm_schedule}/edit', [App\Http\Controllers\PmScheduleController::class, 'edit'])->name('pm-schedules.edit');
        Route::match(['put', 'patch'], '/pm-schedules/{pm_schedule}', [App\Http\Controllers\PmScheduleController::class, 'update'])->name('pm-schedules.update');
        Route::post('/pm-schedules/{pm_schedule}/submit', [App\Http\Controllers\PmScheduleController::class, 'submit'])->name('pm-schedules.submit');
        Route::post('/pm-schedules/{pm_schedule}/check', [App\Http\Controllers\PmScheduleController::class, 'check'])->name('pm-schedules.check');
        Route::post('/pm-schedules/{pm_schedule}/approve', [App\Http\Controllers\PmScheduleController::class, 'approve'])->name('pm-schedules.approve');
        Route::post('/pm-schedules/{pm_schedule}/reject', [App\Http\Controllers\PmScheduleController::class, 'reject'])->name('pm-schedules.reject');
        Route::post('/pm-schedules/{pm_schedule}/revert', [App\Http\Controllers\PmScheduleController::class, 'revert'])->name('pm-schedules.revert');
    });
    Route::middleware('permission:pm-schedules.delete')->group(function () {
        Route::delete('/pm-schedules/{pm_schedule}', [App\Http\Controllers\PmScheduleController::class, 'destroy'])->name('pm-schedules.destroy');
    });
    Route::middleware('permission:pm-schedules.view')->group(function () {
        Route::get('/pm-schedules', [App\Http\Controllers\PmScheduleController::class, 'index'])->name('pm-schedules.index');
        Route::get('/pm-schedules/{pm_schedule}/print', [App\Http\Controllers\PmScheduleController::class, 'print'])->name('pm-schedules.print');
        Route::get('/pm-schedules/{pm_schedule}', [App\Http\Controllers\PmScheduleController::class, 'show'])->name('pm-schedules.show');
    });

    // 14. Equipment Breakdown & Maintenance Reports (OF/TNT/ EAMD/030)
    Route::middleware('permission:equipment-breakdowns.export')->group(function () {
        Route::get('/equipment-breakdowns/{equipment_breakdown}/export', [App\Http\Controllers\EquipmentBreakdownReportController::class, 'export'])->name('equipment-breakdowns.export');
    });
    Route::middleware('permission:equipment-breakdowns.create')->group(function () {
        Route::get('/equipment-breakdowns/create', [App\Http\Controllers\EquipmentBreakdownReportController::class, 'create'])->name('equipment-breakdowns.create');
        Route::post('/equipment-breakdowns', [App\Http\Controllers\EquipmentBreakdownReportController::class, 'store'])->name('equipment-breakdowns.store');
        Route::post('/equipment-breakdowns/import', [App\Http\Controllers\EquipmentBreakdownReportController::class, 'import'])->name('equipment-breakdowns.import');
    });
    Route::middleware('permission:equipment-breakdowns.edit')->group(function () {
        Route::get('/equipment-breakdowns/{equipment_breakdown}/edit', [App\Http\Controllers\EquipmentBreakdownReportController::class, 'edit'])->name('equipment-breakdowns.edit');
        Route::match(['put', 'patch'], '/equipment-breakdowns/{equipment_breakdown}', [App\Http\Controllers\EquipmentBreakdownReportController::class, 'update'])->name('equipment-breakdowns.update');
        Route::post('/equipment-breakdowns/{equipment_breakdown}/submit', [App\Http\Controllers\EquipmentBreakdownReportController::class, 'submit'])->name('equipment-breakdowns.submit');
        Route::post('/equipment-breakdowns/{equipment_breakdown}/check', [App\Http\Controllers\EquipmentBreakdownReportController::class, 'check'])->name('equipment-breakdowns.check');
        Route::post('/equipment-breakdowns/{equipment_breakdown}/approve', [App\Http\Controllers\EquipmentBreakdownReportController::class, 'approve'])->name('equipment-breakdowns.approve');
        Route::post('/equipment-breakdowns/{equipment_breakdown}/reject', [App\Http\Controllers\EquipmentBreakdownReportController::class, 'reject'])->name('equipment-breakdowns.reject');
        Route::post('/equipment-breakdowns/{equipment_breakdown}/revert', [App\Http\Controllers\EquipmentBreakdownReportController::class, 'revert'])->name('equipment-breakdowns.revert');
    });
    Route::middleware('permission:equipment-breakdowns.delete')->group(function () {
        Route::delete('/equipment-breakdowns/{equipment_breakdown}', [App\Http\Controllers\EquipmentBreakdownReportController::class, 'destroy'])->name('equipment-breakdowns.destroy');
    });
    Route::middleware('permission:equipment-breakdowns.view')->group(function () {
        Route::get('/equipment-breakdowns', [App\Http\Controllers\EquipmentBreakdownReportController::class, 'index'])->name('equipment-breakdowns.index');
        Route::get('/equipment-breakdowns/{equipment_breakdown}/print', [App\Http\Controllers\EquipmentBreakdownReportController::class, 'print'])->name('equipment-breakdowns.print');
        Route::get('/equipment-breakdowns/{equipment_breakdown}', [App\Http\Controllers\EquipmentBreakdownReportController::class, 'show'])->name('equipment-breakdowns.show');
    });

    // 15. Daily Equipment Maintenance Logs & Spare Parts Costs
    Route::middleware('permission:equipment-logs.export')->group(function () {
        Route::get('/equipment-logs/export', [App\Http\Controllers\EquipmentMaintenanceLogController::class, 'export'])->name('equipment-logs.export');
    });
    Route::middleware('permission:equipment-logs.create')->group(function () {
        Route::get('/equipment-logs/create', [App\Http\Controllers\EquipmentMaintenanceLogController::class, 'create'])->name('equipment-logs.create');
        Route::post('/equipment-logs', [App\Http\Controllers\EquipmentMaintenanceLogController::class, 'store'])->name('equipment-logs.store');
        Route::post('/equipment-logs/import', [App\Http\Controllers\EquipmentMaintenanceLogController::class, 'import'])->name('equipment-logs.import');
    });
    Route::middleware('permission:equipment-logs.edit')->group(function () {
        Route::get('/equipment-logs/{equipment_log}/edit', [App\Http\Controllers\EquipmentMaintenanceLogController::class, 'edit'])->name('equipment-logs.edit');
        Route::match(['put', 'patch'], '/equipment-logs/{equipment_log}', [App\Http\Controllers\EquipmentMaintenanceLogController::class, 'update'])->name('equipment-logs.update');
    });
    Route::middleware('permission:equipment-logs.delete')->group(function () {
        Route::delete('/equipment-logs/{equipment_log}', [App\Http\Controllers\EquipmentMaintenanceLogController::class, 'destroy'])->name('equipment-logs.destroy');
    });
    Route::middleware('permission:equipment-logs.view')->group(function () {
        Route::get('/equipment-logs', [App\Http\Controllers\EquipmentMaintenanceLogController::class, 'index'])->name('equipment-logs.index');
        Route::get('/equipment-logs/{equipment_log}', [App\Http\Controllers\EquipmentMaintenanceLogController::class, 'show'])->name('equipment-logs.show');
    });

    // 16. Reports & Analytics
    Route::middleware('permission:reports.view')->group(function () {
        Route::get('/reports/30-column', [App\Http\Controllers\ReportController::class, 'thirtyColumnReport'])->name('reports.30-column');
    });
    Route::middleware('permission:reports.export')->group(function () {
        Route::get('/reports/30-column/excel', [App\Http\Controllers\ReportController::class, 'exportExcel'])->name('reports.30-column.excel');
        Route::get('/reports/30-column/pdf', [App\Http\Controllers\ReportController::class, 'downloadPdf'])->name('reports.30-column.pdf');
    });

    // 17. Admin Management Routes
    Route::prefix('admin')->name('admin.')->group(function () {
        // Users Management (create defined before {user})
        Route::middleware('permission:users.create')->group(function () {
            Route::get('users/create', [App\Http\Controllers\Admin\UserController::class, 'create'])->name('users.create');
            Route::post('users', [App\Http\Controllers\Admin\UserController::class, 'store'])->name('users.store');
        });
        Route::middleware('permission:users.edit')->group(function () {
            Route::get('users/{user}/edit', [App\Http\Controllers\Admin\UserController::class, 'edit'])->name('users.edit');
            Route::match(['put', 'patch'], 'users/{user}', [App\Http\Controllers\Admin\UserController::class, 'update'])->name('users.update');
            Route::post('users/{user}/toggle-status', [App\Http\Controllers\Admin\UserController::class, 'toggleStatus'])->name('users.toggle-status');
            Route::get('users/{user}/permissions', [App\Http\Controllers\Admin\UserController::class, 'permissions'])->name('users.permissions');
            Route::put('users/{user}/permissions', [App\Http\Controllers\Admin\UserController::class, 'updatePermissions'])->name('users.permissions.update');
        });
        Route::middleware('permission:users.delete')->group(function () {
            Route::delete('users/{user}', [App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('users.destroy');
        });
        Route::middleware('permission:users.view')->group(function () {
            Route::get('users', [App\Http\Controllers\Admin\UserController::class, 'index'])->name('users.index');
            Route::get('users/{user}', [App\Http\Controllers\Admin\UserController::class, 'show'])->name('users.show');
        });

        // Roles Management (create defined before {role})
        Route::middleware('permission:roles.create')->group(function () {
            Route::get('roles/create', [App\Http\Controllers\Admin\RoleController::class, 'create'])->name('roles.create');
            Route::post('roles', [App\Http\Controllers\Admin\RoleController::class, 'store'])->name('roles.store');
        });
        Route::middleware('permission:roles.edit')->group(function () {
            Route::get('roles/{role}/edit', [App\Http\Controllers\Admin\RoleController::class, 'edit'])->name('roles.edit');
            Route::match(['put', 'patch'], 'roles/{role}', [App\Http\Controllers\Admin\RoleController::class, 'update'])->name('roles.update');
        });
        Route::middleware('permission:roles.delete')->group(function () {
            Route::delete('roles/{role}', [App\Http\Controllers\Admin\RoleController::class, 'destroy'])->name('roles.destroy');
        });
        Route::middleware('permission:roles.view')->group(function () {
            Route::get('roles', [App\Http\Controllers\Admin\RoleController::class, 'index'])->name('roles.index');
            Route::get('roles/{role}', [App\Http\Controllers\Admin\RoleController::class, 'show'])->name('roles.show');
        });

        // Workflow Permissions Matrix
        Route::middleware('permission:workflow.manage')->group(function () {
            Route::get('/workflow', [App\Http\Controllers\Admin\WorkflowPermissionController::class, 'index'])->name('workflow.index');
            Route::put('/workflow', [App\Http\Controllers\Admin\WorkflowPermissionController::class, 'update'])->name('workflow.update');
            Route::post('/workflow/preset', [App\Http\Controllers\Admin\WorkflowPermissionController::class, 'applyPreset'])->name('workflow.preset');
        });

        // Company & Tax Settings
        Route::middleware('permission:settings.manage')->group(function () {
            Route::get('/company-settings', [App\Http\Controllers\Admin\CompanySettingsController::class, 'index'])->name('company-settings.index');
            Route::put('/company-settings', [App\Http\Controllers\Admin\CompanySettingsController::class, 'update'])->name('company-settings.update');
            Route::get('/company-settings/remove-logo', [App\Http\Controllers\Admin\CompanySettingsController::class, 'removeLogo'])->name('company-settings.remove-logo');
            Route::get('/settings', [App\Http\Controllers\Admin\SettingsController::class, 'index'])->name('settings.index');
            Route::put('/settings', [App\Http\Controllers\Admin\SettingsController::class, 'update'])->name('settings.update');
        });

        // Database Management & Backups
        Route::middleware('permission:database.backup')->group(function () {
            Route::get('/database-backups', [App\Http\Controllers\Admin\DatabaseBackupController::class, 'index'])->name('database-backups.index');
            Route::post('/database-backups', [App\Http\Controllers\Admin\DatabaseBackupController::class, 'createBackup'])->name('database-backups.create');
            Route::post('/database-backups/run-daily', [App\Http\Controllers\Admin\DatabaseBackupController::class, 'runDailySync'])->name('database-backups.run-daily');
            Route::get('/database-backups/download/{filename}', [App\Http\Controllers\Admin\DatabaseBackupController::class, 'download'])->name('database-backups.download');
            Route::delete('/database-backups/{filename}', [App\Http\Controllers\Admin\DatabaseBackupController::class, 'destroy'])->name('database-backups.destroy');

            // Smart Database Import & Synchronization (Preserve Existing Data)
            Route::get('/database-sync', [App\Http\Controllers\Admin\SmartDatabaseSyncController::class, 'index'])->name('database-sync.index');
            Route::post('/database-sync/upload', [App\Http\Controllers\Admin\SmartDatabaseSyncController::class, 'upload'])->name('database-sync.upload');
            Route::get('/database-sync/preview/{batchId}', [App\Http\Controllers\Admin\SmartDatabaseSyncController::class, 'preview'])->name('database-sync.preview');
            Route::post('/database-sync/apply/{batchId}', [App\Http\Controllers\Admin\SmartDatabaseSyncController::class, 'apply'])->name('database-sync.apply');
            Route::post('/database-sync/rollback/{batchId}', [App\Http\Controllers\Admin\SmartDatabaseSyncController::class, 'rollback'])->name('database-sync.rollback');
        });

        // Audit Trail
        Route::middleware('permission:audit.view')->group(function () {
            Route::get('/audit', function () { return view('admin.audit.index'); })->name('audit.index');
        });
    });
});

// Fallback
Route::fallback(function () {
    return response()->view('errors.403', [], 403);
});
