@extends('layouts.app')

@section('title', 'Company Settings - CMS')

@section('content')
<div class="page-header">
    <h2>🏢 Company Settings</h2>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Company Settings</li>
        </ol>
    </nav>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header"><h5 class="mb-0">Company Information</h5></div>
            <div class="card-body">
                <form action="{{ route('admin.company-settings.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf @method('PUT')
                    
                    <!-- Logo Upload -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">Company Logo</label>
                        <div class="d-flex align-items-center gap-3">
                            @php $logoUrl = \App\Models\CompanySetting::getLogoUrl(); @endphp
                            <div style="width:100px;height:100px;border:2px dashed #ddd;border-radius:8px;display:flex;align-items:center;justify-content:center;overflow:hidden;">
                                @if($logoUrl)
                                    <img src="{{ $logoUrl }}" alt="Logo" style="max-width:100%;max-height:100%;">
                                @else
                                    <span style="font-size:40px;">🏗️</span>
                                @endif
                            </div>
                            <div>
                                <input type="file" name="company_logo" class="form-control form-control-sm" accept="image/*">
                                <small class="text-muted">Recommended: 200×200px PNG or JPG (max 2MB)</small>
                                @if($logoUrl)
                                <br><a href="{{ route('admin.company-settings.remove-logo') }}" class="text-danger small" onclick="return confirm('Remove logo?')">Remove Logo</a>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Company Name (English) <span class="text-danger">*</span></label>
                            <input type="text" name="company_name" class="form-control" value="{{ \App\Models\CompanySetting::get('company_name') }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Company Name (Amharic)</label>
                            <input type="text" name="company_name_amharic" class="form-control" value="{{ \App\Models\CompanySetting::get('company_name_amharic') }}">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Tagline</label>
                        <input type="text" name="company_tagline" class="form-control" value="{{ \App\Models\CompanySetting::get('company_tagline') }}">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" name="company_phone" class="form-control" value="{{ \App\Models\CompanySetting::get('company_phone') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="text" name="company_email" class="form-control" value="{{ \App\Models\CompanySetting::get('company_email') }}">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Address</label>
                            <input type="text" name="company_address" class="form-control" value="{{ \App\Models\CompanySetting::get('company_address') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">TIN Number</label>
                            <input type="text" name="company_tin" class="form-control" value="{{ \App\Models\CompanySetting::get('company_tin') }}">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Website</label>
                            <input type="text" name="company_website" class="form-control" value="{{ \App\Models\CompanySetting::get('company_website') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Document Prefix</label>
                            <input type="text" name="document_prefix" class="form-control" value="{{ \App\Models\CompanySetting::get('document_prefix') }}">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Save Settings</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header"><h5 class="mb-0">📋 Preview</h5></div>
            <div class="card-body text-center">
                @php $logoUrl = \App\Models\CompanySetting::getLogoUrl(); @endphp
                @if($logoUrl)
                    <img src="{{ $logoUrl }}" alt="Logo" style="max-width:120px;margin-bottom:10px;">
                @else
                    <div style="font-size:60px;">🏗️</div>
                @endif
                <h5>{{ \App\Models\CompanySetting::get('company_name') }}</h5>
                <p class="text-muted small">{{ \App\Models\CompanySetting::get('company_tagline') }}</p>
                <hr>
                <small class="text-muted">
                    📞 {{ \App\Models\CompanySetting::get('company_phone') }}<br>
                    📧 {{ \App\Models\CompanySetting::get('company_email') }}<br>
                    📍 {{ \App\Models\CompanySetting::get('company_address') }}<br>
                    TIN: {{ \App\Models\CompanySetting::get('company_tin') }}
                </small>
            </div>
        </div>
    </div>
</div>
@endsection
