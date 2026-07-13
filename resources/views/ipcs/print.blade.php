@extends('layouts.app')

@section('title', 'Print IPC - CMS')

@section('content')
<div class="page-header">
    <h2>🖨️ Print IPC: {{ $ipc->ipc_number }}</h2>
    <button onclick="window.print()" class="btn btn-primary">Print</button>
</div>

<div class="table-card">
    <h5>{{ $ipc->project->name ?? 'N/A' }}</h5>
    <p>Subcontractor: {{ $ipc->subcontractor->name ?? 'N/A' }}</p>
    <p>Date: {{ optional($ipc->ipc_date)->format('M d, Y') }}</p>
    
    <table class="table table-bordered mt-3">
        <thead>
            <tr>
                <th>Item</th>
                <th>Contract Amount</th>
                <th>Previous</th>
                <th>Current</th>
                <th>To Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($ipc->ipcItems as $item)
            <tr>
                <td>{{ $item->boqItem->description ?? 'N/A' }}</td>
                <td>{{ number_format($item->contract_amount, 2) }}</td>
                <td>{{ number_format($item->previous_amount, 2) }}</td>
                <td>{{ number_format($item->current_amount, 2) }}</td>
                <td>{{ number_format($item->to_date_amount, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="4">Net Payment</th>
                <th>{{ number_format($ipc->net_payment_amount, 2) }} ETB</th>
            </tr>
        </tfoot>
    </table>
</div>
@endsection
