@extends('layouts.app')

@section('title', $ipc->ipc_number . ' - Payment Certificate')

@section('content')
<div class="page-header">
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
            @if(\App\Helpers\PermissionHelper::canDelete('ipc') && in_array($ipc->status, ['draft','rejected']))
            <form action="{{ route('ipcs.destroy', $ipc) }}" method="POST" onsubmit="return confirm('Delete?')" class="d-inline">@csrf @method('DELETE')<button class="btn btn-danger"><i class="fas fa-trash"></i></button></form>
            @endif
            <a href="{{ route('ipcs.print', $ipc) }}" class="btn btn-outline-dark" target="_blank"><i class="fas fa-print me-1"></i> Print</a>
            <a href="{{ route('ipcs.certificate', $ipc) }}" class="btn btn-primary"><i class="fas fa-file-pdf me-1"></i> PDF</a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-7">
        <div class="card mb-3">
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr><td class="text-muted">Project:</td><td><strong>{{ $ipc->project->name ?? 'N/A' }}</strong></td></tr>
                    <tr><td class="text-muted">Subcontractor:</td><td><strong>{{ $ipc->subcontractor->name ?? 'N/A' }}</strong></td></tr>
                    <tr><td class="text-muted">Date:</td><td>{{ optional($ipc->ipc_date)->format('F d, Y') }}</td></tr>
                </table>
            </div>
        </div>

        <!-- Work Items -->
        <div class="card mb-3">
            <div class="card-header"><h6 class="mb-0">📋 Work Items</h6></div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light"><tr><th>#</th><th>Description</th><th>Unit</th><th>Qty</th><th>Rate</th><th>Amount</th></tr></thead>
                    <tbody>
                        @php $total = 0; @endphp
                        @foreach($ipc->ipcItems as $i => $item)
                        @php $amt = $item->current_amount ?? ($item->current_quantity * ($item->boqItem->unit_rate ?? 0)); $total += $amt; @endphp
                        <tr><td>{{ $i+1 }}</td><td>{{ $item->boqItem->description ?? 'N/A' }}</td><td>{{ $item->boqItem->unit ?? '-' }}</td><td>{{ number_format($item->current_quantity,2) }}</td><td>{{ number_format($item->boqItem->unit_rate??0,2) }}</td><td class="text-end fw-bold">{{ number_format($amt,2) }}</td></tr>
                        @endforeach
                    </tbody>
                    <tfoot><tr class="table-primary fw-bold"><td colspan="5" class="text-end">Net Amount:</td><td class="text-end">{{ number_format($total,2) }} ETB</td></tr></tfoot>
                </table>
            </div>
        </div>

        <!-- Actions -->
        @php $user = auth()->user(); $role = $user->getRoleName(); $status = $ipc->status; @endphp
        <div class="card">
            <div class="card-header"><h6 class="mb-0">✅ Actions</h6></div>
            <div class="card-body py-2">
                <div class="d-flex gap-2 flex-wrap">
                    @if($status == 'draft' && in_array($role, ['admin','manager','engineer']))
                    <form action="{{ route('ipcs.prepare', $ipc) }}" method="POST">@csrf<button class="btn btn-info btn-sm">📝 Prepare</button></form>
                    @endif
                    @if(in_array($status, ['draft','prepared']) && in_array($role, ['admin','manager','engineer']))
                    <form action="{{ route('ipcs.check', $ipc) }}" method="POST">@csrf<button class="btn btn-warning btn-sm">✅ Check</button></form>
                    @endif
                    @if(in_array($status, ['draft','prepared','checked']) && in_array($role, ['admin','manager','engineer']))
                    <form action="{{ route('ipcs.submit', $ipc) }}" method="POST">@csrf<button class="btn btn-primary btn-sm">📤 Submit</button></form>
                    @endif
                    @if($status == 'submitted' && in_array($role, ['admin','manager','finance']))
                    <form action="{{ route('ipcs.approve', $ipc) }}" method="POST" onsubmit="return confirm('Approve?')">@csrf<button class="btn btn-success btn-sm">✔️ Approve</button></form>
                    <form action="{{ route('ipcs.reject', $ipc) }}" method="POST" onsubmit="return confirm('Reject?')">@csrf<button class="btn btn-danger btn-sm">❌ Reject</button></form>
                    @endif
                    @if($status == 'approved' && in_array($role, ['admin','finance']))
                    <form action="{{ route('ipcs.mark-paid', $ipc) }}" method="POST">@csrf<button class="btn btn-dark btn-sm">💰 Mark Paid</button></form>
                    @endif
                </div>
                @if($status == 'paid')<div class="alert alert-success mt-2 mb-0 py-1">✅ Paid by <strong>{{ $ipc->paid_by ?? 'N/A' }}</strong> on {{ $ipc->paid_at ? \Carbon\Carbon::parse($ipc->paid_at)->format('M d, Y') : 'N/A' }}</div>@endif
                @if($status == 'rejected')<div class="alert alert-danger mt-2 mb-0 py-1">❌ Rejected by <strong>{{ $ipc->rejected_by ?? 'N/A' }}</strong></div>@endif
            </div>
        </div>
    </div>

    <div class="col-md-5">
        <!-- Approval Status -->
        <div class="card mb-3">
            <div class="card-header"><h6 class="mb-0">🔐 Approval Status</h6></div>
            <div class="card-body">
                @php
                    $steps = [
                        ['label'=>'Prepared By','user'=>$ipc->prepared_by,'date'=>$ipc->prepared_at],
                        ['label'=>'Checked By','user'=>$ipc->checked_by,'date'=>$ipc->checked_at],
                        ['label'=>'Submitted By','user'=>$ipc->submitted_by,'date'=>$ipc->submitted_at],
                        ['label'=>'Approved By','user'=>$ipc->approved_by,'date'=>$ipc->approved_at],
                        ['label'=>'Paid By','user'=>$ipc->paid_by,'date'=>$ipc->paid_at],
                    ];
                    $order = ['draft','prepared','checked','submitted','approved','paid'];
                    $idx = array_search($ipc->status, $order);
                @endphp
                @foreach($steps as $i => $s)
                    @php $done = $idx !== false && array_search(['prepared','checked','submitted','approved','paid'][$i], $order) <= $idx; @endphp
                    <div class="d-flex align-items-start mb-2 pb-2 border-bottom">
                        <div style="width:24px;">@if($done)<i class="fas fa-check-circle text-success"></i>@else<i class="far fa-circle text-muted"></i>@endif</div>
                        <div class="ms-2">
                            <strong>{{ $s['label'] }}</strong>
                            <div>@if($s['user'])<span class="text-primary fw-bold">👤 {{ $s['user'] }}</span>@elseif($done)<span class="text-warning small">Not recorded</span>@else<span class="text-muted small">Pending...</span>@endif</div>
                            @if($s['date'])<small class="text-muted">📅 {{ \Carbon\Carbon::parse($s['date'])->format('M d, Y h:i A') }}</small>@endif
                        </div>
                    </div>
                @endforeach
                @if($ipc->status == 'rejected')
                    <div class="d-flex align-items-start mb-2"><div style="width:24px;"><i class="fas fa-times-circle text-danger"></i></div><div class="ms-2"><strong>Rejected By</strong><br><span class="text-danger fw-bold">👤 {{ $ipc->rejected_by ?? 'N/A' }}</span>@if($ipc->rejected_at)<br><small>📅 {{ \Carbon\Carbon::parse($ipc->rejected_at)->format('M d, Y h:i A') }}</small>@endif</div></div>
                @endif
            </div>
        </div>

        <!-- Financial Summary -->
        <div class="card">
            <div class="card-header"><h6 class="mb-0">💰 Financial Summary</h6></div>
            <div class="card-body">
                @php
                    $net = $total; $vat = $net * 0.15; $gross = $net + $vat;
                    $ret = $net * (($ipc->retention_percentage??5)/100); $prev = $ipc->total_previous_amount ?? 0;
                    $ded = $prev + $ret; $due = $gross - $ded;
                @endphp
                <table class="table table-sm">
                    <tr><td>Net Amount:</td><td class="text-end">{{ number_format($net,2) }}</td></tr>
                    <tr><td>VAT (15%):</td><td class="text-end text-success">+{{ number_format($vat,2) }}</td></tr>
                    <tr class="fw-bold"><td>Gross:</td><td class="text-end">{{ number_format($gross,2) }}</td></tr>
                    <tr><td colspan="2"><hr class="my-1"></td></tr>
                    <tr><td>Prev. Payment:</td><td class="text-end text-danger">-{{ number_format($prev,2) }}</td></tr>
                    <tr><td>Retention:</td><td class="text-end text-danger">-{{ number_format($ret,2) }}</td></tr>
                    <tr><td colspan="2"><hr class="my-1"></td></tr>
                    <tr class="fw-bold fs-6"><td>Net Due:</td><td class="text-end text-success">{{ number_format($due,2) }} ETB</td></tr>
                </table>
                <div class="p-2 mt-2 rounded" style="background:#fffde7;border:1px solid #f59e0b;">
                    <small>Amount in Words:</small><br>
                    <strong>{{ \App\Helpers\NumberToWordsHelper::convert($due) }} Ethiopian Birr Only</strong>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
