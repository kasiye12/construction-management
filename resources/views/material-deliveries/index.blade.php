@extends('layouts.app')

@section('title', 'Material Deliveries - CMS')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2>📦 Material Delivery Tracking</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Material Deliveries</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('material-deliveries.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> New Delivery
        </a>
    </div>
</div>

<!-- Summary Cards -->
<div class="row mb-3">
    <div class="col-md-3"><div class="card bg-info text-white"><div class="card-body text-center py-2"><h6>TOTAL DELIVERIES</h6><h3>{{ $deliveries->total() }}</h3></div></div></div>
    <div class="col-md-3"><div class="card bg-success text-white"><div class="card-body text-center py-2"><h6>TOTAL QUANTITY</h6><h3>{{ number_format($totalDelivered, 2) }}</h3></div></div></div>
    <div class="col-md-3"><div class="card bg-warning text-white"><div class="card-body text-center py-2"><h6>RECORDED</h6><h3>{{ \App\Models\MaterialDelivery::where('status','recorded')->count() }}</h3></div></div></div>
    <div class="col-md-3"><div class="card bg-primary text-white"><div class="card-body text-center py-2"><h6>CONFIRMED</h6><h3>{{ \App\Models\MaterialDelivery::where('status','confirmed')->count() }}</h3></div></div></div>
</div>

<!-- Filter -->
<div class="card mb-3">
    <div class="card-header"><h6 class="mb-0"><i class="fas fa-filter me-2"></i>Filters</h6></div>
    <div class="card-body py-2">
        <form method="GET" class="row g-2">
            <div class="col-md-3">
                <select name="project_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Projects</option>
                    @foreach($projects as $p)
                        <option value="{{ $p->id }}" {{ $projectId == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Status</option>
                    <option value="recorded" {{ request('status')=='recorded'?'selected':'' }}>Recorded</option>
                    <option value="confirmed" {{ request('status')=='confirmed'?'selected':'' }}>Confirmed</option>
                </select>
            </div>
            <div class="col-md-2"><input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}"></div>
            <div class="col-md-2"><input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}"></div>
            <div class="col-md-2"><input type="text" name="search" class="form-control form-control-sm" placeholder="Search..." value="{{ request('search') }}"></div>
            <div class="col-md-1"><button type="submit" class="btn btn-primary btn-sm w-100"><i class="fas fa-search"></i></button></div>
        </form>
    </div>
</div>

<!-- Deliveries Table -->
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Date</th>
                    <th>Project</th>
                    <th>Material</th>
                    <th>Unit</th>
                    <th>Qty</th>
                    <th>Conv. Qty</th>
                    <th>Gate Pass</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($deliveries as $d)
                <tr>
                    <td>{{ $d->delivery_date->format('M d, Y') }}</td>
                    <td>{{ Str::limit($d->project->name??'N/A',20) }}</td>
                    <td>{{ Str::limit($d->item_description,30) }}</td>
                    <td>{{ $d->unit }}</td>
                    <td class="text-end">{{ number_format($d->quantity,2) }}</td>
                    <td class="text-end">{{ number_format($d->converted_quantity,2) }}</td>
                    <td>{{ $d->gate_pass_number ?? '-' }}</td>
                    <td>
                        @if($d->status == 'confirmed')
                            <span class="badge bg-success">✅ Confirmed</span>
                        @else
                            <span class="badge bg-warning text-dark">📝 Recorded</span>
                        @endif
                    </td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('material-deliveries.show', $d) }}" class="btn btn-info"><i class="fas fa-eye"></i></a>
                            @if($d->status != 'confirmed' || auth()->user()->isAdmin())
                            <a href="{{ route('material-deliveries.edit', $d) }}" class="btn btn-warning"><i class="fas fa-edit"></i></a>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" class="text-center py-4">No deliveries recorded.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer"><div class="d-flex justify-content-between align-items-center px-3 py-2">
                <div class="pagination-info">Showing {{ $deliveries->firstItem() ?? 0 }} - {{ $deliveries->lastItem() ?? 0 }} of {{ $deliveries->total() }} results</div>
                {{ $deliveries->links('vendor.pagination.custom') }}
            </div></div>
</div>
@endsection
