@extends('layouts.app')

@section('title', 'Project Timeline - Gantt Chart')

@push('styles')
<style>
    .gantt-container { overflow-x: auto; background: white; border-radius: 12px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.06); border: 1px solid #e5e7eb; }
    .gantt-table { width: 100%; border-collapse: collapse; font-size: 11px; min-width: 950px; }
    .gantt-table th, .gantt-table td { border: 1px solid #d1d5db; padding: 8px 10px; vertical-align: middle; }
    .gantt-table thead th { background: #1a237e; color: white; font-size: 9px; font-weight: 600; text-align: center; text-transform: uppercase; letter-spacing: 0.5px; }
    .gantt-table .task-name { min-width: 280px; font-weight: 500; font-size: 11px; }
    .gantt-table .task-name a { color: #1a237e; text-decoration: none; }
    .gantt-table .task-name a:hover { text-decoration: underline; }
    .gantt-bar-cell { position: relative; min-width: 700px; height: 38px; }
    .gantt-bar { position: absolute; top: 10px; height: 18px; border-radius: 9px; display: flex; align-items: center; justify-content: center; font-size: 7px; color: white; font-weight: 700; cursor: pointer; box-shadow: 0 1px 3px rgba(0,0,0,0.2); min-width: 20px; transition: all 0.2s; }
    .gantt-bar:hover { opacity: 0.9; transform: scaleY(1.4); z-index: 2; }
    .gantt-bar.completed { background: #10b981; }
    .gantt-bar.in_progress { background: #3b82f6; }
    .gantt-bar.pending { background: #6b7280; }
    .today-line { position: absolute; top: 0; bottom: 0; width: 2px; background: #ef4444; z-index: 3; }
    .today-label { position: absolute; top: -20px; left: -28px; font-size: 9px; color: #ef4444; font-weight: 700; white-space: nowrap; background: white; padding: 1px 4px; border-radius: 3px; border: 1px solid #ef4444; }
    .month-scale { display: flex; border-bottom: 2px solid #1a237e; margin-bottom: 10px; }
    .month-scale .month-item { text-align: center; font-size: 10px; font-weight: 600; color: #1a237e; padding: 5px 0; border-right: 1px solid #e5e7eb; }
    .legend-item { display: inline-flex; align-items: center; gap: 5px; margin-right: 15px; font-size: 11px; }
    .legend-dot { width: 12px; height: 12px; border-radius: 3px; }
    .filter-bar { background: white; padding: 16px; border-radius: 8px; margin-bottom: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.06); border: 1px solid #e5e7eb; }
    
    @media print {
        @page { size: A3 landscape; margin: 10mm; }
        body { background: white !important; font-family: 'Segoe UI', Arial, sans-serif !important; }
        .sidebar, .topbar, .btn, .filter-bar, .no-print, .breadcrumb { display: none !important; }
        .main-content { margin: 0 !important; padding: 5px !important; width: 100% !important; }
        .gantt-container { box-shadow: none !important; border: 1px solid #000 !important; border-radius: 0 !important; padding: 10px !important; }
        .gantt-table { font-size: 8px; min-width: 100%; }
        .gantt-table thead th { background: #1a237e !important; color: white !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; font-size: 7px; }
        .gantt-table .task-name { min-width: 200px; font-size: 8px; }
        .gantt-bar.completed { background: #10b981 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .gantt-bar.in_progress { background: #3b82f6 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .gantt-bar.pending { background: #6b7280 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .today-line { background: #ef4444 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .print-header { display: block !important; text-align: center; margin-bottom: 15px; }
        .print-header h2 { font-size: 16px; margin: 0; color: #1a237e; }
        .print-header p { font-size: 10px; margin: 3px 0; color: #666; }
        .print-footer { display: block !important; text-align: center; font-size: 7px; color: #999; margin-top: 10px; border-top: 1px solid #ddd; padding-top: 5px; }
    }
    .print-header, .print-footer { display: none; }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h2>📅 Project Timeline - Gantt Chart</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Gantt Chart</li>
                    </ol>
                </nav>
            </div>
            <button onclick="window.print()" class="btn btn-outline-dark"><i class="fas fa-print me-1"></i> Print</button>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="filter-bar no-print">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-bold">📁 Project</label>
                <select name="project_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">-- Select Project --</option>
                    @foreach($projects as $p)
                        <option value="{{ $p->id }}" {{ ($projectId ?? '') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold">📊 Status</label>
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status')=='pending'?'selected':'' }}>Pending</option>
                    <option value="in_progress" {{ request('status')=='in_progress'?'selected':'' }}>In Progress</option>
                    <option value="completed" {{ request('status')=='completed'?'selected':'' }}>Completed</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold">📅 Date From</label>
                <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold">📅 Date To</label>
                <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-primary btn-sm w-100"><i class="fas fa-filter"></i></button>
            </div>
            @if(request()->anyFilled(['project_id','status','date_from','date_to']))
            <div class="col-md-1">
                <a href="{{ route('gantt.index') }}" class="btn btn-outline-danger btn-sm w-100"><i class="fas fa-times"></i></a>
            </div>
            @endif
        </form>
    </div>

    @if($tasks->count() > 0)
    <!-- Print Header -->
    <div class="print-header">
        <h2>PROJECT TIMELINE - GANTT CHART</h2>
        <p>Project: <strong>{{ $selectedProject->name ?? 'All Projects' }}</strong> | Period: {{ \Carbon\Carbon::parse($minDate)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($maxDate)->format('M d, Y') }} | Tasks: {{ $tasks->count() }}</p>
    </div>

    <!-- Legend -->
    <div class="mb-3 no-print">
        <span class="legend-item"><span class="legend-dot" style="background:#10b981;"></span> Completed</span>
        <span class="legend-item"><span class="legend-dot" style="background:#3b82f6;"></span> In Progress</span>
        <span class="legend-item"><span class="legend-dot" style="background:#6b7280;"></span> Pending</span>
        <span class="legend-item"><span class="legend-dot" style="background:#ef4444;width:2px;border-radius:0;"></span> Today</span>
        <span class="ms-3"><strong>{{ $tasks->count() }}</strong> tasks | <span class="text-success">{{ $tasks->where('status','completed')->count() }} done</span> | <span class="text-primary">{{ $tasks->where('status','in_progress')->count() }} active</span></span>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-3 no-print">
        <div class="col-md-3"><div class="card bg-success text-white"><div class="card-body text-center py-2"><h6>COMPLETED</h6><h3>{{ $tasks->where('status','completed')->count() }}</h3></div></div></div>
        <div class="col-md-3"><div class="card bg-primary text-white"><div class="card-body text-center py-2"><h6>IN PROGRESS</h6><h3>{{ $tasks->where('status','in_progress')->count() }}</h3></div></div></div>
        <div class="col-md-3"><div class="card bg-secondary text-white"><div class="card-body text-center py-2"><h6>PENDING</h6><h3>{{ $tasks->where('status','pending')->count() }}</h3></div></div></div>
        <div class="col-md-3"><div class="card bg-dark text-white"><div class="card-body text-center py-2"><h6>TOTAL</h6><h3>{{ $tasks->count() }}</h3></div></div></div>
    </div>

    <!-- Gantt Chart -->
    <div class="gantt-container">
        @php
            $months = []; $current = \Carbon\Carbon::parse($minDate)->startOfMonth(); $end = \Carbon\Carbon::parse($maxDate)->endOfMonth();
            while ($current->lte($end)) { $months[] = $current->copy(); $current->addMonth(); }
            $totalMonths = count($months);
        @endphp
        @if($totalMonths > 1)
        <div class="month-scale no-print">
            @foreach($months as $month)<div class="month-item" style="width:{{ 100/$totalMonths }}%;">{{ $month->format('M Y') }}</div>@endforeach
        </div>
        @endif

        <table class="gantt-table">
            <thead><tr><th style="width:30px;">#</th><th>Task</th><th style="width:80px;">Category</th><th style="width:65px;">Start</th><th style="width:65px;">End</th><th style="width:40px;">Days</th><th style="width:55px;">Progress</th><th>Timeline</th></tr></thead>
            <tbody>
                @foreach($tasks as $index => $task)
                @php
                    $sd = \Carbon\Carbon::parse($task['start']); $ed = \Carbon\Carbon::parse($task['end']);
                    $so = \Carbon\Carbon::parse($minDate)->diffInDays($sd);
                    $bw = max($task['duration'],1); $to = \Carbon\Carbon::parse($minDate)->diffInDays(now());
                    $bl = ($so/max($totalDays,1))*100; $bw2 = ($bw/max($totalDays,1))*100; $tl = ($to/max($totalDays,1))*100;
                @endphp
                <tr>
                    <td style="text-align:center;">{{ $index+1 }}</td>
                    <td class="task-name"><a href="{{ route('boq-items.show', $task['id']) }}">{{ $task['name'] }}</a></td>
                    <td style="text-align:center;"><span class="badge bg-secondary">{{ $task['category'] }}</span></td>
                    <td style="text-align:center;">{{ $sd->format('M d') }}</td>
                    <td style="text-align:center;">{{ $ed->format('M d') }}</td>
                    <td style="text-align:center;">{{ $task['duration'] }}d</td>
                    <td style="text-align:center;"><div class="progress" style="height:5px;width:45px;margin:0 auto;"><div class="progress-bar bg-{{ $task['progress']>=100?'success':($task['progress']>=50?'info':'warning') }}" style="width:{{ $task['progress'] }}%"></div></div><small>{{ $task['progress'] }}%</small></td>
                    <td class="gantt-bar-cell">
                        @if($tl>=0&&$tl<=100)<div class="today-line" style="left:{{$tl}}%;"><div class="today-label">▼ Today</div></div>@endif
                        <div class="gantt-bar {{ $task['status'] }}" style="left:{{$bl}}%;width:{{max($bw2,0.3)}}%;">{{ $task['duration'] }}d</div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="print-footer">Generated by CMS | {{ date('F d, Y H:i') }}</div>
    @else
    <div class="text-center py-5">
        <i class="fas fa-chart-gantt fa-4x text-muted mb-3"></i>
        <h4>No Timeline Data</h4>
        <p>Select a project with BOQ items that have start and end dates.</p>
    </div>
    @endif
</div>
@endsection
