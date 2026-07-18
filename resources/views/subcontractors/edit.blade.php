@extends('layouts.app')

@section('title', 'Edit Subcontractor - CMS')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <h2>✏️ Edit Subcontractor</h2>
        @if(\App\Helpers\PermissionHelper::canDelete('subcontractors'))
        <form action="{{ route('subcontractors.destroy', $subcontractor) }}" method="POST" onsubmit="return confirm('Delete?')" class="d-inline">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-danger"><i class="fas fa-trash me-1"></i> Delete</button>
        </form>
        @endif
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header"><h5 class="mb-0">Subcontractor Details</h5></div>
            <div class="card-body">
                <form action="{{ route('subcontractors.update', $subcontractor) }}" method="POST">
                    @csrf @method('PUT')
                    
                    @php $canEdit = \App\Helpers\PermissionHelper::canEdit('subcontractors'); @endphp
                    
                    <div class="mb-3">
                        <label class="form-label">Company Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $subcontractor->name) }}" required {{ $canEdit ? '' : 'readonly' }}>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3"><label class="form-label">Contact Person</label><input type="text" name="contact_person" class="form-control" value="{{ $subcontractor->contact_person }}" {{ $canEdit ? '' : 'readonly' }}></div>
                        <div class="col-md-6 mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="{{ $subcontractor->email }}" {{ $canEdit ? '' : 'readonly' }}></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3"><label class="form-label">Phone</label><input type="text" name="phone" class="form-control" value="{{ $subcontractor->phone }}" {{ $canEdit ? '' : 'readonly' }}></div>
                        <div class="col-md-6 mb-3"><label class="form-label">Tax ID</label><input type="text" name="tax_id" class="form-control" value="{{ $subcontractor->tax_id }}" {{ $canEdit ? '' : 'readonly' }}></div>
                    </div>
                    <div class="mb-3"><label class="form-label">Address</label><textarea name="address" class="form-control" rows="2" {{ $canEdit ? '' : 'readonly' }}>{{ $subcontractor->address }}</textarea></div>
                    <div class="mb-3"><div class="form-check"><input type="checkbox" name="is_active" class="form-check-input" value="1" {{ $subcontractor->is_active ? 'checked' : '' }} {{ $canEdit ? '' : 'disabled' }}><label class="form-check-label">Active</label></div></div>
                    
                    @if($canEdit)
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Update</button>
                    @else
                    <div class="alert alert-warning"><i class="fas fa-lock me-2"></i>You don't have permission to edit.</div>
                    @endif
                    <a href="{{ route('subcontractors.index') }}" class="btn btn-secondary">Back</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
