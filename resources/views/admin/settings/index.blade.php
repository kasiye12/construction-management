@extends('layouts.app')

@section('title', 'Tax Settings - CMS')

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
            <div class="card-header"><h5 class="mb-0">Configure Tax Rates</h5></div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    These rates affect all payment certificate calculations. Changes take effect immediately.
                </div>

                <form action="{{ route('admin.settings.update') }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Tax / Fee</th>
                                <th style="width:150px;">Rate (%)</th>
                                <th style="width:80px;">Active</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($settings as $setting)
                            <tr>
                                <td>
                                    <strong>{{ $setting->display_name }}</strong>
                                    <br><small class="text-muted">{{ $setting->key }}</small>
                                </td>
                                <td>
                                    <div class="input-group input-group-sm">
                                        <input type="number" 
                                               name="rates[{{ $setting->id }}]" 
                                               class="form-control" 
                                               value="{{ old('rates.'.$setting->id, $setting->rate) }}"
                                               step="0.01" min="0" max="100"
                                               {{ $setting->is_active ? '' : 'disabled' }}>
                                        <span class="input-group-text">%</span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="form-check form-switch d-flex justify-content-center">
                                        <input class="form-check-input" type="checkbox" 
                                               name="active[{{ $setting->id }}]" 
                                               value="1"
                                               {{ $setting->is_active ? 'checked' : '' }}
                                               onchange="toggleRateInput(this, {{ $setting->id }})">
                                    </div>
                                </td>
                                <td><small>{{ $setting->description }}</small></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Save Settings
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Preview -->
    <div class="col-md-4">
        <div class="card mb-3">
            <div class="card-header"><h5 class="mb-0">📊 Example Calculation</h5></div>
            <div class="card-body">
                <p class="text-muted small">For 100,000 ETB work done:</p>
                @php
                    $vat = \App\Models\TaxSetting::vatRate();
                    $ret = \App\Models\TaxSetting::retentionRate();
                    $net = 100000;
                    $vatAmt = $net * ($vat/100);
                    $retAmt = $net * ($ret/100);
                    $due = $net + $vatAmt - $retAmt;
                @endphp
                <table class="table table-sm">
                    <tr><td>Net Work:</td><td class="text-end">{{ number_format($net,2) }}</td></tr>
                    <tr><td>VAT ({{ $vat }}%):</td><td class="text-end text-success">+{{ number_format($vatAmt,2) }}</td></tr>
                    <tr><td>Retention ({{ $ret }}%):</td><td class="text-end text-danger">-{{ number_format($retAmt,2) }}</td></tr>
                    <tr class="fw-bold"><td>Net Due:</td><td class="text-end text-primary">{{ number_format($due,2) }} ETB</td></tr>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleRateInput(checkbox, id) {
    const input = document.querySelector('input[name="rates[' + id + ']"]');
    input.disabled = !checkbox.checked;
    if (!checkbox.checked) {
        input.style.opacity = '0.5';
    } else {
        input.style.opacity = '1';
    }
}
</script>
@endpush
