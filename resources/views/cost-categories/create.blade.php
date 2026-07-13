@extends('layouts.app')

@section('title', 'Create Cost Category - CMS')

@section('content')
<div class="page-header">
    <h2>➕ Create Cost Category</h2>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="table-card">
            <form action="{{ route('cost-categories.store') }}" method="POST">
                @csrf
                
                <div class="mb-3">
                    <label for="project_id" class="form-label">Project</label>
                    <select class="form-select" id="project_id" name="project_id" required>
                        <option value="">Select Project</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}">{{ $project->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="code" class="form-label">Code</label>
                        <input type="text" class="form-control" id="code" name="code" placeholder="A, B, C...">
                    </div>
                    <div class="col-md-8 mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">Create</button>
                <a href="{{ route('cost-categories.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection
