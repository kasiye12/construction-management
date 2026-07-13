#!/bin/bash

echo "🏗️ Finalizing Construction Management System..."
echo "==============================================="

# 1. Create User Authentication System
cat > database/migrations/2024_01_01_000011_add_fields_to_users_table.php << 'MIGRATION'
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('engineer'); // admin, engineer, manager, viewer
            $table->string('department')->nullable();
            $table->string('phone')->nullable();
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'department', 'phone', 'is_active']);
        });
    }
};
MIGRATION

# 2. Create Activity Log for Audit Trail
cat > database/migrations/2024_01_01_000012_create_activity_logs_table.php << 'MIGRATION'
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('action');
            $table->string('module');
            $table->text('description')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('activity_logs');
    }
};
MIGRATION

# 3. Create Settings Table
cat > database/migrations/2024_01_01_000013_create_settings_table.php << 'MIGRATION'
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('group')->default('general');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('settings');
    }
};
MIGRATION

# 4. Create comprehensive Dashboard
cat > app/Http/Controllers/DashboardController.php << 'PHP'
<?php
namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Subcontractor;
use App\Models\BoqItem;
use App\Models\Ipc;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Project Statistics
        $totalProjects = Project::count();
        $activeProjects = Project::where('status', 'active')->count();
        $completedProjects = Project::where('status', 'completed')->count();
        $totalContractValue = Project::sum('contract_amount');
        
        // Financial Overview
        $totalRevenue = BoqItem::sum('revenue_amount');
        $totalBudget = DB::table('boq_items')
            ->selectRaw('SUM((SELECT COALESCE(SUM(amount),0) FROM labor_resources WHERE boq_item_id = boq_items.id) + 
                          (SELECT COALESCE(SUM(amount),0) FROM material_resources WHERE boq_item_id = boq_items.id) + 
                          (SELECT COALESCE(SUM(amount),0) FROM equipment_resources WHERE boq_item_id = boq_items.id)) as total_budget')
            ->value('total_budget') ?? 0;
        
        $profitLoss = $totalRevenue - $totalBudget;
        $profitMargin = $totalRevenue > 0 ? ($profitLoss / $totalRevenue) * 100 : 0;
        
        // IPC Statistics
        $totalIpcs = Ipc::count();
        $pendingIpcs = Ipc::where('status', 'submitted')->count();
        $approvedIpcs = Ipc::where('status', 'approved')->count();
        $totalPaidAmount = Ipc::where('status', 'paid')->sum('net_payment_amount');
        
        // Recent Activities
        $recentProjects = Project::latest()->take(5)->get();
        $recentIpcs = Ipc::with(['project', 'subcontractor'])
                         ->latest()
                         ->take(5)
                         ->get();
        
        // Monthly Revenue (Last 12 months)
        $monthlyRevenue = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthlyRevenue[] = [
                'month' => $date->format('M Y'),
                'amount' => Ipc::whereYear('ipc_date', $date->year)
                               ->whereMonth('ipc_date', $date->month)
                               ->sum('net_payment_amount')
            ];
        }
        
        // Project Status Distribution
        $projectStatusData = [
            'active' => Project::where('status', 'active')->count(),
            'completed' => Project::where('status', 'completed')->count(),
            'on_hold' => Project::where('status', 'on_hold')->count(),
            'cancelled' => Project::where('status', 'cancelled')->count(),
        ];
        
        // Cost Category Summary
        $costCategorySummary = DB::table('cost_categories')
            ->leftJoin('boq_items', 'cost_categories.id', '=', 'boq_items.cost_category_id')
            ->select('cost_categories.name', DB::raw('SUM(boq_items.revenue_amount) as total'))
            ->groupBy('cost_categories.id', 'cost_categories.name')
            ->get();
        
        // Subcontractor Performance
        $subcontractorPerformance = Ipc::with('subcontractor')
            ->select('subcontractor_id', DB::raw('SUM(net_payment_amount) as total_paid'))
            ->groupBy('subcontractor_id')
            ->orderByDesc('total_paid')
            ->take(5)
            ->get();
        
        return view('dashboard', compact(
            'totalProjects', 'activeProjects', 'completedProjects', 'totalContractValue',
            'totalRevenue', 'totalBudget', 'profitLoss', 'profitMargin',
            'totalIpcs', 'pendingIpcs', 'approvedIpcs', 'totalPaidAmount',
            'recentProjects', 'recentIpcs',
            'monthlyRevenue', 'projectStatusData', 'costCategorySummary',
            'subcontractorPerformance'
        ));
    }
    
    public function getChartData()
    {
        // Monthly revenue data for charts
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $data['labels'][] = $date->format('M');
            $data['revenue'][] = Ipc::whereYear('ipc_date', $date->year)
                                   ->whereMonth('ipc_date', $date->month)
                                   ->sum('net_payment_amount');
            $data['budget'][] = BoqItem::whereYear('created_at', $date->year)
                                      ->whereMonth('created_at', $date->month)
                                      ->sum('revenue_amount');
        }
        
        return response()->json($data);
    }
}
PHP

