<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\SubcontractorController;
use App\Http\Controllers\BoqItemController;
use App\Http\Controllers\CostCategoryController;
use App\Http\Controllers\IpcController;
use App\Http\Controllers\ReportController;

// ==========================================
// PUBLIC ROUTES
// ==========================================
Route::get('/', [ProjectController::class, 'dashboard'])->name('dashboard');

Route::get('/login', function() {
    return view('auth.login');
})->name('login');

Route::post('/login', function(Request $request) {
    $credentials = $request->only('email', 'password');
    if (Auth::attempt($credentials)) {
        $user = Auth::user();
        $user->update(['last_login_at' => now(), 'last_login_ip' => $request->ip()]);
        return redirect()->intended('/');
    }
    return back()->withErrors(['email' => 'Invalid credentials.'])->withInput();
})->name('login.submit');

Route::post('/logout', function() {
    Auth::logout();
    return redirect('/');
})->name('logout');

// ==========================================
// ALL RESOURCE ROUTES (Authenticated)
// ==========================================
Route::middleware(['auth'])->group(function () {
    
    // Projects - Full Resource
    Route::resource('projects', ProjectController::class);
    
    // Subcontractors - Full Resource
    Route::resource('subcontractors', SubcontractorController::class);
    
    // Cost Categories - Full Resource
    Route::resource('cost-categories', CostCategoryController::class);
    
    // BOQ Items - Full Resource
    Route::resource('boq-items', BoqItemController::class);
    Route::get('/api/boq-items/project/{project}', [BoqItemController::class, 'getByProject']);
    
    // IPCs - Full Resource
    Route::resource('ipcs', IpcController::class);
    Route::get('/ipcs/{ipc}/print', [IpcController::class, 'print'])->name('ipcs.print');
    Route::post('/ipcs/{ipc}/approve', [IpcController::class, 'approve'])->name('ipcs.approve');
    
    // Reports
    Route::get('/reports/30-column', [ReportController::class, 'thirtyColumnReport'])->name('reports.30-column');
    Route::get('/reports/30-column/excel', [ReportController::class, 'exportExcel'])->name('reports.30-column.excel');
    Route::get('/reports/30-column/pdf', [ReportController::class, 'downloadPdf'])->name('reports.30-column.pdf');
    
    // ==========================================
    // ADMIN ROUTES
    // ==========================================
    Route::prefix('admin')->name('admin.')->group(function () {
        
        // Profile
        Route::get('/profile', [\App\Http\Controllers\Admin\UserController::class, 'profile'])->name('profile');
        Route::put('/profile', [\App\Http\Controllers\Admin\UserController::class, 'updateProfile'])->name('profile.update');
        Route::put('/profile/password', [\App\Http\Controllers\Admin\UserController::class, 'updatePassword'])->name('profile.password');
        
        // Users - Full Resource
        Route::resource('users', \App\Http\Controllers\Admin\UserController::class);
        Route::post('users/{user}/toggle-status', [\App\Http\Controllers\Admin\UserController::class, 'toggleStatus'])->name('users.toggle-status');
        
        
        // Settings
        Route::get('/settings', [\App\Http\Controllers\Admin\SettingsController::class, 'index'])->name('settings.index');
        Route::put('/settings', [\App\Http\Controllers\Admin\SettingsController::class, 'update'])->name('settings.update');
        Route::post('/settings/{setting}/toggle', [\App\Http\Controllers\Admin\SettingsController::class, 'toggleStatus'])->name('settings.toggle');
        // Roles - Full Resource
        Route::resource('roles', \App\Http\Controllers\Admin\RoleController::class);
    });
});

// Actual Costs
Route::resource('actual-costs', \App\Http\Controllers\ActualCostController::class);
Route::get('/reports/variance', [\App\Http\Controllers\ActualCostController::class, 'varianceReport'])->name('actual-costs.variance');

// Documents
Route::post('/documents/upload', [\App\Http\Controllers\DocumentController::class, 'upload'])->name('documents.upload');
Route::get('/documents/{document}/download', [\App\Http\Controllers\DocumentController::class, 'download'])->name('documents.download');
Route::delete('/documents/{document}', [\App\Http\Controllers\DocumentController::class, 'destroy'])->name('documents.destroy');

