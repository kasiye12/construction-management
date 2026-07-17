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
            <a href="{{ route('ipcs.print', $ipc) }}" class="btn btn-outline-dark" target="_blank">
                <i class="fas fa-print me-1"></i> Print
            </a>
            <a href="{{ route('ipcs.certificate', $ipc) }}" class="btn btn-primary">
                <i class="fas fa-file-pdf me-1"></i> PDF
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-7">
        <!-- Info -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-6">
                        <table class="table table-sm table-borderless mb-0">
                            <tr><td class="text-muted">Project:</td><td><strong>{{ $ipc->project->name ?? 'N/A' }}</strong></td></tr>
                            <tr><td class="text-muted">Subcontractor:</td><td><strong>{{ $ipc->subcontractor->name ?? 'N/A' }}</strong></td></tr>
                            <tr><td class="text-muted">Date:</td><td>{{ optional($ipc->ipc_date)->format('F d, Y') }}</td></tr>
                            <tr><td class="text-muted">Period:</td><td>{{ optional($ipc->period_start_date)->format('M d') }} - {{ optional($ipc->period_end_date)->format('M d, Y') }}</td></tr>
                        </table>
                    </div>
                    <div class="col-sm-6 text-end">
                        @php $colors = ['draft'=>'secondary','prepared'=>'info','checked'=>'warning','submitted'=>'primary','approved'=>'success','rejected'=>'danger','paid'=>'dark']; @endphp
                        <span class="badge bg-{{ $colors[$ipc->status] ?? 'secondary' }} fs-6">{{ strtoupper($ipc->status) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Work Items -->
        <div class="card mb-4">
            <div class="card-header"><h5 class="mb-0">📋 Work Executed</h5></div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr><th>#</th><th>Description</th><th>Unit</th><th>Qty</th><th>Rate</th><th>Amount</th></tr>
                    </thead>
                    <tbody>
                        @php $total = 0; @endphp
                        @foreach($ipc->ipcItems as $i => $item)
                        @php $amt = $item->current_amount ?? ($item->current_quantity * ($item->boqItem->unit_rate ?? 0)); $total += $amt; @endphp
                        <tr>
                            <td>{{ $i+1 }}</td>
                            <td>{{ $item->boqItem->description ?? 'N/A' }}</td>
                            <td>{{ $item->boqItem->unit ?? '-' }}</td>
                            <td>{{ number_format($item->current_quantity,2) }}</td>
                            <td>{{ number_format($item->boqItem->unit_rate??0,2) }}</td>
                            <td class="text-end fw-bold">{{ number_format($amt,2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot><tr class="table-primary fw-bold"><td colspan="5" class="text-end">Net Amount:</td><td class="text-end">{{ number_format($total,2) }} ETB</td></tr></tfoot>
                </table>
            </div>
        </div>

        <!-- APPROVAL ACTIONS - Simple Buttons -->
        @php
            $currentUser = auth()->user();
            $userRole = $currentUser->getRoleName();
            $status = $ipc->status;
            
            $canPrepare = in_array($userRole, ['admin','manager','engineer']);
            $canCheck = in_array($userRole, ['admin','manager','engineer']);
            $canSubmit = in_array($userRole, ['admin','manager','engineer']);
            $canApprove = in_array($userRole, ['admin','manager','finance']);
            $canReject = in_array($userRole, ['admin','manager','finance']);
            $canPay = in_array($userRole, ['admin','finance']);
        @endphp

        <div class="card">
            <div class="card-header"><h5 class="mb-0">✅ Actions</h5></div>
            <div class="card-body">
                <div class="d-flex gap-2 flex-wrap">
                    @if($status == 'draft' && $canPrepare)
                        <form action="{{ route('ipcs.prepare', $ipc) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-info">📝 Mark as Prepared</button>
                        </form>
                    @endif

                    @if(in_array($status, ['draft','prepared']) && $canCheck)
                        <form action="{{ route('ipcs.check', $ipc) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-warning">✅ Mark as Checked</button>
                        </form>
                    @endif

                    @if(in_array($status, ['draft','prepared','checked']) && $canSubmit)
                        <form action="{{ route('ipcs.submit', $ipc) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-primary">📤 Submit for Approval</button>
                        </form>
                    @endif

                    @if($status == 'submitted' && $canApprove)
                        <form action="{{ route('ipcs.approve', $ipc) }}" method="POST" onsubmit="return confirm('Approve this certificate?')">
                            @csrf
                            <button type="submit" class="btn btn-success">✔️ Approve</button>
                        </form>
                    @endif

                    @if($status == 'submitted' && $canReject)
                        <form action="{{ route('ipcs.reject', $ipc) }}" method="POST" onsubmit="return confirm('Reject this certificate?')">
                            @csrf
                            <button type="submit" class="btn btn-danger">❌ Reject</button>
                        </form>
                    @endif

                    @if($status == 'approved' && $canPay)
                        <form action="{{ route('ipcs.mark-paid', $ipc) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-dark">💰 Mark as Paid</button>
                        </form>
                    @endif
                </div>

                {{-- Status Messages --}}
                @if($status == 'paid')
                    <div class="alert alert-success mt-3 mb-0">✅ Fully processed and paid.</div>
                @endif
                @if($status == 'rejected')
                    <div class="alert alert-danger mt-3 mb-0">❌ Rejected - needs revision.</div>
                @endif
                @if($status == 'submitted' && !$canApprove && !$canReject)
                    <div class="alert alert-info mt-3 mb-0">📌 Waiting for Finance/Admin approval.</div>
                @endif
                @if($status == 'approved' && !$canPay)
                    <div class="alert alert-info mt-3 mb-0">📌 Waiting for Finance to process payment.</div>
                @endif
            </div>
        </div>
    </div>

    <!-- RIGHT: Approval Status -->
    <div class="col-md-5">
        <div class="card mb-4">
            <div class="card-header"><h5 class="mb-0">🔐 Approval Status</h5></div>
            <div class="card-body">
                @php
                    $steps = [
                        ['label' => 'Prepared By',  'user' => $ipc->prepared_by,  'date' => $ipc->prepared_at,  'status' => 'prepared'],
                        ['label' => 'Checked By',   'user' => $ipc->checked_by,   'date' => $ipc->checked_at,   'status' => 'checked'],
                        ['label' => 'Submitted By', 'user' => $ipc->submitted_by, 'date' => $ipc->submitted_at, 'status' => 'submitted'],
                        ['label' => 'Approved By',  'user' => $ipc->approved_by,  'date' => $ipc->approved_at,  'status' => 'approved'],
                        ['label' => 'Paid By',      'user' => $ipc->paid_by,      'date' => $ipc->paid_at,      'status' => 'paid'],
                    ];
                    $statusOrder = ['draft','prepared','checked','submitted','approved','paid'];
                    $currentIdx = array_search($ipc->status, $statusOrder);
                @endphp
                
                @foreach($steps as $step)
                    @php 
                        $stepIdx = array_search($step['status'], $statusOrder);
                        $isDone = $currentIdx !== false && $stepIdx <= $currentIdx && $ipc->status != 'rejected';
                    @endphp
                    <div class="d-flex align-items-start mb-3 pb-3 border-bottom">
                        <div style="width:28px;text-align:center;">
                            @if($isDone)<i class="fas fa-check-circle text-success fa-lg"></i>
                            @else<i class="far fa-circle text-muted fa-lg"></i>@endif
                        </div>
                        <div class="ms-3">
                            <strong>{{ $step['label'] }}</strong>
                            <div style="min-height:22px;">
                                @if(!empty($step['user']))
                                    <span class="text-primary fw-bold">👤 {{ $step['user'] }}</span>
                                @elseif($isDone)
                                    <span class="text-warning small">⚠️ Not recorded</span>
                                @else
                                    <span class="text-muted small">Pending...</span>
                                @endif
                            </div>
                            @if(!empty($step['date']))
                                <small class="text-muted">📅 {{ \Carbon\Carbon::parse($step['date'])->format('M d, Y h:i A') }}</small>
                            @endif
                        </div>
                    </div>
                @endforeach
                
                @if($ipc->status == 'rejected')
                    <div class="d-flex align-items-start mb-3">
                        <div style="width:28px;"><i class="fas fa-times-circle text-danger fa-lg"></i></div>
                        <div class="ms-3">
                            <strong>Rejected By</strong>
                            @if($ipc->rejected_by)<br><span class="text-danger fw-bold">👤 {{ $ipc->rejected_by }}</span>@endif
                            @if($ipc->rejected_at)<br><small>📅 {{ \Carbon\Carbon::parse($ipc->rejected_at)->format('M d, Y h:i A') }}</small>@endif
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Financial Summary -->
        <div class="card">
            <div class="card-header"><h5 class="mb-0">💰 Financial Summary</h5></div>
            <div class="card-body">
                @php
                    $net = $total; $vat = $net * 0.15; $gross = $net + $vat;
                    $ret = $net * (($ipc->retention_percentage??5)/100);
                    $prev = $ipc->total_previous_amount ?? 0; $ded = $prev + $ret; $due = $gross - $ded;
                @endphp
                <table class="table table-sm">
                    <tr><td>Net Amount:</td><td class="text-end">{{ number_format($net,2) }}</td></tr>
                    <tr><td>VAT (15%):</td><td class="text-end text-success">+{{ number_format($vat,2) }}</td></tr>
                    <tr class="fw-bold"><td>Gross:</td><td class="text-end">{{ number_format($gross,2) }}</td></tr>
                    <tr><td colspan="2"><hr class="my-1"></td></tr>
                    <tr><td>Prev. Payment:</td><td class="text-end text-danger">-{{ number_format($prev,2) }}</td></tr>
                    <tr><td>Retention ({{ $ipc->retention_percentage??5 }}%):</td><td class="text-end text-danger">-{{ number_format($ret,2) }}</td></tr>
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
