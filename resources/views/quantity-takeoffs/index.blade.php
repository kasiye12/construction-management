@extends('layouts.app')

@section('title', 'Quantity Take-Off - CMS')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div><h2>📐 Quantity Take-Off (Measurement)</h2></div>
        <a href="{{ route('quantity-takeoffs.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i> New Measurement</a>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-3"><div class="card bg-info text-white"><div class="card-body text-center py-2"><h6>TOTAL MEASUREMENTS</h6><h3>{{ $takeoffs->total() }}</h3></div></div></div>
    <div class="col-md-3"><div class="card bg-success text-white"><div class="card-body text-center py-2"><h6>TOTAL AREA/VOLUME</h6><h3>{{ number_format($totalMeasured, 2) }}</h3></div></div></div>
</div>

<div class="card mb-3">
    <div class="card-header"><h6 class="mb-0"><i class="fas fa-filter me-2"></i>Filters</h6></div>
    <div class="card-body py-2">
        <form method="GET" class="row g-2">
            <div class="col-md-3"><select name="project_id" class="form-select form-select-sm" onchange="this.form.submit()"><option value="">All Projects</option>@foreach($projects as $p)<option value="{{ $p->id }}" {{ $projectId==$p->id?'selected':'' }}>{{ $p->name }}</option>@endforeach</select></div>
            <div class="col-md-2"><select name="status" class="form-select form-select-sm" onchange="this.form.submit()"><option value="">All Status</option><option value="draft" {{ request('status')=='draft'?'selected':'' }}>Draft</option><option value="verified" {{ request('status')=='verified'?'selected':'' }}>Verified</option><option value="approved" {{ request('status')=='approved'?'selected':'' }}>Approved</option></select></div>
            <div class="col-md-1"><button type="submit" class="btn btn-primary btn-sm w-100"><i class="fas fa-filter"></i></button></div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light"><tr><th>Date</th><th>Project</th><th>BOQ Item</th><th>Structure</th><th>Element</th><th>Qty</th><th>L×W×H</th><th>Total</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
                @forelse($takeoffs as $t)
                <tr>
                    <td>{{ $t->measurement_date->format('M d, Y') }}</td>
                    <td>{{ Str::limit($t->project->name??'N/A',20) }}</td>
                    <td>{{ Str::limit($t->boqItem->description??'N/A',25) }}</td>
                    <td>{{ $t->structure_type??'-' }}</td>
                    <td>{{ $t->element_id??'-' }}</td>
                    <td>{{ $t->quantity_count }}</td>
                    <td>{{ number_format($t->length,2) }}×{{ number_format($t->width,2) }}×{{ number_format($t->height_depth,2) }}</td>
                    <td class="fw-bold">{{ number_format($t->total_area_volume,2) }}</td>
                    <td><span class="badge bg-{{ $t->status=='approved'?'success':($t->status=='verified'?'info':'secondary') }}">{{ ucfirst($t->status) }}</span></td>
                    <td>
                        <a href="{{ route('quantity-takeoffs.show',$t) }}" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>
                        @if($t->status=='draft')<form action="{{ route('quantity-takeoffs.verify',$t) }}" method="POST" class="d-inline">@csrf<button class="btn btn-sm btn-warning">Verify</button></form>@endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="10" class="text-center py-4">No measurements recorded.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer"><div class="d-flex justify-content-between align-items-center px-3 py-2">
                <div class="pagination-info">Showing {{ $takeoffs->firstItem() ?? 0 }} - {{ $takeoffs->lastItem() ?? 0 }} of {{ $takeoffs->total() }} results</div>
                {{ $takeoffs->links('vendor.pagination.custom') }}
            </div></div>
</div>
@endsection
