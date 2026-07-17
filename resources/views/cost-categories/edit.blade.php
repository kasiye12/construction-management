@extends('layouts.app')

@section('title', 'Add Cost Category - CMS')

@section('content')
<div class="page-header"><h2>➕ Edit Cost Category</h2></div>
<div class="row"><div class="col-md-8"><div class="card"><div class="card-header"><h5 class="mb-0">Category Details</h5></div><div class="card-body">
    <form action="{{ route('cost-categories.update, $costCategory') }}" method="POST">
        @csrf
                @method('PUT')
        <div class="mb-3"><label class="form-label">Project <span class="text-danger">*</span></label><select name="project_id" class="form-select" required><option value="">Select</option>@foreach($projects as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach</select></div>
        <div class="row"><div class="col-md-4 mb-3"><label class="form-label">Code</label><input type="text" name="code" class="form-control" placeholder="A, B, C..."></div><div class="col-md-8 mb-3"><label class="form-label">Name <span class="text-danger">*</span></label><input type="text" name="name" class="form-control" required></div></div>
        <div class="mb-3"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="2"></textarea></div>
        <div class="mb-3"><label class="form-label">Display Order</label><input type="number" name="display_order" class="form-control" value="1" min="0"></div>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Create</button>
        <a href="{{ route('cost-categories.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div></div></div></div>
@endsection
