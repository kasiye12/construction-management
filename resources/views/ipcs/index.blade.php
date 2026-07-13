@extends('layouts.app')

@section('title', 'IPCs - CMS')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2>📄 Interim Payment Certificates</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">IPCs</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('ipcs.create') }}" class="btn btn-primary btn-custom">
            <i class="fas fa-plus me-2"></i>New IPC
        </a>
    </div>
</div>

<div class="table-card">
    <div class="table-responsive">
        <table class="table table-hover datatable">
            <thead class="table-light">
                <tr>
                    <th>IPC Number</th>
                    <th>Project</th>
                    <th>Subcontractor</th>
                    <th>Date</th>
                    <th>Previous</th>
                    <th>Current</th>
                    <th>To Date</th>
                    <th>Net Payment</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($ipcs as $ipc)
                <tr>
                    <td>
                        <a href="{{ route('ipcs.show', $ipc) }}" class="fw-bold">
                            {{ $ipc->ipc_number }}
                        </a>
                    </td>
                    <td>{{ $ipc->project->name ?? 'N/A' }}</td>
                    <td>{{ $ipc->subcontractor->name ?? 'N/A' }}</td>
                    <td>{{ optional($ipc->ipc_date)->format('M d, Y') ?? 'N/A' }}</td>
                    <td>{{ number_format($ipc->total_previous_amount, 2) }}</td>
                    <td>{{ number_format($ipc->total_current_amount, 2) }}</td>
                    <td>{{ number_format($ipc->total_to_date_amount, 2) }}</td>
                    <td><strong>{{ number_format($ipc->net_payment_amount, 2) }}</strong></td>
                    <td>
                        @if($ipc->status == 'approved')
                            <span class="badge bg-success">Approved</span>
                        @elseif($ipc->status == 'paid')
                            <span class="badge bg-primary">Paid</span>
                        @elseif($ipc->status == 'submitted')
                            <span class="badge bg-info">Submitted</span>
                        @else
                            <span class="badge bg-warning">Draft</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('ipcs.show', $ipc) }}" class="btn btn-sm btn-info">
                            <i class="fas fa-eye"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="text-center py-4">
                        <i class="fas fa-file-invoice fa-3x text-muted mb-3"></i>
                        <h4>No IPCs Found</h4>
                        <a href="{{ route('ipcs.create') }}" class="btn btn-primary">Create First IPC</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $ipcs->links() }}
</div>
@endsection
