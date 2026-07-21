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
    
    // Projects
    Route::resource('projects', App\Http\Controllers\ProjectController::class);
    Route::get('/projects/{project}/subcontractors', [App\Http\Controllers\ProjectSubcontractorController::class, 'manage'])->name('projects.subcontractors');
    Route::post('/projects/{project}/subcontractors/assign', [App\Http\Controllers\ProjectSubcontractorController::class, 'assign'])->name('projects.subcontractors.assign');
    Route::delete('/projects/{project}/subcontractors/{subcontractor}', [App\Http\Controllers\ProjectSubcontractorController::class, 'remove'])->name('projects.subcontractors.remove');
    Route::put('/projects/{project}/subcontractors/{subcontractor}', [App\Http\Controllers\ProjectSubcontractorController::class, 'updateContract'])->name('projects.subcontractors.update');
    Route::get('/projects/{project}/team', [App\Http\Controllers\TeamController::class, 'manage'])->name('projects.team');
    Route::post('/projects/{project}/team/assign', [App\Http\Controllers\TeamController::class, 'assign'])->name('projects.team.assign');
    Route::delete('/projects/{project}/team/{user}', [App\Http\Controllers\TeamController::class, 'remove'])->name('projects.team.remove');
    Route::put('/projects/{project}/team/{user}', [App\Http\Controllers\TeamController::class, 'update'])->name('projects.team.update');
    
    // BOQ Items
    Route::resource('boq-items', App\Http\Controllers\BoqItemController::class);
    Route::get('/api/boq-items/project/{project}', [App\Http\Controllers\BoqItemController::class, 'getByProject']);
    
    // IPCs
    Route::resource('ipcs', App\Http\Controllers\IpcController::class);
    Route::get('/ipcs/{ipc}/print', [App\Http\Controllers\IpcController::class, 'print'])->name('ipcs.print');
    Route::get('/ipcs/{ipc}/certificate', [App\Http\Controllers\IpcController::class, 'downloadCertificate'])->name('ipcs.certificate');
    Route::post('/ipcs/{ipc}/prepare', [App\Http\Controllers\IpcController::class, 'prepare'])->name('ipcs.prepare');
    Route::post('/ipcs/{ipc}/check', [App\Http\Controllers\IpcController::class, 'check'])->name('ipcs.check');
    Route::post('/ipcs/{ipc}/submit', [App\Http\Controllers\IpcController::class, 'submit'])->name('ipcs.submit');
    Route::post('/ipcs/{ipc}/approve', [App\Http\Controllers\IpcController::class, 'approve'])->name('ipcs.approve');
    Route::post('/ipcs/{ipc}/reject', [App\Http\Controllers\IpcController::class, 'reject'])->name('ipcs.reject');
    Route::post('/ipcs/{ipc}/mark-paid', [App\Http\Controllers\IpcController::class, 'markPaid'])->name('ipcs.mark-paid');
    
    // Subcontractors
    Route::resource('subcontractors', App\Http\Controllers\SubcontractorController::class);
    
    // Cost Categories
    Route::resource('cost-categories', App\Http\Controllers\CostCategoryController::class);
    
    // Actual Costs
    Route::resource('actual-costs', App\Http\Controllers\ActualCostController::class);
    Route::get('/reports/variance', [App\Http\Controllers\ActualCostController::class, 'varianceReport'])->name('actual-costs.variance');
    
    // Quantity Take-Offs
    Route::resource('quantity-takeoffs', App\Http\Controllers\QuantityTakeoffController::class);
    Route::post('/quantity-takeoffs/store-multiple', [App\Http\Controllers\QuantityTakeoffController::class, 'storeMultiple'])->name('quantity-takeoffs.store-multiple');
    Route::post('/quantity-takeoffs/{quantityTakeoff}/verify', [App\Http\Controllers\QuantityTakeoffController::class, 'verify'])->name('quantity-takeoffs.verify');
    Route::post('/quantity-takeoffs/{quantityTakeoff}/approve', [App\Http\Controllers\QuantityTakeoffController::class, 'approve'])->name('quantity-takeoffs.approve');
    Route::post('/quantity-takeoffs/{quantityTakeoff}/revert', [App\Http\Controllers\QuantityTakeoffController::class, 'revertToDraft'])->name('quantity-takeoffs.revert');
    
    // Takeoff Sheets
    Route::resource('takeoff-sheets', App\Http\Controllers\TakeoffSheetController::class);
    Route::get('/takeoff-sheets/{takeoff_sheet}/print', [App\Http\Controllers\TakeoffSheetController::class, 'print'])->name('takeoff-sheets.print');
    Route::post('/takeoff-sheets/{takeoff_sheet}/verify', [App\Http\Controllers\TakeoffSheetController::class, 'verify'])->name('takeoff-sheets.verify');
    Route::post('/takeoff-sheets/{takeoff_sheet}/approve', [App\Http\Controllers\TakeoffSheetController::class, 'approve'])->name('takeoff-sheets.approve');
    Route::post('/takeoff-sheets/{takeoff_sheet}/revert', [App\Http\Controllers\TakeoffSheetController::class, 'revertToDraft'])->name('takeoff-sheets.revert');
    Route::post('/takeoff-sheets/{takeoff_sheet}/revert-to-verified', [AppHttpControllersTakeoffSheetController::class, 'revertToVerified'])->name('takeoff-sheets.revert-to-verified');
    Route::post('/takeoff-sheets/{takeoff_sheet}/revert-to-verified', [AppHttpControllersTakeoffSheetController::class, 'revertToVerified'])->name('takeoff-sheets.revert-to-verified');
    Route::post('/takeoff-sheets/{takeoff_sheet}/revert-to-verified', [AppHttpControllersTakeoffSheetController::class, 'revertToVerified'])->name('takeoff-sheets.revert-to-verified');
    
    // Material Deliveries
    Route::resource('material-deliveries', App\Http\Controllers\MaterialDeliveryController::class);
    Route::post('/material-deliveries/{materialDelivery}/confirm', [App\Http\Controllers\MaterialDeliveryController::class, 'confirm'])->name('material-deliveries.confirm');
    Route::post('/material-deliveries/{materialDelivery}/revert', [App\Http\Controllers\MaterialDeliveryController::class, 'revertToRecorded'])->name('material-deliveries.revert');
    
    // Gantt Chart
    Route::get('/gantt', [App\Http\Controllers\GanttController::class, 'index'])->name('gantt.index');
    Route::get('/gantt/project/{project}', [App\Http\Controllers\GanttController::class, 'projectTimeline'])->name('gantt.project');
    
    // Reports
    Route::get('/reports/30-column', [App\Http\Controllers\ReportController::class, 'thirtyColumnReport'])->name('reports.30-column');
    Route::get('/reports/30-column/excel', [App\Http\Controllers\ReportController::class, 'exportExcel'])->name('reports.30-column.excel');
    Route::get('/reports/30-column/pdf', [App\Http\Controllers\ReportController::class, 'downloadPdf'])->name('reports.30-column.pdf');
    
    // Documents
    Route::post('/documents/upload', [App\Http\Controllers\DocumentController::class, 'upload'])->name('documents.upload');
    Route::get('/documents/{document}/download', [App\Http\Controllers\DocumentController::class, 'download'])->name('documents.download');
    Route::delete('/documents/{document}', [App\Http\Controllers\DocumentController::class, 'destroy'])->name('documents.destroy');
    
    // Notifications
    Route::get('/notifications', [App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/{notification}/read', [App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/mark-all-read', [App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::delete('/notifications/{notification}', [App\Http\Controllers\NotificationController::class, 'delete'])->name('notifications.delete');
    Route::delete('/notifications/delete-all-read', [App\Http\Controllers\NotificationController::class, 'deleteAllRead'])->name('notifications.delete-all-read');
    
    // Admin Routes
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::resource('users', App\Http\Controllers\Admin\UserController::class);
        Route::post('users/{user}/toggle-status', [App\Http\Controllers\Admin\UserController::class, 'toggleStatus'])->name('users.toggle-status');
        Route::get('/profile', [App\Http\Controllers\Admin\UserController::class, 'profile'])->name('profile');
        Route::put('/profile', [App\Http\Controllers\Admin\UserController::class, 'updateProfile'])->name('profile.update');
        Route::put('/profile/password', [App\Http\Controllers\Admin\UserController::class, 'updatePassword'])->name('profile.password');
        Route::resource('roles', App\Http\Controllers\Admin\RoleController::class);
        
        // Workflow Permissions
        Route::get('/workflow', [App\Http\Controllers\Admin\WorkflowPermissionController::class, 'index'])->name('workflow.index');
        Route::put('/workflow', [App\Http\Controllers\Admin\WorkflowPermissionController::class, 'update'])->name('workflow.update');
        Route::post('/workflow/preset', [App\Http\Controllers\Admin\WorkflowPermissionController::class, 'applyPreset'])->name('workflow.preset');
        
        Route::get('/company-settings', [App\Http\Controllers\Admin\CompanySettingsController::class, 'index'])->name('company-settings.index');
        Route::put('/company-settings', [App\Http\Controllers\Admin\CompanySettingsController::class, 'update'])->name('company-settings.update');
        Route::get('/company-settings/remove-logo', [App\Http\Controllers\Admin\CompanySettingsController::class, 'removeLogo'])->name('company-settings.remove-logo');
        Route::get('/settings', [App\Http\Controllers\Admin\SettingsController::class, 'index'])->name('settings.index');
        Route::put('/settings', [App\Http\Controllers\Admin\SettingsController::class, 'update'])->name('settings.update');
        Route::get('/audit', function () { return view('admin.audit.index'); })->name('audit.index');
    });
});

// Fallback
Route::fallback(function () {
    return response()->view('errors.403', [], 403);
});
