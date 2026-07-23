@extends('layouts.app')

@section('title', 'Project Timeline - Gantt Chart')

@push('styles')
<style>
    :root {
        --row-h: 48px;
        --bar-h: 26px;
        --green: #10b981;
        --blue: #3b82f6;
        --amber: #f59e0b;
        --red: #ef4444;
        --gray: #94a3b8;
    }

    .gantt-container {
        background: #fff;
        border-radius: 16px;
        border: 1px solid #e5e7eb;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        overflow: hidden;
    }

    .gantt-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px 20px;
        border-bottom: 1px solid #e5e7eb;
        background: #fafbfc;
        flex-wrap: wrap;
        gap: 10px;
    }

    .legend {
        display: flex;
        gap: 14px;
        align-items: center;
    }
    .legend-item {
        display: flex;
        align-items: center;
        gap: 5px;
        font-size: 0.7rem;
        color: #64748b;
        font-weight: 500;
    }
    .legend-dot { width: 10px; height: 10px; border-radius: 3px; }

    .stats-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(110px, 1fr));
        gap: 10px;
        padding: 14px 20px;
        border-bottom: 1px solid #e5e7eb;
    }
    .stat-card {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 14px;
        border-radius: 10px;
        background: #f8fafc;
        border: 1px solid #e5e7eb;
    }
    .stat-card .stat-icon {
        width: 36px; height: 36px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
    }
    .stat-card .stat-info h4 { font-size: 1.1rem; font-weight: 700; margin: 0; line-height: 1; }
    .stat-card .stat-info span { font-size: 0.65rem; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.3px; }

    .gantt-table-wrap { overflow-x: auto; }
    .gantt-table-wrap::-webkit-scrollbar { height: 6px; }
    .gantt-table-wrap::-webkit-scrollbar-track { background: #f1f5f9; }
    .gantt-table-wrap::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }

    .gantt-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.78rem;
        min-width: 900px;
    }

    .gantt-table thead th {
        background: #1e293b;
        color: #e2e8f0;
        font-weight: 600;
        font-size: 0.65rem;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        padding: 10px 10px;
        border-bottom: 2px solid #334155;
        white-space: nowrap;
        position: sticky;
        top: 0;
        z-index: 3;
    }

    .gantt-table tbody td {
        padding: 0 10px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
        height: var(--row-h);
    }

    .gantt-table tbody tr:hover td { background: #fafbfd; }

    .col-num { width: 28px; text-align: center; color: #94a3b8; font-size: 0.65rem; }
    .col-task { min-width: 260px; }
    .col-task .t-name {
        font-weight: 600; color: #1e293b; font-size: 0.8rem;
        display: block; text-decoration: none;
    }
    .col-task .t-name:hover { color: #4f46e5; }
    .col-task .t-meta {
        font-size: 0.63rem; color: #94a3b8;
        display: flex; align-items: center; gap: 6px; margin-top: 1px;
        flex-wrap: wrap;
    }
    .col-task .t-meta .pct-badge {
        display: inline-block;
        padding: 1px 6px;
        border-radius: 8px;
        font-size: 0.6rem;
        font-weight: 700;
        color: #fff;
    }
    .col-cat { min-width: 80px; }
    .cat-tag {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 0.6rem;
        font-weight: 600;
        background: #e2e8f0;
        color: #475569;
        white-space: nowrap;
    }
    .col-date { min-width: 65px; font-size: 0.7rem; color: #64748b; text-align: center; white-space: nowrap; }

    .tl-cell { position: relative; min-width: 550px; height: var(--row-h); }

    .tl-weekend {
        position: absolute; top: 0; bottom: 0;
        background: #fafbfc;
        pointer-events: none; z-index: 0;
    }

    .tl-today {
        position: absolute; top: 0; bottom: 0;
        width: 2px; background: var(--red);
        z-index: 4; pointer-events: none;
    }
    .tl-today .tl-flag {
        position: absolute; top: -20px; left: -24px;
        background: #ef4444; color: #fff;
        font-size: 0.55rem; font-weight: 700;
        padding: 2px 8px; border-radius: 4px;
        white-space: nowrap;
    }

    .tl-bar {
        position: absolute;
        top: 50%; transform: translateY(-50%);
        height: var(--bar-h);
        border-radius: 13px;
        font-size: 0.6rem;
        font-weight: 700;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        cursor: pointer;
        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        min-width: 50px;
        padding: 0 8px;
        white-space: nowrap;
        overflow: hidden;
        transition: all 0.2s ease;
        z-index: 2;
    }
    .tl-bar:hover {
        transform: translateY(-50%) scaleY(1.7);
        box-shadow: 0 4px 16px rgba(0,0,0,0.2);
        z-index: 10;
    }
    .tl-bar.done { background: var(--green); }
    .tl-bar.active { background: var(--blue); }
    .tl-bar.wait { background: var(--gray); }
    .tl-bar.late { background: var(--red); animation: pulse-bar 1.8s infinite; }

    .tl-bar .bar-pct {
        background: rgba(255,255,255,0.25);
        padding: 1px 5px;
        border-radius: 6px;
        font-size: 0.55rem;
    }

    @keyframes pulse-bar {
        0%, 100% { box-shadow: 0 2px 6px rgba(239,68,68,0.3); }
        50% { box-shadow: 0 4px 16px rgba(239,68,68,0.6); }
    }

    .filter-strip {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        align-items: end;
        padding: 10px 20px;
    }

    @media print {
        @page { size: A3 landscape; margin: 6mm; }
        .sidebar, .topbar, .btn, .filter-strip, .no-print, .breadcrumb, .stats-row, .gantt-toolbar { display: none !important; }
        .main-content { margin: 0 !important; padding: 0 !important; }
        .gantt-container { box-shadow: none; border: 1px solid #ccc; }
        .gantt-table thead th { background: #1e293b !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .tl-bar.done { background: #10b981 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .tl-bar.active { background: #3b82f6 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .tl-bar.wait { background: #94a3b8 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h2>📅 Project Timeline</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Gantt Chart</li>
                    </ol>
                </nav>
            </div>
            <button onclick="window.print()" class="btn btn-sm btn-outline-dark no-print"><i class="fas fa-print me-1"></i> Print</button>
        </div>
    </div>

    @if(count($tasks) > 0)
    <div class="gantt-container">
        <div class="stats-row no-print">
            <div class="stat-card">
                <div class="stat-icon" style="background:#d1fae5;color:#059669;">✅</div>
                <div class="stat-info"><h4>{{ collect($tasks)->where('status','completed')->count() }}</h4><span>Done</span></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#dbeafe;color:#2563eb;">🔄</div>
                <div class="stat-info"><h4>{{ collect($tasks)->where('status','in_progress')->count() }}</h4><span>Active</span></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#fef3c7;color:#d97706;">⏳</div>
                <div class="stat-info"><h4>{{ collect($tasks)->where('status','pending')->count() }}</h4><span>Pending</span></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#fee2e2;color:#dc2626;">⚠️</div>
                <div class="stat-info"><h4>{{ collect($tasks)->filter(fn($t)=>$t['status']!='completed'&&now()->gt(\Carbon\Carbon::parse($t['end'])))->count() }}</h4><span>Overdue</span></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#ede9fe;color:#7c3aed;">📊</div>
                <div class="stat-info"><h4>{{ count($tasks) }}</h4><span>Total</span></div>
            </div>
        </div>

        <div class="gantt-toolbar no-print">
            <span style="font-weight:600;font-size:0.85rem;color:#1e293b;">
                {{ $selectedProject->name ?? 'All Projects' }}
            </span>
            <div class="legend">
                <span class="legend-item"><span class="legend-dot" style="background:var(--green);"></span> Done</span>
                <span class="legend-item"><span class="legend-dot" style="background:var(--blue);"></span> Active</span>
                <span class="legend-item"><span class="legend-dot" style="background:var(--gray);"></span> Pending</span>
                <span class="legend-item"><span class="legend-dot" style="background:var(--red);"></span> Overdue</span>
            </div>
        </div>

        <form method="GET" class="filter-strip no-print">
            <select name="project_id" class="form-select form-select-sm" style="width:200px;" onchange="this.form.submit()">
                <option value="">All Projects</option>
                @foreach($projects as $p)
                    <option value="{{ $p->id }}" {{ ($projectId??'')==$p->id?'selected':'' }}>{{ $p->name }}</option>
                @endforeach
            </select>
            <select name="status" class="form-select form-select-sm" style="width:120px;" onchange="this.form.submit()">
                <option value="">All Status</option>
                <option value="completed" {{ request('status')=='completed'?'selected':'' }}>Done</option>
                <option value="in_progress" {{ request('status')=='in_progress'?'selected':'' }}>Active</option>
                <option value="pending" {{ request('status')=='pending'?'selected':'' }}>Pending</option>
            </select>
            @if(request()->anyFilled(['project_id','status']))
                <a href="{{ route('gantt.index') }}" class="btn btn-sm btn-outline-danger">Clear</a>
            @endif
        </form>

        @php
            $totalDays = max($totalDays, 1);
            $todayPct = (\Carbon\Carbon::parse($minDate)->diffInDays(now()) / $totalDays) * 100;
            $weekends = [];
            $d = \Carbon\Carbon::parse($minDate)->startOfWeek(6);
            while($d->lte(\Carbon\Carbon::parse($maxDate))) {
                $left = (\Carbon\Carbon::parse($minDate)->diffInDays($d) / $totalDays) * 100;
                $width = (2 / $totalDays) * 100;
                $weekends[] = ['left' => $left, 'width' => $width];
                $d->addWeek();
            }
        @endphp

        <div class="gantt-table-wrap">
            <table class="gantt-table">
                <thead>
                    <tr>
                        <th class="col-num">#</th>
                        <th class="col-task">Task</th>
                        <th class="col-cat">Category</th>
                        <th class="col-date">Start</th>
                        <th class="col-date">End</th>
                        <th>Timeline</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tasks as $i => $task)
                    @php
                        $sd = \Carbon\Carbon::parse($task['start']);
                        $ed = \Carbon\Carbon::parse($task['end']);
                        $left = (\Carbon\Carbon::parse($minDate)->diffInDays($sd) / $totalDays) * 100;
                        $width = max(($task['duration'] / $totalDays) * 100, 0.5);
                        $isLate = $task['status'] != 'completed' && now()->gt($ed);
                        $barClass = $isLate ? 'late' : ($task['status']=='completed'?'done':($task['status']=='in_progress'?'active':'wait'));
                        
                        $pctColor = $task['progress']>=100 ? 'var(--green)' : ($task['progress']>=50 ? 'var(--blue)' : 'var(--amber)');
                    @endphp
                    <tr>
                        <td class="col-num">{{ $i + 1 }}</td>
                        <td class="col-task">
                            <a href="{{ route('boq-items.show', $task['id']) }}" class="t-name">{{ $task['name'] }}</a>
                            <div class="t-meta">
                                <span class="pct-badge" style="background:{{ $pctColor }};">{{ $task['progress'] }}%</span>
                                <span>{{ $sd->format('M d') }} → {{ $ed->format('M d') }}</span>
                                @if($isLate)<span style="color:#ef4444;font-weight:600;">⚠️</span>@endif
                            </div>
                        </td>
                        <td class="col-cat"><span class="cat-tag">{{ $task['category'] }}</span></td>
                        <td class="col-date">{{ $sd->format('M d, Y') }}</td>
                        <td class="col-date">{{ $ed->format('M d, Y') }}</td>
                        <td class="tl-cell">
                            @foreach($weekends as $we)
                                <div class="tl-weekend" style="left:{{$we['left']}}%;width:{{$we['width']}}%;"></div>
                            @endforeach
                            @if($todayPct>=0&&$todayPct<=100)
                                <div class="tl-today" style="left:{{$todayPct}}%;"><div class="tl-flag">Today</div></div>
                            @endif
                            <div class="tl-bar {{ $barClass }}"
                                 style="left:{{$left}}%;width:{{$width}}%;"
                                 title="{{ $task['name'] }} | {{ $task['progress'] }}%">
                                {{ $task['duration'] }}d
                                <span class="bar-pct">{{ $task['progress'] }}%</span>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @else
    <div class="text-center py-5">
        <div style="font-size:4rem;margin-bottom:12px;">📅</div>
        <h5 style="color:#64748b;">No timeline data</h5>
        <p style="color:#94a3b8;">Select a project with BOQ items that have start and end dates.</p>
    </div>
    @endif
</div>
@endsection
