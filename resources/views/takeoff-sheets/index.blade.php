@extends('layouts.app')

@section('title', 'Takeoff Sheets - CMS')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2>📐 Takeoff Sheets</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Takeoff Sheets</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('takeoff-sheets.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> New Takeoff Sheet
        </a>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2">
            <div class="col-md-3">
                <select name="project_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Projects</option>
                    @foreach($projects as $p)
                        <option value="{{ $p->id }}" {{ ($projectId ?? '') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Status</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="verified" {{ request('status') == 'verified' ? 'selected' : '' }}>Verified</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                </select>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Sheet No.</th>
                    <th>Project</th>
                    <th>Division</th>
                    <th>Date</th>
                    <th>Items</th>
                    <th>Measured By</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sheets as $sheet)
                <tr>
                    <td><a href="{{ route('takeoff-sheets.show', $sheet) }}" class="fw-bold">{{ $sheet->sheet_number }}</a></td>
                    <td>{{ Str::limit($sheet->project->name ?? 'N/A', 25) }}</td>
                    <td>{{ $sheet->division ?? '-' }}</td>
                    <td>{{ optional($sheet->measurement_date)->format('M d, Y') }}</td>
                    <td><span class="badge bg-info">{{ $sheet->items->count() }}</span></td>
                    <td>{{ $sheet->measured_by ?? '-' }}</td>
                    <td>
                        <span class="badge bg-{{ $sheet->status == 'approved' ? 'success' : ($sheet->status == 'verified' ? 'info' : 'warning') }}">
                            {{ ucfirst($sheet->status) }}
                        </span>
                    </td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('takeoff-sheets.show', $sheet) }}" class="btn btn-info"><i class="fas fa-eye"></i></a>
                            <a href="{{ route('takeoff-sheets.edit', $sheet) }}" class="btn btn-primary"><i class="fas fa-edit"></i></a>
                            <a href="{{ route('takeoff-sheets.print', $sheet) }}" class="btn btn-outline-dark" target="_blank"><i class="fas fa-print"></i></a>
                            @if($sheet->status != 'approved' || auth()->user()->isAdmin())
                            <form action="{{ route('takeoff-sheets.destroy', $sheet) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this sheet?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger"><i class="fas fa-trash"></i></button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center py-4">No takeoff sheets found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $sheets->links() }}</div>
</div>
@endsection
