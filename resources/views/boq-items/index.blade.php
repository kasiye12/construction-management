@extends('layouts.app')

@section('title', 'BOQ Items - CMS')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div><h2>📋 Bill of Quantities</h2></div>
        @if(\App\Helpers\PermissionHelper::canCreate('boq'))
        <a href="{{ route('boq-items.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i> New BOQ Item</a>
        @endif
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr><th>Item No.</th><th>Description</th><th>Project</th><th>Unit</th><th>Revenue</th><th>P/L</th><th>Status</th><th>Actions</th></tr>
            </thead>
            <tbody>
                @forelse($boqItems as $item)
                <tr>
                    <td><strong>{{ $item->item_number }}</strong></td>
                    <td><a href="{{ route('boq-items.show', $item) }}" class="text-decoration-none">{{ Str::limit($item->description, 50) }}</a></td>
                    <td>{{ $item->project->name ?? 'N/A' }}</td>
                    <td>{{ $item->unit }}</td>
                    <td class="text-end">{{ number_format($item->revenue_amount, 2) }}</td>
                    <td class="text-end fw-bold {{ $item->profit_loss >= 0 ? 'text-success' : 'text-danger' }}">{{ number_format($item->profit_loss, 2) }}</td>
                    <td><span class="badge bg-{{ $item->status=='completed'?'success':($item->status=='in_progress'?'warning':'secondary') }}">{{ ucfirst($item->status) }}</span></td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            @if(\App\Helpers\PermissionHelper::canView('boq'))
                            <a href="{{ route('boq-items.show', $item) }}" class="btn btn-info"><i class="fas fa-eye"></i></a>
                            @endif
                            @if(\App\Helpers\PermissionHelper::canEdit('boq'))
                            <a href="{{ route('boq-items.edit', $item) }}" class="btn btn-warning"><i class="fas fa-edit"></i></a>
                            @endif
                            @if(\App\Helpers\PermissionHelper::canDelete('boq'))
                            <form action="{{ route('boq-items.destroy', $item) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-danger"><i class="fas fa-trash"></i></button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center py-4">No BOQ items found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $boqItems->links() }}</div>
</div>
@endsection
