@extends('layouts.app')

@section('title', 'Cost Categories - CMS')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2>📂 Cost Categories</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Cost Categories</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('cost-categories.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> New Category
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
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Project</th>
                    <th>BOQ Items</th>
                    <th>Order</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($costCategories as $cat)
                <tr>
                    <td><span class="badge bg-dark">{{ $cat->code ?? '-' }}</span></td>
                    <td><strong>{{ $cat->name }}</strong></td>
                    <td>{{ $cat->project->name ?? 'N/A' }}</td>
                    <td><span class="badge bg-info">{{ $cat->boq_items_count }}</span></td>
                    <td>{{ $cat->display_order }}</td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('cost-categories.show', $cat) }}" class="btn btn-info"><i class="fas fa-eye"></i></a>
                            <a href="{{ route('cost-categories.edit', $cat) }}" class="btn btn-warning"><i class="fas fa-edit"></i></a>
                            <form action="{{ route('cost-categories.destroy', $cat) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this category?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center py-4">No cost categories found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $costCategories->links() }}</div>
</div>
@endsection
