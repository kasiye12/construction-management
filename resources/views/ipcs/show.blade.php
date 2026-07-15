@extends('layouts.app')

@section('title', $ipc->ipc_number . ' - Payment Certificate')

@push('styles')
<style>
    .workflow-container { position: relative; }
    .workflow-steps { display: flex; justify-content: space-between; position: relative; }
    .workflow-steps::before {
        content: ''; position: absolute; top: 20px; left: 10%; right: 10%;
        height: 3px; background: #e5e7eb; z-index: 0;
    }
    .workflow-step { text-align: center; position: relative; z-index: 1; flex: 1; }
    .workflow-step .step-circle {
        width: 40px; height: 40px; border-radius: 50%;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 1rem; border: 3px solid #e5e7eb; background: white;
    }
    .workflow-step.completed .step-circle { background: #10b981; border-color: #10b981; color: white; }
    .workflow-step.current .step-circle { background: #4f46e5; border-color: #4f46e5; color: white; box-shadow: 0 0 0 5px rgba(79,70,229,0.2); }
    .workflow-step.rejected .step-circle { background: #ef4444; border-color: #ef4444; color: white; }
    .workflow-step .step-label { font-size: 0.65rem; margin-top: 4px; font-weight: 600; text-transform: uppercase; }
    .workflow-step .step-user { font-size: 0.65rem; color: #6b7280; }
    .user-chip {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 5px 12px; border-radius: 20px; border: 2px solid #e5e7eb;
        cursor: pointer; font-size: 0.75rem; transition: all 0.2s;
    }
    .user-chip:hover { border-color: #4f46e5; background: #eef2ff; }
    .user-chip.selected { border-color: #4f46e5; background: #4f46e5; color: white; font-weight: 600; }
    .stamp {
        border: 3px solid #10b981; color: #10b981; padding: 10px 20px;
        border-radius: 50%; font-weight: 700; font-size: 1rem;
        transform: rotate(-15deg); display: inline-block; opacity: 0.7;
    }
</style>
@endpush

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

<!-- WORKFLOW STATUS BAR -->
<div class="card mb-4">
    <div class="card-body">
        <h5 class="mb-3 text-center">📋 Approval Workflow Status</h5>
        <div class="workflow-container">
            <div class="workflow-steps">
                @php
                    $allSteps = ['draft','prepared','checked','submitted','approved','paid'];
                    if($ipc->status == 'rejected') $allSteps = ['draft','prepared','checked','submitted','rejected'];
                    $currentIdx = array_search($ipc->status, $allSteps);
                @endphp
                @foreach($allSteps as $index => $step)
                    @php
                        $isComplete = $currentIdx !== false && $index <= $currentIdx;
                        $isCurrent = $index == $currentIdx;
                        $stepInfo = \App\Services\WorkflowService::WORKFLOW[$step] ?? ['label'=>ucfirst($step),'icon'=>'circle','color'=>'secondary'];
                        $fieldMap = ['prepared'=>'prepared_by','checked'=>'checked_by','submitted'=>'submitted_by','approved'=>'approved_by','rejected'=>'rejected_by','paid'=>'paid_by'];
                        $dateMap = ['prepared'=>'prepared_at','checked'=>'checked_at','submitted'=>'submitted_at','approved'=>'approved_at','rejected'=>'rejected_at','paid'=>'paid_at'];
                        $user = $ipc->{$fieldMap[$step] ?? ''} ?? null;
                        $date = $ipc->{$dateMap[$step] ?? ''} ?? null;
                    @endphp
                    <div class="workflow-step {{ $isComplete ? 'completed' : '' }} {{ $isCurrent ? 'current' : '' }}">
                        <div class="step-circle"><i class="fas fa-{{ $stepInfo['icon'] }}"></i></div>
                        <div class="step-label">{{ $stepInfo['label'] }}</div>
                        @if($user)<div class="step-user">👤 {{ $user }}</div>@endif
                        @if($date)<div class="step-user">{{ \Carbon\Carbon::parse($date)->format('M d') }}</div>@endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-7">
        <!-- Info Card -->
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
                        <span class="badge bg-{{ \App\Services\WorkflowService::getStatusColor($ipc->status) }} fs-6">
                            {{ strtoupper(\App\Services\WorkflowService::getStatusLabel($ipc->status)) }}
                        </span>
                        @if($ipc->status == 'approved')<div class="stamp mt-2">APPROVED</div>@endif
                        @if($ipc->status == 'paid')<div class="stamp mt-2" style="border-color:#3b82f6;color:#3b82f6;">PAID</div>@endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Work Items Table -->
        <div class="card mb-4">
            <div class="card-header"><h5 class="mb-0">📋 Work Executed</h5></div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Item Description</th>
                            <th>Unit</th>
                            <th>Contract Qty</th>
                            <th>Rate</th>
                            <th>Previous Qty</th>
                            <th>Previous Amount</th>
                            <th>Current Qty</th>
                            <th>Amount (ETB)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $total = 0; @endphp
                        @foreach($ipc->ipcItems as $i => $item)
                        @php $amt = $item->current_amount ?? ($item->current_quantity * ($item->boqItem->unit_rate ?? 0)); $total += $amt; @endphp
                        <tr>
                            <td>{{ $i+1 }}</td>
                            <td>{{ $item->boqItem->description ?? 'N/A' }}</td>
                            <td>{{ $item->boqItem->unit ?? '-' }}</td>
                            <td class="text-end">{{ number_format($item->contract_quantity, 2) }}</td>
                            <td class="text-end">{{ number_format($item->contract_rate ?? $item->boqItem->unit_rate, 2) }}</td>
                            <td class="text-end">{{ number_format($item->previous_quantity, 2) }}</td>
                            <td class="text-end">{{ number_format($item->previous_amount, 2) }}</td>
                            <td class="text-end fw-bold">{{ number_format($item->current_quantity, 2) }}</td>
                            <td class="text-end fw-bold">{{ number_format($amt, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="table-primary fw-bold">
                            <td colspan="8" class="text-end">Total Work Done:</td>
                            <td class="text-end">{{ number_format($total, 2) }} ETB</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Actions -->
        @if(count($availableActions) > 0)
        <div class="card">
            <div class="card-header"><h5 class="mb-0">✅ Available Actions</h5></div>
            <div class="card-body">
                @foreach($availableActions as $action)
                <div class="border rounded p-3 mb-3">
                    <h6><i class="fas fa-{{ $action['icon'] }} text-{{ $action['color'] }} me-2"></i>{{ $action['label'] }}</h6>
                    <div class="d-flex flex-wrap gap-2 mb-2">
                        @foreach($users as $u)
                        <span class="user-chip {{ $loop->first ? 'selected' : '' }}" 
                              data-action="{{ $action['status'] }}" data-name="{{ $u->name }}"
                              onclick="selectUser(this, '{{ $action['status'] }}')">
                            👤 {{ $u->name }}
                        </span>
                        @endforeach
                    </div>
                    <form action="{{ route($action['route_name'], $ipc) }}" method="POST" id="form-{{ $action['status'] }}"
                          @if(in_array($action['status'], ['approved','rejected'])) onsubmit="return confirm('Confirm this action?')" @endif>
                        @csrf
                        <input type="hidden" name="{{ $action['status'] }}_by_name" id="input-{{ $action['status'] }}" value="{{ $users->first()->name ?? auth()->user()->name }}">
                        <button type="submit" class="btn btn-{{ $action['color'] }}">
                            <i class="fas fa-{{ $action['icon'] }} me-1"></i> {{ $action['label'] }}
                        </button>
                    </form>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        @if($ipc->status == 'paid')<div class="alert alert-success"><i class="fas fa-check-circle me-2"></i>Fully processed and paid.</div>@endif
        @if($ipc->status == 'rejected')<div class="alert alert-danger"><i class="fas fa-times-circle me-2"></i>Rejected - needs revision.</div>@endif
    </div>

    <!-- Right Column -->
    <div class="col-md-5">
        <div class="card">
            <div class="card-header"><h5 class="mb-0">💰 Financial Summary</h5></div>
            <div class="card-body">
                @php
                    $net = $total;
                    $vat = $net * 0.15; $gross = $net + $vat;
                    $ret = $net * (($ipc->retention_percentage??5)/100);
                    $prev = $ipc->total_previous_amount ?? 0;
                    $ded = $prev + $ret; $due = $gross - $ded;
                @endphp
                <table class="table table-sm">
                    <tr><td>Net Amount:</td><td class="text-end">{{ number_format($net,2) }}</td></tr>
                    <tr><td>VAT (15%):</td><td class="text-end text-success">+{{ number_format($vat,2) }}</td></tr>
                    <tr class="fw-bold"><td>Gross Amount:</td><td class="text-end">{{ number_format($gross,2) }}</td></tr>
                    <tr><td colspan="2"><hr class="my-1"></td></tr>
                    <tr><td>Previous Payment:</td><td class="text-end text-danger">-{{ number_format($prev,2) }}</td></tr>
                    <tr><td>Retention ({{ $ipc->retention_percentage??5 }}%):</td><td class="text-end text-danger">-{{ number_format($ret,2) }}</td></tr>
                    <tr><td colspan="2"><hr class="my-1"></td></tr>
                    <tr class="fw-bold fs-6"><td>Net Due:</td><td class="text-end text-success">{{ number_format($due,2) }} ETB</td></tr>
                </table>
                <div class="p-3 mt-2 rounded" style="background:#fffde7;border:1px solid #f59e0b;">
                    <small class="text-muted">Amount in Words:</small><br>
                    <strong>{{ \App\Helpers\NumberToWordsHelper::convert($due) }} Ethiopian Birr Only</strong>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function selectUser(chip, action) {
    chip.parentElement.querySelectorAll('.user-chip').forEach(c => c.classList.remove('selected'));
    chip.classList.add('selected');
    document.getElementById('input-' + action).value = chip.dataset.name;
}
</script>
@endpush
