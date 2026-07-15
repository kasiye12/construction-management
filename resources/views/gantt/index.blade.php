@extends('layouts.app')

@section('title', 'Project Timeline - Gantt Chart')

@push('styles')
<style>
    .gantt-container {
        overflow-x: auto;
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 15px rgba(0,0,0,0.08);
        padding: 20px;
    }
    .gantt-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 12px;
        min-width: 900px;
    }
    .gantt-table th, .gantt-table td {
        border: 1px solid #ddd;
        padding: 8px 10px;
    }
    .gantt-table thead th {
        background: #1a237e;
        color: white;
        font-size: 10px;
        text-align: center;
        font-weight: 600;
    }
    .gantt-table .task-name { min-width: 280px; font-weight: 500; }
    .gantt-bar-cell { position: relative; min-width: 800px; height: 40px; }
    .gantt-bar {
        position: absolute;
        top: 10px;
        height: 20px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 8px;
        color: white;
        font-weight: 700;
        cursor: pointer;
        box-shadow: 0 1px 3px rgba(0,0,0,0.2);
    }
    .gantt-bar.completed { background: #28a745; }
    .gantt-bar.in_progress { background: #007bff; }
    .gantt-bar.pending { background: #6c757d; }
    .today-line {
        position: absolute;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #dc3545;
        z-index: 3;
    }
    .today-label {
        position: absolute;
        top: -18px;
        left: -25px;
        font-size: 9px;
        color: #dc3545;
        font-weight: bold;
        white-space: nowrap;
    }
    @media print {
        body { background: white !important; }
        .sidebar, .topbar, .btn, .filter-bar, .no-print { display: none !important; }
        .main-content { margin: 0 !important; padding: 10px !important; }
        .gantt-container { box-shadow: none !important; border: 1px solid #000 !important; }
        .gantt-bar.completed { background: #28a745 !important; -webkit-print-color-adjust: exact; }
        .gantt-bar.in_progress { background: #007bff !important; -webkit-print-color-adjust: exact; }
        .gantt-bar.pending { background: #6c757d !important; -webkit-print-color-adjust: exact; }
        @page { size: A3 landscape; margin: 10mm; }
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <div>
            <h2>📅 Project Timeline - Gantt Chart</h2>
            <p class="text-muted">
                @if($selectedProject)
                    Project: <strong>{{ $selectedProject->name }}</strong>
                @else
                    Select a project to view timeline
                @endif
            </p>
        </div>
        <div>
            <button onclick="window.print()" class="btn btn-primary">
                <i class="fas fa-print me-2"></i>Print
            </button>
        </div>
    </div>

    <div class="filter-bar mb-4 no-print" style="background:white;padding:15px;border-radius:8px;box-shadow:0 2px 10px rgba(0,0,0,0.1);">
        <form method="GET" class="row align-items-end">
            <div class="col-md-4">
                <label class="form-label fw-bold">📁 Select Project</label>
                <select name="project_id" class="form-select" onchange="this.form.submit()">
                    <option value="">-- Choose Project --</option>
                    @foreach($projects as $p)
                        <option value="{{ $p->id }}" {{ $projectId == $p->id ? 'selected' : '' }}>
                            {{ $p->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>

    @if($tasks->count() > 0)
    <div class="d-none d-print-block mb-3">
        <h3 style="text-align:center;">PROJECT TIMELINE - GANTT CHART</h3>
        <p style="text-align:center;font-size:12px;">
            Project: <strong>{{ $selectedProject->name ?? 'N/A' }}</strong> | 
            Printed: {{ date('M d, Y') }}
        </p>
    </div>

    <div style="margin-bottom:15px;">
        <span style="display:inline-block;width:14px;height:14px;background:#28a745;border-radius:3px;vertical-align:middle;margin-right:5px;"></span> Completed
        <span style="display:inline-block;width:14px;height:14px;background:#007bff;border-radius:3px;vertical-align:middle;margin:0 5px 0 15px;"></span> In Progress
        <span style="display:inline-block;width:14px;height:14px;background:#6c757d;border-radius:3px;vertical-align:middle;margin:0 5px 0 15px;"></span> Pending
        <span style="display:inline-block;width:14px;height:2px;background:#dc3545;vertical-align:middle;margin:0 5px 0 15px;"></span> Today
    </div>

    <div class="gantt-container">
        <table class="gantt-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Task Description</th>
                    <th>Category</th>
                    <th>Start</th>
                    <th>End</th>
                    <th>Days</th>
                    <th>Progress</th>
                    <th>Timeline</th>
                </tr>
            </thead>
            <tbody>
                @foreach($tasks as $index => $task)
                @php
                    $startDate = \Carbon\Carbon::parse($task['start']);
                    $endDate = \Carbon\Carbon::parse($task['end']);
                    $startOffset = \Carbon\Carbon::parse($minDate)->diffInDays($startDate);
                    $barWidth = max($task['duration'], 1);
                    $todayOffset = \Carbon\Carbon::parse($minDate)->diffInDays(now());
                    $barLeft = ($startOffset / max($totalDays, 1)) * 100;
                    $barW = ($barWidth / max($totalDays, 1)) * 100;
                    $todayLeft = ($todayOffset / max($totalDays, 1)) * 100;
                @endphp
                <tr>
                    <td style="text-align:center;">{{ $index + 1 }}</td>
                    <td class="task-name">{{ $task['name'] }}</td>
                    <td style="text-align:center;"><span class="badge bg-secondary">{{ $task['category'] }}</span></td>
                    <td style="text-align:center;">{{ $startDate->format('M d') }}</td>
                    <td style="text-align:center;">{{ $endDate->format('M d') }}</td>
                    <td style="text-align:center;">{{ $task['duration'] }}d</td>
                    <td style="text-align:center;">
                        <small>{{ $task['progress'] }}%</small>
                    </td>
                    <td class="gantt-bar-cell">
                        @if($todayLeft >= 0 && $todayLeft <= 100)
                        <div class="today-line" style="left:{{ $todayLeft }}%;">
                            <div class="today-label">Today</div>
                        </div>
                        @endif
                        <div class="gantt-bar {{ $task['status'] }}" 
                             style="left:{{ $barLeft }}%;width:{{ max($barW, 0.3) }}%;">
                            {{ $task['duration'] }}d
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div style="margin-top:20px;font-size:10px;color:#999;" class="d-none d-print-block">
        <p style="text-align:center;">Generated by Construction Management System - {{ date('F d, Y H:i') }}</p>
    </div>
    @else
    <div class="text-center py-5">
        <i class="fas fa-chart-gantt fa-4x text-muted mb-3"></i>
        <h4>No Timeline Data</h4>
        <p>Select a project with BOQ items that have start and end dates.</p>
    </div>
    @endif
</div>
@endsection
