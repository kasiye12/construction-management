@extends('layouts.app')

@section('title', 'Payment Summary - ' . $ipc->ipc_number)

@push('styles')
<style>
    .payment-summary {
        font-family: 'Segoe UI', Arial, sans-serif;
        font-size: 8px;
        background: white;
        border: 2px solid #1a237e;
        padding: 6px;
        max-width: 100%;
    }
    .payment-summary .header-table { width: 100%; border-collapse: collapse; margin-bottom: 3px; }
    .payment-summary .header-table td { padding: 2px 3px; font-size: 8px; vertical-align: top; }
    .payment-summary .company-name { font-size: 10px; font-weight: bold; color: #1a237e; }
    .payment-summary .company-name-sub { font-size: 9px; font-weight: bold; color: #1a237e; }
    .payment-summary .summary-title {
        text-align: center; font-size: 11px; font-weight: bold;
        border: 2px solid #333; padding: 3px; margin: 4px 0;
        background: #f5f5f5;
    }
    .payment-summary .items-table { width: 100%; border-collapse: collapse; font-size: 7px; margin: 4px 0; }
    .payment-summary .items-table th {
        background: #1a237e; color: white; padding: 3px 2px;
        font-size: 6px; text-align: center; border: 1px solid #333;
    }
    .payment-summary .items-table td { padding: 2px 3px; border: 1px solid #333; text-align: center; }
    .payment-summary .items-table .text-left { text-align: left; }
    .payment-summary .items-table .text-right { text-align: right; }
    .payment-summary .items-table .total-row { background: #e8eaf6; font-weight: bold; }
    .payment-summary .info-row { padding: 1px 0; font-size: 8px; }
    .payment-summary .info-row strong { display: inline-block; width: 100px; }
    .payment-summary .footer-text { text-align: center; font-size: 7px; color: #1a237e; font-style: italic; margin-top: 5px; font-weight: bold; }
    
    @media print {
        body { background: white !important; }
        .sidebar, .topbar, .btn, .no-print, .breadcrumb, .page-header { display: none !important; }
        .main-content { margin: 0 !important; padding: 3px !important; }
        @page { size: A4 landscape; margin: 4mm; }
    }
</style>
@endpush

@section('content')
<div class="page-header no-print">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h2>📄 {{ $ipc->ipc_number }}</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('ipcs.index') }}">Payment Certificates</a></li>
                    <li class="breadcrumb-item active">{{ $ipc->ipc_number }}</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('ipcs.print', $ipc) }}" class="btn btn-outline-dark btn-sm" target="_blank"><i class="fas fa-print me-1"></i> Print</a>
            <a href="{{ route('ipcs.certificate', $ipc) }}" class="btn btn-primary btn-sm"><i class="fas fa-file-pdf me-1"></i> PDF</a>
        </div>
    </div>
</div>

<!-- APPROVAL ACTIONS -->
@php
    $user = auth()->user(); $userId = $user->id; $status = $ipc->status;
    $canPrepare = \App\Models\WorkflowPermission::canUserAct($userId, 'prepare') || $user->isAdmin();
    $canCheck = \App\Models\WorkflowPermission::canUserAct($userId, 'check') || $user->isAdmin();
    $canSubmit = \App\Models\WorkflowPermission::canUserAct($userId, 'submit') || $user->isAdmin();
    $canApprove = \App\Models\WorkflowPermission::canUserAct($userId, 'approve') || $user->isAdmin();
    $canReject = \App\Models\WorkflowPermission::canUserAct($userId, 'reject') || $user->isAdmin();
    $canPay = \App\Models\WorkflowPermission::canUserAct($userId, 'pay') || $user->isAdmin();
@endphp

<div class="card mb-3 no-print">
    <div class="card-header"><h6 class="mb-0">✅ Actions <small>({{ $user->role_label }})</small></h6></div>
    <div class="card-body py-2">
        <div class="d-flex gap-2 flex-wrap">
            @if($status == 'draft' && $canPrepare)<form action="{{ route('ipcs.prepare', $ipc) }}" method="POST">@csrf<button class="btn btn-info btn-sm">📝 Prepare</button></form>@endif
            @if(in_array($status, ['draft','prepared']) && $canCheck)<form action="{{ route('ipcs.check', $ipc) }}" method="POST">@csrf<button class="btn btn-warning btn-sm">✅ Check</button></form>@endif
            @if(in_array($status, ['draft','prepared','checked']) && $canSubmit)<form action="{{ route('ipcs.submit', $ipc) }}" method="POST">@csrf<button class="btn btn-primary btn-sm">📤 Submit</button></form>@endif
            @if($status == 'submitted' && $canApprove)<form action="{{ route('ipcs.approve', $ipc) }}" method="POST" onsubmit="return confirm('Approve?')">@csrf<button class="btn btn-success btn-sm">✔️ Approve</button></form>@endif
            @if($status == 'submitted' && $canReject)<form action="{{ route('ipcs.reject', $ipc) }}" method="POST" onsubmit="return confirm('Reject?')">@csrf<button class="btn btn-danger btn-sm">❌ Reject</button></form>@endif
            @if($status == 'approved' && $canPay)<form action="{{ route('ipcs.mark-paid', $ipc) }}" method="POST">@csrf<button class="btn btn-dark btn-sm">💰 Mark Paid</button></form>@endif
        </div>
    </div>
</div>

<!-- PAYMENT SUMMARY -->
<div class="payment-summary">
    
    <!-- HEADER -->
    <table class="header-table">
        <tr>
            <td width="65%">
                <div class="company-name">ቲኤንቲ ኮንስትራክሽንና ንግድ ሥራዎች</div>
                <div class="company-name-sub">TNT CONSTRUCTION AND TRADING</div>
            </td>
            <td width="35%" style="text-align:right;font-size:7px;">
                <strong>Document №:</strong> {{ $ipc->ipc_number }}<br>
                <strong>Issue №:</strong> {{ $ipc->issue_number ?? '1' }} &nbsp;&nbsp; <strong>Page №:</strong> 1 of 1
            </td>
        </tr>
    </table>

    <div class="summary-title">SUBCONTRACT PAYMENT SUMMARY</div>

    <div class="info-row"><strong>Project:</strong> {{ $ipc->project->name ?? 'N/A' }}</div>
    <div class="info-row"><strong>Client:</strong> {{ $ipc->project->client_name ?? '_________________' }}</div>
    <div class="info-row"><strong>Contractor:</strong> {{ \App\Models\CompanySetting::get('company_name', 'TNT Construction and Trading PLC') }}</div>
    <div class="info-row"><strong>Location:</strong> Addis Ababa</div>
    <div class="info-row"><strong>Sub-Contractor:</strong> {{ $ipc->subcontractor->name ?? 'N/A' }}</div>
    <div class="info-row"><strong>SUMMARY OF PAYMENT NO. {{ str_pad($ipc->issue_number ?? '01', 2, '0', STR_PAD_LEFT) }}</strong></div>
    <br>

    <!-- ITEMS TABLE - CORRECT CALCULATIONS -->
    <table class="items-table">
        <thead>
            <tr>
                <th rowspan="2">Item</th>
                <th rowspan="2">Description</th>
                <th rowspan="2">Unit</th>
                <th rowspan="2">Rate</th>
                <th colspan="2">Contract</th>
                <th colspan="2">Previous Executed</th>
                <th colspan="2">Current Executed</th>
                <th colspan="2">Todate Executed</th>
                <th rowspan="2">Remark</th>
            </tr>
            <tr>
                <th>Quantity</th><th>Amount (birr)</th>
                <th>Quantity</th><th>Amount (birr)</th>
                <th>Quantity</th><th>Amount (birr)</th>
                <th>Quantity</th><th>Amount (birr)</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalContractAmt = 0;
                $totalPrevQty = 0; $totalPrevAmt = 0;
                $totalCurrQty = 0; $totalCurrAmt = 0;
                $totalTodateQty = 0; $totalTodateAmt = 0;
            @endphp
            
            @foreach($ipc->ipcItems as $index => $item)
            @php
                // Get rate from contract or BOQ item
                $rate = $item->contract_rate ?? $item->boqItem->unit_rate ?? 0;
                
                // Contract values
                $contractQty = $item->contract_quantity ?? 0;
                $contractAmt = $item->contract_amount ?? ($contractQty * $rate);
                
                // Previous executed (from earlier IPCs)
                $prevQty = $item->previous_quantity ?? 0;
                $prevAmt = $item->previous_amount ?? ($prevQty * $rate);
                
                // Current executed (this IPC)
                $currQty = $item->current_quantity ?? 0;
                $currAmt = $item->current_amount ?? ($currQty * $rate);
                
                // To-date = Previous + Current
                $todateQty = $prevQty + $currQty;
                $todateAmt = $prevAmt + $currAmt;
                
                // Percentage
                $pct = $contractQty > 0 ? round(($todateQty / $contractQty) * 100, 1) : 0;
                
                // Totals
                $totalContractAmt += $contractAmt;
                $totalPrevQty += $prevQty; $totalPrevAmt += $prevAmt;
                $totalCurrQty += $currQty; $totalCurrAmt += $currAmt;
                $totalTodateQty += $todateQty; $totalTodateAmt += $todateAmt;
            @endphp
            <tr>
                <td>{{ $index + 1 }}</td>
                <td class="text-left">{{ $item->boqItem->description ?? 'N/A' }}</td>
                <td>{{ $item->boqItem->unit ?? '-' }}</td>
                <td class="text-right">{{ number_format($rate, 2) }}</td>
                {{-- Contract --}}
                <td class="text-right">{{ number_format($contractQty, 2) }}</td>
                <td class="text-right">{{ number_format($contractAmt, 2) }}</td>
                {{-- Previous --}}
                <td class="text-right">{{ number_format($prevQty, 2) }}</td>
                <td class="text-right">{{ number_format($prevAmt, 2) }}</td>
                {{-- Current --}}
                <td class="text-right fw-bold">{{ number_format($currQty, 2) }}</td>
                <td class="text-right fw-bold">{{ number_format($currAmt, 2) }}</td>
                {{-- Todate --}}
                <td class="text-right">{{ number_format($todateQty, 2) }}</td>
                <td class="text-right">{{ number_format($todateAmt, 2) }}</td>
                {{-- Remark --}}
                <td class="text-left" style="font-size:6px;">{{ $pct }}% Paid</td>
            </tr>
            @endforeach
            
            <!-- TOTAL ROW -->
            <tr class="total-row">
                <td colspan="4" class="text-right"><strong>Total Amount……………</strong></td>
                <td></td>
                <td class="text-right"><strong>{{ number_format($totalContractAmt, 2) }}</strong></td>
                <td></td>
                <td class="text-right"><strong>{{ number_format($totalPrevAmt, 2) }}</strong></td>
                <td></td>
                <td class="text-right"><strong>{{ number_format($totalCurrAmt, 2) }}</strong></td>
                <td></td>
                <td class="text-right"><strong>{{ number_format($totalTodateAmt, 2) }}</strong></td>
                <td></td>
            </tr>
        </tbody>
    </table>

    <div class="footer-text">Striving to Build The Future!</div>
</div>
@endsection
