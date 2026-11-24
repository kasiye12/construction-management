@extends('layouts.app')

@section('title', 'Daily Equipment Maintenance & Spare Parts Log')

@section('content')
<div class="container-fluid py-3">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 text-dark fw-bold">
                <i class="fas fa-receipt text-success me-2"></i> Daily Equipment Maintenance & Spare Parts Log
            </h1>
            <p class="text-muted small mb-0">Daily workshop transactions, spare part requisitions, PO references & maintenance expenditures</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('equipment-logs.export', request()->all()) }}" class="btn btn-outline-success btn-sm">
                <i class="fas fa-file-excel me-1"></i> Export Excel
            </a>
            <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#importExcelModal">
                <i class="fas fa-file-upload me-1"></i> Import Daily Report
            </button>
            <a href="{{ route('equipment-logs.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-1"></i> Log Spare Part / Cost
            </a>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 h-100 border-start border-success border-4">
                <div class="card-body p-3">
                    <span class="text-muted small text-uppercase fw-semibold">Total Maintenance Spend (VAT incl.)</span>
                    <h3 class="fw-bold mb-0 text-success mt-1">{{ number_format($totalCost, 2) }} <span class="fs-6 text-muted">ETB</span></h3>
                    <small class="text-muted">Total filtered expenditure</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 h-100 border-start border-primary border-4">
                <div class="card-body p-3">
                    <span class="text-muted small text-uppercase fw-semibold">Transactions Logged</span>
                    <h3 class="fw-bold mb-0 text-primary mt-1">{{ $totalLogsCount }}</h3>
                    <small class="text-muted">Spare parts / workshop services</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 h-100 border-start border-warning border-4">
                <div class="card-body p-3">
                    <span class="text-muted small text-uppercase fw-semibold">Average Cost per Item</span>
                    <h3 class="fw-bold mb-0 text-dark mt-1">
                        {{ $totalLogsCount > 0 ? number_format($totalCost / $totalLogsCount, 2) : '0.00' }}
                        <span class="fs-6 text-muted">ETB</span>
                    </h3>
                    <small class="text-muted">Per line entry</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form action="{{ route('equipment-logs.index') }}" method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">Search Part / PO / Supplier</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Spare part, PO NO, Plate, Supplier..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">Equipment</label>
                    <select name="equipment_id" class="form-select form-select-sm">
                        <option value="">All Fleet</option>
                        @foreach($equipments as $eq)
                            <option value="{{ $eq->id }}" {{ request('equipment_id') == $eq->id ? 'selected' : '' }}>
                                {{ $eq->plate_serial_no }} ({{ $eq->equipment_type }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">Project Site</label>
                    <select name="project_id" class="form-select form-select-sm">
                        <option value="">All Projects</option>
                        @foreach($projects as $p)
                            <option value="{{ $p->id }}" {{ request('project_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">From Date</label>
                    <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">To Date</label>
                    <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-1 d-flex gap-1">
                    <button type="submit" class="btn btn-primary btn-sm flex-grow-1" title="Apply Filter">
                        <i class="fas fa-filter"></i>
                    </button>
                    @if(request()->anyFilled(['search', 'equipment_id', 'project_id', 'date_from', 'date_to']))
                        <a href="{{ route('equipment-logs.index') }}" class="btn btn-outline-secondary btn-sm" title="Clear">
                            <i class="fas fa-times"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Log Table --}}
    <div class="card border-0 shadow-sm rounded-3">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                <thead class="table-light text-uppercase small text-muted">
                    <tr>
                        <th style="width: 4%;">#</th>
                        <th style="width: 9%;">Date</th>
                        <th style="width: 14%;">Equipment</th>
                        <th style="width: 22%;">Spare Part / Work Description</th>
                        <th style="width: 11%;">PO / Ref No</th>
                        <th style="width: 8%;" class="text-center">Qty & Unit</th>
                        <th style="width: 10%;" class="text-end">Unit Cost</th>
                        <th style="width: 11%;" class="text-end">Total Cost (VAT)</th>
                        <th style="width: 11%;">Supplier / Workshop</th>
                        <th style="width: 5%;" class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $index => $item)
                        <tr>
                            <td class="text-center text-muted">{{ $logs->firstItem() + $index }}</td>
                            <td>{{ $item->log_date ? $item->log_date->format('d/m/Y') : '-' }}</td>
                            <td>
                                @if($item->equipment_id)
                                    <a href="{{ route('equipment.show', $item->equipment_id) }}" class="fw-bold text-dark text-decoration-none">
                                        {{ $item->equipment_plate ?: $item->equipment_type }}
                                    </a>
                                @else
                                    <span class="fw-bold">{{ $item->equipment_plate ?: '-' }}</span>
                                @endif
                                <div class="small text-muted">{{ $item->equipment_type }}</div>
                            </td>
                            <td>
                                <span class="fw-semibold text-dark">{{ $item->spare_part_name }}</span>
                                @if($item->remarks)
                                    <div class="small text-muted">{{ $item->remarks }}</div>
                                @endif
                            </td>
                            <td>
                                @if($item->po_no)
                                    <code class="text-primary">{{ $item->po_no }}</code>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="fw-bold">{{ (float)$item->quantity }}</span>
                                <span class="small text-muted">{{ $item->unit }}</span>
                            </td>
                            <td class="text-end">{{ number_format($item->unit_cost, 2) }}</td>
                            <td class="text-end fw-bold text-dark">{{ number_format($item->total_cost_with_vat, 2) }} ETB</td>
                            <td><span class="small">{{ $item->supplier ?: '-' }}</span></td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('equipment-logs.edit', $item) }}" class="btn btn-outline-secondary p-1 px-2" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if(auth()->user()?->isAdmin() || auth()->user()?->is_super_admin || auth()->user()?->hasPermission('equipment-logs.delete'))
                                        <form action="{{ route('equipment-logs.destroy', $item) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this maintenance record?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger p-1 px-2" title="Delete">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-4 text-muted">
                                <i class="fas fa-receipt fa-3x mb-3 text-secondary"></i>
                                <p class="mb-2">No spare parts or maintenance logs recorded.</p>
                                <a href="{{ route('equipment-logs.create') }}" class="btn btn-primary btn-sm">Add First Log Entry</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($logs->hasPages())
            <div class="card-footer bg-white py-2">
                {{ $logs->links() }}
            </div>
        @endif
    </div>

    {{-- Import Modal --}}
    <div class="modal fade" id="importExcelModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content text-start">
                <form action="{{ route('equipment-logs.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title h6">
                            <i class="fas fa-file-excel text-success me-2"></i> Import Daily Report (Sheet 3)
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="small text-muted">
                            Upload <strong>FORMAT 1.xlsx</strong> or an Excel file with a <strong>DAILY REPORT</strong> sheet. The system will parse spare part names, PO references, unit costs, and total Ethiopian Birr costs.
                        </p>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Select Excel File (.xlsx, .xls)</label>
                            <input type="file" name="excel_file" class="form-control form-control-sm" accept=".xlsx,.xls" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Project Site (Optional)</label>
                            <select name="project_id" class="form-select form-select-sm">
                                <option value="">Central Workshop / All Sites</option>
                                @foreach($projects as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fas fa-upload me-1"></i> Start Import
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
