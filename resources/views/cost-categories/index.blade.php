@extends('layouts.app')

@section('title', 'Cost Categories - CMS')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div><h2>📂 Cost Categories</h2></div>
        @if(\App\Helpers\PermissionHelper::canCreate('cost-categories'))
        <a href="{{ route('cost-categories.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i> New Category</a>
        @endif
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr><th>Code</th><th>Name</th><th>Project</th><th>Items</th><th>Actions</th></tr>
            </thead>
            <tbody>
                @forelse($costCategories as $cat)
                <tr>
                    <td><span class="badge bg-dark">{{ $cat->code ?? 'N/A' }}</span></td>
                    <td><a href="{{ route('cost-categories.show', $cat) }}" class="fw-bold text-decoration-none">{{ $cat->name }}</a></td>
                    <td>{{ $cat->project->name ?? 'N/A' }}</td>
                    <td>{{ $cat->boq_items_count ?? 0 }}</td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            @if(\App\Helpers\PermissionHelper::canView('cost-categories'))
                            <a href="{{ route('cost-categories.show', $cat) }}" class="btn btn-info"><i class="fas fa-eye"></i></a>
                            @endif
                            @if(\App\Helpers\PermissionHelper::canEdit('cost-categories'))
                            <a href="{{ route('cost-categories.edit', $cat) }}" class="btn btn-warning"><i class="fas fa-edit"></i></a>
                            @endif
                            @if(\App\Helpers\PermissionHelper::canDelete('cost-categories'))
                            <form action="{{ route('cost-categories.destroy', $cat) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-danger"><i class="fas fa-trash"></i></button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center py-4">No categories found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $costCategories->links() }}</div>
</div>
@endsection