// Notifications
Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
Route::get('/notifications/{notification}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.read');
Route::post('/notifications/mark-all-read', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
Route::get('/api/notifications/count', [\App\Http\Controllers\NotificationController::class, 'getUnreadCount']);
Route::get('/api/notifications/recent', [\App\Http\Controllers\NotificationController::class, 'getRecent']);

// Gantt Chart
Route::get('/gantt', [\App\Http\Controllers\GanttController::class, 'index'])->name('gantt.index');
Route::get('/gantt/project/{project}', [\App\Http\Controllers\GanttController::class, 'projectTimeline'])->name('gantt.project');
Route::get('/ipcs/{ipc}/certificate', [\App\Http\Controllers\IpcController::class, 'downloadCertificate'])->name('ipcs.certificate');

// IPC Approval Workflow
Route::post('/ipcs/{ipc}/prepare', [\App\Http\Controllers\IpcController::class, 'prepare'])->name('ipcs.prepare');
Route::post('/ipcs/{ipc}/check', [\App\Http\Controllers\IpcController::class, 'check'])->name('ipcs.check');
Route::post('/ipcs/{ipc}/submit', [\App\Http\Controllers\IpcController::class, 'submit'])->name('ipcs.submit');
Route::post('/ipcs/{ipc}/approve', [\App\Http\Controllers\IpcController::class, 'approve'])->name('ipcs.approve');
Route::post('/ipcs/{ipc}/mark-paid', [\App\Http\Controllers\IpcController::class, 'markPaid'])->name('ipcs.mark-paid');

// Project Subcontractors
Route::get('/projects/{project}/subcontractors', [\App\Http\Controllers\ProjectSubcontractorController::class, 'manage'])->name('projects.subcontractors');
Route::post('/projects/{project}/subcontractors/assign', [\App\Http\Controllers\ProjectSubcontractorController::class, 'assign'])->name('projects.subcontractors.assign');
Route::delete('/projects/{project}/subcontractors/{subcontractor}', [\App\Http\Controllers\ProjectSubcontractorController::class, 'remove'])->name('projects.subcontractors.remove');
Route::put('/projects/{project}/subcontractors/{subcontractor}', [\App\Http\Controllers\ProjectSubcontractorController::class, 'updateContract'])->name('projects.subcontractors.update');
Route::post('/ipcs/{ipc}/reject', [\App\Http\Controllers\IpcController::class, 'reject'])->name('ipcs.reject');

// Settings
Route::get('/admin/settings', [\App\Http\Controllers\Admin\SettingsController::class, 'index'])->name('admin.settings.index');
Route::put('/admin/settings', [\App\Http\Controllers\Admin\SettingsController::class, 'update'])->name('admin.settings.update');

// Workflow Permissions
Route::get('/admin/workflow', [\App\Http\Controllers\Admin\WorkflowPermissionController::class, 'index'])->name('admin.workflow.index');
Route::put('/admin/workflow', [\App\Http\Controllers\Admin\WorkflowPermissionController::class, 'update'])->name('admin.workflow.update');
Route::post('/admin/workflow/preset', [\App\Http\Controllers\Admin\WorkflowPermissionController::class, 'applyPreset'])->name('admin.workflow.preset');

// Project Team Management
Route::get('/projects/{project}/team', [\App\Http\Controllers\TeamController::class, 'manage'])->name('projects.team');
Route::post('/projects/{project}/team/assign', [\App\Http\Controllers\TeamController::class, 'assign'])->name('projects.team.assign');
Route::delete('/projects/{project}/team/{user}', [\App\Http\Controllers\TeamController::class, 'remove'])->name('projects.team.remove');
Route::put('/projects/{project}/team/{user}', [\App\Http\Controllers\TeamController::class, 'update'])->name('projects.team.update');

// Module permission protected routes
Route::middleware(['auth', 'module:projects.view'])->group(function () {
    Route::get('/projects', [\App\Http\Controllers\ProjectController::class, 'index'])->name('projects.index');
    Route::get('/projects/{project}', [\App\Http\Controllers\ProjectController::class, 'show'])->name('projects.show');
});

Route::middleware(['auth', 'module:boq.view'])->group(function () {
    Route::get('/boq-items', [\App\Http\Controllers\BoqItemController::class, 'index'])->name('boq-items.index');
    Route::get('/boq-items/{boqItem}', [\App\Http\Controllers\BoqItemController::class, 'show'])->name('boq-items.show');
});

Route::middleware(['auth', 'module:ipc.view'])->group(function () {
    Route::get('/ipcs', [\App\Http\Controllers\IpcController::class, 'index'])->name('ipcs.index');
    Route::get('/ipcs/{ipc}', [\App\Http\Controllers\IpcController::class, 'show'])->name('ipcs.show');
});

// Audit Trail
Route::get('/admin/audit', function () {
    return view('admin.audit.index');
})->name('admin.audit.index')->middleware('auth');

// Material Deliveries
Route::resource('material-deliveries', \App\Http\Controllers\MaterialDeliveryController::class);

// Quantity Takeoffs
Route::resource('quantity-takeoffs', \App\Http\Controllers\QuantityTakeoffController::class);
Route::post('quantity-takeoffs/{quantityTakeoff}/verify', [\App\Http\Controllers\QuantityTakeoffController::class, 'verify'])->name('quantity-takeoffs.verify');
Route::post('quantity-takeoffs/{quantityTakeoff}/approve', [\App\Http\Controllers\QuantityTakeoffController::class, 'approve'])->name('quantity-takeoffs.approve');

// Company Settings
Route::get('/admin/company-settings', [\App\Http\Controllers\Admin\CompanySettingsController::class, 'index'])->name('admin.company-settings.index');
Route::put('/admin/company-settings', [\App\Http\Controllers\Admin\CompanySettingsController::class, 'update'])->name('admin.company-settings.update');
Route::get('/admin/company-settings/remove-logo', [\App\Http\Controllers\Admin\CompanySettingsController::class, 'removeLogo'])->name('admin.company-settings.remove-logo');

// Take-Off Workflow
Route::post('quantity-takeoffs/{quantityTakeoff}/revert', [\App\Http\Controllers\QuantityTakeoffController::class, 'revertToDraft'])->name('quantity-takeoffs.revert');

// Material Delivery Workflow
Route::post('material-deliveries/{materialDelivery}/confirm', [\App\Http\Controllers\MaterialDeliveryController::class, 'confirm'])->name('material-deliveries.confirm');
Route::post('material-deliveries/{materialDelivery}/revert', [\App\Http\Controllers\MaterialDeliveryController::class, 'revertToRecorded'])->name('material-deliveries.revert');

// Notification delete
Route::delete('/notifications/{notification}', [\App\Http\Controllers\NotificationController::class, 'delete'])->name('notifications.delete');
Route::delete('/notifications/delete-all-read', [\App\Http\Controllers\NotificationController::class, 'deleteAllRead'])->name('notifications.delete-all-read');
