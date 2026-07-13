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
        <a href="{{ route('cost-categories.create') }}" class="btn btn-primary btn-custom">
            <i class="fas fa-plus me-2"></i>New Category
        </a>
    </div>
</div>

<div class="table-card">
    <div class="table-responsive">
        <table class="table table-hover">
            <thead class="table-light">
                <tr>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Project</th>
                    <th>Order</th>
                    <th>Items</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($costCategories as $category)
                <tr>
                    <td>{{ $category->code ?? 'N/A' }}</td>
                    <td>{{ $category->name }}</td>
                    <td>{{ $category->project->name ?? 'N/A' }}</td>
                    <td>{{ $category->display_order }}</td>
                    <td>{{ $category->boqItems->count() ?? 0 }}</td>
                    <td>
                        <a href="{{ route('cost-categories.show', $category) }}" class="btn btn-sm btn-info">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="{{ route('cost-categories.edit', $category) }}" class="btn btn-sm btn-warning">
                            <i class="fas fa-edit"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-4">No categories found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
