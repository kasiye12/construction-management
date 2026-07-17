@extends('layouts.app')

@section('title', 'Add Subcontractor - CMS')

@section('content')
<div class="page-header">
    <h2>➕ Edit Subcontractor</h2>
</div>
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header"><h5 class="mb-0">Subcontractor Details</h5></div>
            <div class="card-body">
                <form action="{{ route('subcontractors.update, $subcontractor') }}" method="POST">
                    @csrf
                @method('PUT')
                    <div class="mb-3">
                        <label class="form-label">Company Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3"><label class="form-label">Contact Person</label><input type="text" name="contact_person" class="form-control"></div>
                        <div class="col-md-6 mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control"></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3"><label class="form-label">Phone</label><input type="text" name="phone" class="form-control"></div>
                        <div class="col-md-6 mb-3"><label class="form-label">Tax ID</label><input type="text" name="tax_id" class="form-control"></div>
                    </div>
                    <div class="mb-3"><label class="form-label">Address</label><textarea name="address" class="form-control" rows="2"></textarea></div>
                    <div class="mb-3"><div class="form-check"><input type="checkbox" name="is_active" class="form-check-input" value="1" checked><label class="form-check-label">Active</label></div></div>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Create</button>
                    <a href="{{ route('subcontractors.index') }}" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
