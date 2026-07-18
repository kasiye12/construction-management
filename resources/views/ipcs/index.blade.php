@extends('layouts.app')

@section('title', 'Payment Certificates - CMS')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div><h2>📄 Payment Certificates</h2></div>
        @if(\App\Helpers\PermissionHelper::canCreate('ipc'))
        <a href="{{ route('ipcs.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i> New IPC</a>
        @endif
    </div>
</div>

<!-- Professional Filter -->
<div class="card mb-3">
    <div class="card-header"><h6 class="mb-0"><i class="fas fa-filter me-2"></i>Filters</h6></div>
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-2">
                <label class="form-label small fw-bold">📁 Project</label>
                <select name="project_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Projects</option>
                    @foreach($projects as $p)
                        <option value="{{ $p->id }}" {{ request('project_id') == $p->id ? 'selected' : '' }}>{{ Str::limit($p->name, 25) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold">📊 Status</label>
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Status</option>
                    <option value="draft" {{ request('status')=='draft'?'selected':'' }}>Draft</option>
                    <option value="submitted" {{ request('status')=='submitted'?'selected':'' }}>Submitted</option>
                    <option value="approved" {{ request('status')=='approved'?'selected':'' }}>Approved</option>
                    <option value="rejected" {{ request('status')=='rejected'?'selected':'' }}>Rejected</option>
                    <option value="paid" {{ request('status')=='paid'?'selected':'' }}>Paid</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold">🔍 Search</label>
                <input type="text" name="search" class="form-control form-control-sm" placeholder="IPC number..." value="{{ request('search') }}">
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
                <button type="submit" class="btn btn-primary btn-sm w-100"><i class="fas fa-search"></i></button>
            </div>
            @if(request()->anyFilled(['project_id','status','search','date_from','date_to']))
            <div class="col-md-1"><a href="{{ route('ipcs.index') }}" class="btn btn-outline-danger btn-sm w-100"><i class="fas fa-times"></i></a></div>
            @endif
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr><th>IPC No.</th><th>Project</th><th>Subcontractor</th><th>Date</th><th class="text-end">Amount</th><th>Status</th><th>Actions</th></tr>
            </thead>
            <tbody>
                @forelse($ipcs as $ipc)
                <tr>
                    <td><a href="{{ route('ipcs.show', $ipc) }}" class="fw-bold text-decoration-none">{{ $ipc->ipc_number }}</a></td>
                    <td>{{ Str::limit($ipc->project->name ?? 'N/A', 25) }}</td>
                    <td>{{ Str::limit($ipc->subcontractor->name ?? 'N/A', 25) }}</td>
                    <td>{{ optional($ipc->ipc_date)->format('M d, Y') }}</td>
                    <td class="text-end">{{ number_format($ipc->net_payment_amount, 2) }}</td>
                    <td>
                        @php $sc = ['draft'=>'secondary','prepared'=>'info','checked'=>'warning','submitted'=>'primary','approved'=>'success','rejected'=>'danger','paid'=>'success']; @endphp
                        <span class="badge bg-{{ $sc[$ipc->status] ?? 'secondary' }}">{{ strtoupper($ipc->status) }}</span>
                    </td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            @if(\App\Helpers\PermissionHelper::canView('ipc'))<a href="{{ route('ipcs.show', $ipc) }}" class="btn btn-info"><i class="fas fa-eye"></i></a>@endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center py-4">No IPCs found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer"><div class="d-flex justify-content-between align-items-center px-3 py-2">
                <div class="pagination-info">Showing {{ $ipcs->firstItem() ?? 0 }} - {{ $ipcs->lastItem() ?? 0 }} of {{ $ipcs->total() }} results</div>
                {{ $ipcs->links('vendor.pagination.custom') }}
            </div></div>
</div>
@endsection
