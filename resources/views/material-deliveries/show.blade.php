@extends('layouts.app')

@section('title', 'Delivery #' . $materialDelivery->id . ' - CMS')

@push('styles')
<style>
    .delivery-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.06);
        border: 1px solid #e5e7eb;
    }
    .delivery-card .info-row {
        display: flex;
        padding: 10px 14px;
        border-bottom: 1px solid #f3f4f6;
    }
    .delivery-card .info-row:last-child { border-bottom: none; }
    .delivery-card .info-label {
        width: 150px;
        font-weight: 600;
        color: #4b5563;
        font-size: 0.8rem;
        flex-shrink: 0;
    }
    .delivery-card .info-value {
        flex: 1;
        font-size: 0.85rem;
        color: #1f2937;
    }
    .status-badge-lg {
        display: inline-block;
        padding: 6px 16px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.8rem;
    }
    .status-confirmed { background: #d1fae5; color: #065f46; }
    .status-recorded { background: #fef3c7; color: #92400e; }
    .stamp-watermark {
        position: absolute;
        right: 30px;
        top: 40%;
        transform: translateY(-50%) rotate(-20deg);
        font-size: 48px;
        font-weight: 900;
        opacity: 0.06;
        pointer-events: none;
        text-transform: uppercase;
    }
    .stamp-confirmed { color: #10b981; }
    .stamp-recorded { color: #f59e0b; }
</style>
@endpush

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h2>📦 Material Delivery Details</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('material-deliveries.index') }}">Material Deliveries</a></li>
                    <li class="breadcrumb-item active">Delivery #{{ $materialDelivery->id }}</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            {{-- Confirm Button --}}
            @if($materialDelivery->status == 'recorded' && $canConfirm)
            <form action="{{ route('material-deliveries.confirm', $materialDelivery) }}" method="POST" class="d-inline">
                @csrf
                <button class="btn btn-success">
                    <i class="fas fa-check-circle me-1"></i> Confirm Delivery
                </button>
            </form>
            @endif
            
            {{-- Revert Button (Admin only) --}}
            @if($materialDelivery->status == 'confirmed' && auth()->user()->isAdmin())
            <form action="{{ route('material-deliveries.revert', $materialDelivery) }}" method="POST" class="d-inline">
                @csrf
                <button class="btn btn-outline-warning">
                    <i class="fas fa-undo me-1"></i> Revert
                </button>
            </form>
            @endif
            
            {{-- Edit Button --}}
            @if($materialDelivery->status != 'confirmed' || auth()->user()->isAdmin())
            <a href="{{ route('material-deliveries.edit', $materialDelivery) }}" class="btn btn-outline-primary">
                <i class="fas fa-edit me-1"></i> Edit
            </a>
            @endif
            
            {{-- Delete Button --}}
            @if($materialDelivery->status != 'confirmed' || auth()->user()->isAdmin())
            <form action="{{ route('material-deliveries.destroy', $materialDelivery) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this delivery record?')">
                @csrf @method('DELETE')
                <button class="btn btn-outline-danger"><i class="fas fa-trash"></i></button>
            </form>
            @endif
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="delivery-card position-relative">
            {{-- Watermark Stamp --}}
            @if($materialDelivery->status == 'confirmed')
                <div class="stamp-watermark stamp-confirmed">CONFIRMED</div>
            @else
                <div class="stamp-watermark stamp-recorded">RECORDED</div>
            @endif

            <div class="card-header" style="background:white;border-bottom:1px solid #e5e7eb;padding:14px 16px;">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-truck-loading me-2"></i>
                        Delivery Information
                    </h5>
                    <span class="status-badge-lg {{ $materialDelivery->status == 'confirmed' ? 'status-confirmed' : 'status-recorded' }}">
                        @if($materialDelivery->status == 'confirmed')
                            ✅ Confirmed
                        @else
                            📝 Recorded
                        @endif
                    </span>
                </div>
            </div>
            
            <div class="card-body p-0">
                <div class="info-row">
                    <div class="info-label">📁 Project</div>
                    <div class="info-value"><strong>{{ $materialDelivery->project->name ?? 'N/A' }}</strong></div>
                </div>
                <div class="info-row">
                    <div class="info-label">👤 Subcontractor</div>
                    <div class="info-value">{{ $materialDelivery->subcontractor->name ?? 'N/A' }}</div>
                </div>
                <div class="info-row" style="background:#f9fafb;">
                    <div class="info-label">🧱 Material</div>
                    <div class="info-value"><strong>{{ $materialDelivery->item_description }}</strong></div>
                </div>
                <div class="info-row">
                    <div class="info-label">📏 Unit</div>
                    <div class="info-value">{{ $materialDelivery->unit }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">🔢 Quantity</div>
                    <div class="info-value"><strong>{{ number_format($materialDelivery->quantity, 2) }}</strong> {{ $materialDelivery->unit }}</div>
                </div>
                @if($materialDelivery->unit_multiplier > 1)
                <div class="info-row">
                    <div class="info-label">✖️ Multiplier</div>
                    <div class="info-value">{{ number_format($materialDelivery->unit_multiplier, 2) }}</div>
                </div>
                <div class="info-row" style="background:#f0fdf4;">
                    <div class="info-label">✅ Converted Qty</div>
                    <div class="info-value" style="font-size:1rem;font-weight:700;color:#065f46;">
                        {{ number_format($materialDelivery->converted_quantity, 2) }}
                    </div>
                </div>
                @endif
                <div class="info-row">
                    <div class="info-label">🎫 Gate Pass No.</div>
                    <div class="info-value">
                        @if($materialDelivery->gate_pass_number)
                            <span class="badge bg-dark fs-6">{{ $materialDelivery->gate_pass_number }}</span>
                        @else
                            <span class="text-muted">N/A</span>
                        @endif
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-label">📅 Delivery Date</div>
                    <div class="info-value">{{ $materialDelivery->delivery_date->format('F d, Y') }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">📍 Source Location</div>
                    <div class="info-value">{{ $materialDelivery->source_location ?? 'N/A' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">👤 Recorded By</div>
                    <div class="info-value">{{ $materialDelivery->creator->name ?? 'N/A' }}</div>
                </div>
                
                {{-- Confirmation Details --}}
                @if($materialDelivery->status == 'confirmed')
                <div class="info-row" style="background:#d1fae5;">
                    <div class="info-label">✔️ Confirmed By</div>
                    <div class="info-value">
                        <strong>{{ $materialDelivery->confirmedBy->name ?? 'N/A' }}</strong>
                        @if($materialDelivery->confirmed_at)
                            <br><small class="text-muted">On {{ $materialDelivery->confirmed_at->format('M d, Y h:i A') }}</small>
                        @endif
                    </div>
                </div>
                @endif

                @if($materialDelivery->remarks)
                <div class="info-row">
                    <div class="info-label">📝 Remarks</div>
                    <div class="info-value">{{ $materialDelivery->remarks }}</div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-4">
        {{-- Quick Summary Card --}}
        <div class="card mb-3">
            <div class="card-header"><h6 class="mb-0">📊 Quick Summary</h6></div>
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <tr><td>Material:</td><td class="text-end"><strong>{{ Str::limit($materialDelivery->item_description, 25) }}</strong></td></tr>
                    <tr><td>Quantity:</td><td class="text-end">{{ number_format($materialDelivery->quantity, 2) }} {{ $materialDelivery->unit }}</td></tr>
                    <tr><td>Gate Pass:</td><td class="text-end">{{ $materialDelivery->gate_pass_number ?? 'N/A' }}</td></tr>
                    <tr><td>Date:</td><td class="text-end">{{ $materialDelivery->delivery_date->format('M d, Y') }}</td></tr>
                    <tr><td>Status:</td>
                        <td class="text-end">
                            <span class="badge {{ $materialDelivery->status == 'confirmed' ? 'bg-success' : 'bg-warning text-dark' }}">
                                {{ ucfirst($materialDelivery->status) }}
                            </span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        {{-- Workflow Info --}}
        <div class="card">
            <div class="card-header"><h6 class="mb-0">🔄 Workflow Status</h6></div>
            <div class="card-body">
                <div class="d-flex align-items-start mb-2">
                    <div style="width:24px;"><i class="fas fa-check-circle text-{{ $materialDelivery->status == 'recorded' || $materialDelivery->status == 'confirmed' ? 'success' : 'muted' }}"></i></div>
                    <div class="ms-2">
                        <strong>Recorded</strong>
                        <br><small class="text-muted">{{ $materialDelivery->creator->name ?? 'N/A' }}</small>
                        <br><small class="text-muted">{{ $materialDelivery->created_at->format('M d, Y H:i') }}</small>
                    </div>
                </div>
                <div class="d-flex align-items-start">
                    <div style="width:24px;"><i class="fas fa-check-circle text-{{ $materialDelivery->status == 'confirmed' ? 'success' : 'muted' }}"></i></div>
                    <div class="ms-2">
                        <strong>Confirmed</strong>
                        @if($materialDelivery->status == 'confirmed')
                            <br><small class="text-success">{{ $materialDelivery->confirmedBy->name ?? 'N/A' }}</small>
                            <br><small class="text-muted">{{ $materialDelivery->confirmed_at ? $materialDelivery->confirmed_at->format('M d, Y H:i') : '' }}</small>
                        @else
                            <br><small class="text-muted">Pending...</small>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