# 5. Create enhanced Dashboard view with charts
cat > resources/views/dashboard.blade.php << 'VIEW'
@extends('layouts.app')

@section('title', 'Dashboard - Construction Management')

@push('styles')
<style>
    .stat-card {
        border-radius: 15px;
        padding: 25px;
        transition: all 0.3s ease;
        border: none;
        box-shadow: 0 2px 15px rgba(0,0,0,0.08);
    }
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 25px rgba(0,0,0,0.15);
    }
    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
    }
    .progress-ring {
        width: 80px;
        height: 80px;
    }
    .chart-container {
        position: relative;
        height: 300px;
    }
    .quick-action {
        padding: 15px;
        border-radius: 10px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        transition: all 0.3s ease;
    }
    .quick-action:hover {
        transform: scale(1.05);
        color: white;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Welcome Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="fw-bold">📊 Construction Management Dashboard</h2>
                    <p class="text-muted">Welcome back! Here's your project overview for {{ Carbon\Carbon::now()->format('F Y') }}</p>
                </div>
                <div>
                    <a href="{{ route('projects.create') }}" class="btn btn-primary btn-lg">
                        <i class="fas fa-plus me-2"></i>New Project
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Key Metrics -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card bg-primary text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50">ACTIVE PROJECTS</h6>
                        <h2 class="mb-0">{{ $activeProjects }}</h2>
                        <small>of {{ $totalProjects }} total</small>
                    </div>
                    <div class="stat-icon bg-white bg-opacity-25">
                        <i class="fas fa-hard-hat"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card bg-success text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50">TOTAL REVENUE</h6>
                        <h2 class="mb-0">{{ number_format($totalRevenue) }} ETB</h2>
                        <small>Contract value</small>
                    </div>
                    <div class="stat-icon bg-white bg-opacity-25">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card bg-warning text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50">PROFIT/LOSS</h6>
                        <h2 class="mb-0">{{ number_format($profitLoss) }} ETB</h2>
                        <small>{{ number_format($profitMargin, 1) }}% margin</small>
                    </div>
                    <div class="stat-icon bg-white bg-opacity-25">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card bg-info text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50">PENDING IPCs</h6>
                        <h2 class="mb-0">{{ $pendingIpcs }}</h2>
                        <small>{{ $approvedIpcs }} approved</small>
                    </div>
                    <div class="stat-icon bg-white bg-opacity-25">
                        <i class="fas fa-file-invoice"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card stat-card">
                <h5 class="mb-4">💰 Monthly Revenue Overview</h5>
                <div class="chart-container">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card">
                <h5 class="mb-4">📊 Project Status</h5>
                <div class="chart-container">
                    <canvas id="projectStatusChart"></canvas>
                </div>
                <div class="mt-3">
                    @foreach($projectStatusData as $status => $count)
                        @if($count > 0)
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-capitalize">{{ str_replace('_', ' ', $status) }}</span>
                            <strong>{{ $count }}</strong>
                        </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity & Quick Actions -->
    <div class="row">
        <div class="col-md-8">
            <div class="card stat-card">
                <ul class="nav nav-tabs mb-3" id="activityTabs">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#recentProjects">Recent Projects</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#recentIpcs">Recent IPCs</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#topSubcontractors">Top Subcontractors</a>
                    </li>
                </ul>
                
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="recentProjects">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Project</th>
                                        <th>Status</th>
                                        <th>Contract Value</th>
                                        <th>Progress</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentProjects as $project)
                                    <tr>
                                        <td>
                                            <a href="{{ route('projects.show', $project) }}">
                                                {{ $project->name }}
                                            </a>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $project->status == 'active' ? 'success' : 'secondary' }}">
                                                {{ ucfirst($project->status) }}
                                            </span>
                                        </td>
                                        <td>{{ number_format($project->contract_amount, 2) }} ETB</td>
                                        <td>
                                            @php
                                                $progress = $project->boqItems->where('status', 'completed')->count();
                                                $total = $project->boqItems->count();
                                                $percentage = $total > 0 ? ($progress / $total) * 100 : 0;
                                            @endphp
                                            <div class="progress" style="height: 8px;">
                                                <div class="progress-bar" style="width: {{ $percentage }}%"></div>
                                            </div>
                                            <small>{{ number_format($percentage, 1) }}%</small>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div class="tab-pane fade" id="recentIpcs">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>IPC No.</th>
                                        <th>Project</th>
                                        <th>Subcontractor</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentIpcs as $ipc)
                                    <tr>
                                        <td>
                                            <a href="{{ route('ipcs.show', $ipc) }}">{{ $ipc->ipc_number }}</a>
                                        </td>
                                        <td>{{ $ipc->project->name ?? 'N/A' }}</td>
                                        <td>{{ $ipc->subcontractor->name ?? 'N/A' }}</td>
                                        <td>{{ number_format($ipc->net_payment_amount, 2) }} ETB</td>
                                        <td>
                                            <span class="badge bg-{{ $ipc->status == 'approved' ? 'success' : 'warning' }}">
                                                {{ ucfirst($ipc->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div class="tab-pane fade" id="topSubcontractors">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Subcontractor</th>
                                        <th>Total Paid</th>
                                        <th>IPCs Count</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($subcontractorPerformance as $perf)
                                    <tr>
                                        <td>{{ $perf->subcontractor->name ?? 'N/A' }}</td>
                                        <td>{{ number_format($perf->total_paid, 2) }} ETB</td>
                                        <td>{{ $perf->subcontractor->ipcs->count() ?? 0 }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card stat-card mb-4">
                <h5 class="mb-3">⚡ Quick Actions</h5>
                <div class="d-grid gap-2">
                    <a href="{{ route('projects.create') }}" class="btn btn-primary quick-action text-start">
                        <i class="fas fa-plus-circle me-2"></i>Create New Project
                    </a>
                    <a href="{{ route('boq-items.create') }}" class="btn btn-success quick-action text-start">
                        <i class="fas fa-list me-2"></i>Add BOQ Item
                    </a>
                    <a href="{{ route('ipcs.create') }}" class="btn btn-info quick-action text-start">
                        <i class="fas fa-file-invoice me-2"></i>Generate IPC
                    </a>
                    <a href="{{ route('subcontractors.create') }}" class="btn btn-warning quick-action text-start">
                        <i class="fas fa-user-plus me-2"></i>Add Subcontractor
                    </a>
                </div>
            </div>
            
            <div class="card stat-card">
                <h5 class="mb-3">📋 Cost Summary by Category</h5>
                @foreach($costCategorySummary as $category)
                <div class="d-flex justify-content-between mb-2">
                    <span>{{ $category->name }}</span>
                    <strong>{{ number_format($category->total, 2) }} ETB</strong>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Revenue Chart
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    new Chart(revenueCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode(array_column($monthlyRevenue, 'month')) !!},
            datasets: [{
                label: 'Monthly Revenue (ETB)',
                data: {!! json_encode(array_column($monthlyRevenue, 'amount')) !!},
                backgroundColor: 'rgba(102, 126, 234, 0.8)',
                borderColor: 'rgba(102, 126, 234, 1)',
                borderWidth: 1,
                borderRadius: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            }
        }
    });

    // Project Status Chart
    const statusCtx = document.getElementById('projectStatusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Active', 'Completed', 'On Hold', 'Cancelled'],
            datasets: [{
                data: {!! json_encode(array_values($projectStatusData)) !!},
                backgroundColor: ['#28a745', '#007bff', '#ffc107', '#dc3545']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            }
        }
    });
</script>
@endpush
VIEW

echo "✅ Dashboard enhanced with charts and analytics!"
