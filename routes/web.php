<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\SubcontractorController;
use App\Http\Controllers\BoqItemController;
use App\Http\Controllers\CostCategoryController;
use App\Http\Controllers\IpcController;

Route::get('/', [ProjectController::class, 'dashboard'])->name('dashboard');

// Project routes
Route::resource('projects', ProjectController::class);

// Subcontractor routes
Route::resource('subcontractors', SubcontractorController::class);

// Cost Category routes
Route::resource('cost-categories', CostCategoryController::class);

// BOQ Items routes
Route::resource('boq-items', BoqItemController::class);

// IPC routes
Route::resource('ipcs', IpcController::class);

// Report Routes
Route::get('/reports/30-column', [App\Http\Controllers\ReportController::class, 'thirtyColumnReport'])->name('reports.30-column');
Route::get('/reports/30-column/pdf', [App\Http\Controllers\ReportController::class, 'downloadPdf'])->name('reports.30-column.pdf');
