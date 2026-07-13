@extends('layouts.app')

@section('title', 'IPC Details - CMS')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2>📄 {{ $ipc->ipc_number }}</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('ipcs.index') }}">IPCs</a></li>
                    <li class="breadcrumb-item active">{{ $ipc->ipc_number }}</li>
                </ol>
            </nav>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-8">
        <div class="table-card">
            <h5>IPC Information</h5>
            <hr>
            <table class="table table-bordered">
                <tr><th width="30%">Project</th><td>{{ $ipc->project->name ?? 'N/A' }}</td></tr>
                <tr><th>Subcontractor</th><td>{{ $ipc->subcontractor->name ?? 'N/A' }}</td></tr>
                <tr><th>IPC Date</th><td>{{ optional($ipc->ipc_date)->format('M d, Y') }}</td></tr>
                <tr><th>Period</th><td>{{ optional($ipc->period_start_date)->format('M d, Y') }} to {{ optional($ipc->period_end_date)->format('M d, Y') }}</td></tr>
                <tr><th>Status</th><td><span class="badge bg-{{ $ipc->status == 'approved' ? 'success' : 'warning' }}">{{ ucfirst($ipc->status) }}</span></td></tr>
            </table>
        </div>
        
        <div class="table-card mt-4">
            <h5>IPC Items</h5>
            <hr>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Contract Qty</th>
                            <th>Previous</th>
                            <th>Current</th>
                            <th>To Date</th>
                            <th>%</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($ipc->ipcItems as $item)
                        <tr>
                            <td>{{ $item->boqItem->description ?? 'N/A' }}</td>
                            <td>{{ number_format($item->contract_quantity, 2) }}</td>
                            <td>{{ number_format($item->previous_amount, 2) }}</td>
                            <td>{{ number_format($item->current_amount, 2) }}</td>
                            <td>{{ number_format($item->to_date_amount, 2) }}</td>
                            <td>{{ number_format($item->percentage_complete, 1) }}%</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="table-card">
            <h5>Financial Summary</h5>
            <hr>
            <p>Previous: {{ number_format($ipc->total_previous_amount, 2) }} ETB</p>
            <p>Current: {{ number_format($ipc->total_current_amount, 2) }} ETB</p>
            <p>To Date: <strong>{{ number_format($ipc->total_to_date_amount, 2) }} ETB</strong></p>
            <p>Retention ({{ $ipc->retention_percentage }}%): {{ number_format($ipc->retention_amount, 2) }} ETB</p>
            <hr>
            <h4>Net Payment: {{ number_format($ipc->net_payment_amount, 2) }} ETB</h4>
        </div>
    </div>
</div>
@endsection
