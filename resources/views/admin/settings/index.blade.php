@extends('layouts.app')

@section('title', 'Tax Settings - CMS')

@push('styles')
<style>
    .toggle-switch {
        position: relative;
        display: inline-block;
        width: 50px;
        height: 26px;
    }
    .toggle-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }
    .toggle-slider {
        position: absolute;
        cursor: pointer;
        top: 0; left: 0; right: 0; bottom: 0;
        background-color: #ccc;
        transition: .3s;
        border-radius: 26px;
    }
    .toggle-slider:before {
        position: absolute;
        content: "";
        height: 20px;
        width: 20px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: .3s;
        border-radius: 50%;
    }
    input:checked + .toggle-slider {
        background-color: #10b981;
    }
    input:checked + .toggle-slider:before {
        transform: translateX(24px);
    }
    .setting-card {
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        padding: 16px;
        margin-bottom: 12px;
        transition: all 0.2s;
    }
    .setting-card:hover {
        box-shadow: 0 4px 15px rgba(0,0,0,0.06);
    }
    .setting-card.disabled {
        opacity: 0.5;
        background: #f9fafb;
    }
    .setting-card.disabled .rate-input {
        background: #f3f4f6;
        color: #9ca3af;
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <h2>⚙️ Tax & Fee Settings</h2>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Tax Settings</li>
        </ol>
    </nav>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Configure Tax Rates</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Toggle each tax ON/OFF. Disabled taxes will not be applied to certificate calculations.
                </div>

                <form action="{{ route('admin.settings.update') }}" method="POST" id="settingsForm">
                    @csrf
                    @method('PUT')
                    
                    @foreach($settings as $setting)
                    <div class="setting-card {{ $setting->is_active ? '' : 'disabled' }}" id="settingCard{{ $setting->id }}">
                        <div class="row align-items-center">
                            <div class="col-md-5">
                                <h6 class="mb-1">{{ $setting->display_name }}</h6>
                                <small class="text-muted">{{ $setting->description }}</small>
                                <br><code>{{ $setting->key }}</code>
                            </div>
                            <div class="col-md-4">
                                <div class="input-group">
                                    <input type="number" 
                                           name="rates[{{ $setting->id }}]" 
                                           class="form-control rate-input" 
                                           value="{{ old('rates.'.$setting->id, $setting->rate) }}"
                                           step="0.01" min="0" max="100"
                                           id="rateInput{{ $setting->id }}"
                                           {{ $setting->is_active ? '' : 'disabled readonly' }}>
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                            <div class="col-md-3 text-center">
                                <label class="toggle-switch" style="cursor:pointer;">
                                    <input type="checkbox" 
                                           name="active[{{ $setting->id }}]" 
                                           value="1"
                                           {{ $setting->is_active ? 'checked' : '' }}
                                           onchange="toggleSetting({{ $setting->id }}, this.checked)">
                                    <span class="toggle-slider"></span>
                                </label>
                                <div class="mt-1">
                                    <small class="fw-bold status-label" id="statusLabel{{ $setting->id }}">
                                        {{ $setting->is_active ? '🟢 Enabled' : '🔴 Disabled' }}
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                    
                    <button type="submit" class="btn btn-primary mt-3">
                        <i class="fas fa-save me-2"></i>Save Settings
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Preview -->
    <div class="col-md-4">
        <div class="card mb-3">
            <div class="card-header"><h5 class="mb-0">📊 Live Preview</h5></div>
            <div class="card-body">
                <p class="text-muted small">For 100,000 ETB work done:</p>
                <table class="table table-sm" id="previewTable">
                    <tr><td>Net Work:</td><td class="text-end">100,000.00</td></tr>
                    <tr id="previewVAT"><td>VAT (<span class="vat-rate">15</span>%):</td><td class="text-end text-success">+<span class="vat-amount">15,000.00</span></td></tr>
                    <tr id="previewRetention"><td>Retention (<span class="ret-rate">5</span>%):</td><td class="text-end text-danger">-<span class="ret-amount">5,000.00</span></td></tr>
                    <tr id="previewWHT"><td>Withholding Tax (<span class="wht-rate">2</span>%):</td><td class="text-end text-danger">-<span class="wht-amount">2,000.00</span></td></tr>
                    <tr class="fw-bold"><td>Net Due:</td><td class="text-end text-primary" id="previewNetDue">108,000.00 ETB</td></tr>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h5 class="mb-0">ℹ️ How It Works</h5></div>
            <div class="card-body">
                <ul class="list-unstyled mb-0 small">
                    <li class="mb-2">🟢 <strong>Enabled:</strong> Tax is applied to all IPC calculations</li>
                    <li class="mb-2">🔴 <strong>Disabled:</strong> Tax is skipped (0% rate)</li>
                    <li class="mb-2">💡 Changes take effect immediately on new certificates</li>
                    <li class="mb-2">📊 Existing certificates are not affected</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleSetting(id, enabled) {
    const card = document.getElementById('settingCard' + id);
    const input = document.getElementById('rateInput' + id);
    const label = document.getElementById('statusLabel' + id);
    
    if (enabled) {
        card.classList.remove('disabled');
        input.disabled = false;
        input.readOnly = false;
        label.innerHTML = '🟢 Enabled';
    } else {
        card.classList.add('disabled');
        input.disabled = true;
        input.readOnly = true;
        label.innerHTML = '🔴 Disabled';
    }
    
    updatePreview();
}

function updatePreview() {
    const amount = 100000;
    const vatEnabled = document.querySelector('input[name="active[1]"]')?.checked ?? true;
    const retEnabled = document.querySelector('input[name="active[2]"]')?.checked ?? true;
    const whtEnabled = document.querySelector('input[name="active[3]"]')?.checked ?? true;
    
    const vatRate = vatEnabled ? parseFloat(document.getElementById('rateInput1')?.value || 15) : 0;
    const retRate = retEnabled ? parseFloat(document.getElementById('rateInput2')?.value || 5) : 0;
    const whtRate = whtEnabled ? parseFloat(document.getElementById('rateInput3')?.value || 2) : 0;
    
    const vatAmt = amount * (vatRate / 100);
    const retAmt = amount * (retRate / 100);
    const whtAmt = amount * (whtRate / 100);
    const netDue = amount + vatAmt - retAmt - whtAmt;
    
    document.querySelector('.vat-rate').textContent = vatRate;
    document.querySelector('.vat-amount').textContent = vatAmt.toFixed(2);
    document.querySelector('.ret-rate').textContent = retRate;
    document.querySelector('.ret-amount').textContent = retAmt.toFixed(2);
    document.querySelector('.wht-rate').textContent = whtRate;
    document.querySelector('.wht-amount').textContent = whtAmt.toFixed(2);
    document.getElementById('previewNetDue').textContent = netDue.toFixed(2) + ' ETB';
    
    document.getElementById('previewVAT').style.display = vatRate > 0 ? '' : 'none';
    document.getElementById('previewRetention').style.display = retRate > 0 ? '' : 'none';
    document.getElementById('previewWHT').style.display = whtRate > 0 ? '' : 'none';
}

// Update preview on rate change
document.querySelectorAll('.rate-input').forEach(input => {
    input.addEventListener('input', updatePreview);
});

// Initial preview
updatePreview();
</script>
@endpush
