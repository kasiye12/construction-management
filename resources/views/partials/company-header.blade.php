@php
    $logoUrl = \App\Models\CompanySetting::getLogoUrl();
    $companyName = \App\Models\CompanySetting::get('company_name', 'TNT Construction and Trading');
    $companyTagline = \App\Models\CompanySetting::get('company_tagline', 'General Contractor & Engineering Services');
    $companyPhone = \App\Models\CompanySetting::get('company_phone', '+251-000-000000');
    $companyEmail = \App\Models\CompanySetting::get('company_email', 'info@tnt-constructions.com');
    $companyAddress = \App\Models\CompanySetting::get('company_address', 'Addis Ababa, Ethiopia');
    $companyTin = \App\Models\CompanySetting::get('company_tin', '000000000');
@endphp

<div class="header-section">
    <div class="logo-box">
        @if($logoUrl)
            <img src="{{ $logoUrl }}" alt="Logo" style="width:100%;height:100%;object-fit:contain;">
        @else
            🏗️
        @endif
    </div>
    <div class="company-info">
        <div class="company-name">{{ $companyName }}</div>
        <div class="company-sub">{{ $companyTagline }}</div>
        <div class="company-address">📞 {{ $companyPhone }} | 📧 {{ $companyEmail }} | 📍 {{ $companyAddress }} | TIN: {{ $companyTin }}</div>
    </div>
    <div class="doc-box">
        <strong>Document No:</strong> {{ $ipc->ipc_number }}<br>
        <strong>Issue:</strong> {{ $ipc->issue_number ?? '1' }} | <strong>Page:</strong> 1/1
    </div>
</div>
