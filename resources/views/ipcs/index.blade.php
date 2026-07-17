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

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr><th>IPC No.</th><th>Project</th><th>Subcontractor</th><th>Date</th><th>Amount</th><th>Status</th><th>Actions</th></tr>
            </thead>
            <tbody>
                @forelse($ipcs as $ipc)
                <tr>
                    <td><a href="{{ route('ipcs.show', $ipc) }}" class="fw-bold text-decoration-none">{{ $ipc->ipc_number }}</a></td>
                    <td>{{ $ipc->project->name ?? 'N/A' }}</td>
                    <td>{{ $ipc->subcontractor->name ?? 'N/A' }}</td>
                    <td>{{ optional($ipc->ipc_date)->format('M d, Y') }}</td>
                    <td class="text-end">{{ number_format($ipc->net_payment_amount, 2) }}</td>
                    <td><span class="badge bg-{{ $ipc->status=='approved'?'success':($ipc->status=='submitted'?'warning':'secondary') }}">{{ ucfirst($ipc->status) }}</span></td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            @if(\App\Helpers\PermissionHelper::canView('ipc'))
                            <a href="{{ route('ipcs.show', $ipc) }}" class="btn btn-info"><i class="fas fa-eye"></i></a>
                            @endif
                            @if(\App\Helpers\PermissionHelper::canEdit('ipc'))
                            <a href="{{ route('ipcs.edit', $ipc) }}" class="btn btn-warning"><i class="fas fa-edit"></i></a>
                            @endif
                            @if(\App\Helpers\PermissionHelper::canDelete('ipc'))
                            <form action="{{ route('ipcs.destroy', $ipc) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-danger"><i class="fas fa-trash"></i></button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center py-4">No IPCs found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $ipcs->links() }}</div>
</div>
@endsection
