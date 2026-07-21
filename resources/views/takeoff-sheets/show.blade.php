@extends('layouts.app')

@section('title', 'Takeoff Sheet ' . $sheet->sheet_number . ' - CMS')

@section('content')
<div class="page-header no-print">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2>📐 Takeoff Sheet: {{ $sheet->sheet_number }}</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('takeoff-sheets.index') }}">Takeoff Sheets</a></li>
                    <li class="breadcrumb-item active">{{ $sheet->sheet_number }}</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            {{-- Verify Button - Draft -> Verified --}}
            @if($sheet->status == 'draft' && $canVerify)
                <form action="{{ route('takeoff-sheets.verify', $sheet) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-info btn-sm"><i class="fas fa-check me-1"></i> Verify</button>
                </form>
            @endif
            
            {{-- Approve Button - Verified -> Approved --}}
            @if($sheet->status == 'verified' && $canApprove)
                <form action="{{ route('takeoff-sheets.approve', $sheet) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-success btn-sm"><i class="fas fa-thumbs-up me-1"></i> Approve</button>
                </form>
            @endif
            
            {{-- Revert to Verified - Approved -> Verified (Approver/Admin) --}}
            @if($canRevertToVerified)
                <form action="{{ route('takeoff-sheets.revert-to-verified', $sheet) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-warning btn-sm"><i class="fas fa-undo me-1"></i> Revert to Verified</button>
                </form>
            @endif
            
            {{-- Revert to Draft - Verified/Approved -> Draft (Verifier/Admin) --}}
            @if($canRevertToDraft)
                <form action="{{ route('takeoff-sheets.revert', $sheet) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-undo me-1"></i> Revert to Draft</button>
                </form>
            @endif
            
            {{-- Edit Button - Only Draft + Creator --}}
            @if($canEdit)
                <a href="{{ route('takeoff-sheets.edit', $sheet) }}" class="btn btn-primary btn-sm"><i class="fas fa-edit me-1"></i> Edit</a>
            @endif
            
            {{-- Print Button --}}
            <a href="{{ url('takeoff-sheets/'.$sheet->id.'/print') }}" class="btn btn-outline-dark btn-sm" target="_blank"><i class="fas fa-print me-1"></i> Print</a>
            
            {{-- Delete Button - Only Draft + Creator --}}
            @if($canDelete)
                <form action="{{ route('takeoff-sheets.destroy', $sheet) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this sheet?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger btn-sm"><i class="fas fa-trash me-1"></i> Delete</button>
                </form>
            @endif
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-sm table-borderless">
                    <tr><td class="text-muted">Project:</td><td><strong>{{ $sheet->project->name ?? 'N/A' }}</strong></td></tr>
                    <tr><td class="text-muted">Sheet No:</td><td><strong>{{ $sheet->sheet_number }}</strong></td></tr>
                    <tr><td class="text-muted">Division:</td><td>{{ $sheet->division ?? 'N/A' }}</td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-sm table-borderless">
                    <tr><td class="text-muted">Date:</td><td>{{ optional($sheet->measurement_date)->format('M d, Y') ?? 'N/A' }}</td></tr>
                    <tr><td class="text-muted">Status:</td><td>
                        <span class="badge bg-{{ $sheet->status=='approved'?'success':($sheet->status=='verified'?'info':'warning') }}">
                            {{ strtoupper($sheet->status) }}
                        </span>
                    </td></tr>
                    <tr><td class="text-muted">Measured By:</td><td>{{ $sheet->measured_by ?? 'N/A' }}</td></tr>
                    @if($sheet->verified_by)
                    <tr><td class="text-muted">Verified By:</td><td>{{ $sheet->verified_by }}</td></tr>
                    @endif
                    @if($sheet->approved_by)
                    <tr><td class="text-muted">Approved By:</td><td>{{ $sheet->approved_by }}</td></tr>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>

@if($sheet->items->count() > 0)
    @foreach($sheet->items as $item)
    @php
        $leftDescs = collect();
        $rightDescs = collect();
        foreach($item->descriptions as $desc) {
            if($desc->side == 'left') $leftDescs->push($desc);
            else $rightDescs->push($desc);
        }
    @endphp
    
    <div class="card mb-3">
        <div class="card-header" style="background:#1a237e;color:white;">
            <h6 class="mb-0">Item {{ $item->item_number }}: {{ $item->description ?: ($item->boqItem ? $item->boqItem->description : 'No description') }}</h6>
        </div>
        <div class="card-body">
            <div class="row">
                {{-- LEFT SIDE --}}
                <div class="col-md-6">
                    @if($leftDescs->count() > 0)
                        @foreach($leftDescs as $desc)
                        <div class="border rounded p-3 mb-2" style="border-left: 3px solid #4f46e5 !important;">
                            <h6 class="text-primary">LEFT: {{ $desc->description }}</h6>
                            @if($desc->measurements->count() > 0)
                            <table class="table table-sm table-bordered">
                                <thead>
                                    <tr><th>#</th><th>Description</th><th>Qty</th><th>Length</th><th>Width</th><th>Height</th><th>Area</th></tr>
                                </thead>
                                <tbody>
                                    @foreach($desc->measurements as $m)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $m->description }}</td>
                                        <td>{{ $m->quantity_count }}</td>
                                        <td>{{ number_format($m->length, 2) }}</td>
                                        <td>{{ number_format($m->width, 2) }}</td>
                                        <td>{{ number_format($m->height_depth, 2) }}</td>
                                        <td class="fw-bold">{{ number_format($m->total_area_volume, 2) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="table-primary fw-bold">
                                        <td colspan="6" class="text-end">Total:</td>
                                        <td>{{ number_format($desc->total_area, 2) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                            @else
                            <p class="text-muted">No measurements</p>
                            @endif
                        </div>
                        @endforeach
                    @else
                        <div class="border rounded p-3 mb-2">
                            <h6 class="text-primary">LEFT Side</h6>
                            <p class="text-muted">No left descriptions</p>
                        </div>
                    @endif
                </div>
                
                {{-- RIGHT SIDE --}}
                <div class="col-md-6">
                    @if($rightDescs->count() > 0)
                        @foreach($rightDescs as $desc)
                        <div class="border rounded p-3 mb-2" style="border-left: 3px solid #10b981 !important;">
                            <h6 class="text-success">RIGHT: {{ $desc->description }}</h6>
                            @if($desc->measurements->count() > 0)
                            <table class="table table-sm table-bordered">
                                <thead>
                                    <tr><th>#</th><th>Description</th><th>Qty</th><th>Length</th><th>Width</th><th>Height</th><th>Area</th></tr>
                                </thead>
                                <tbody>
                                    @foreach($desc->measurements as $m)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $m->description }}</td>
                                        <td>{{ $m->quantity_count }}</td>
                                        <td>{{ number_format($m->length, 2) }}</td>
                                        <td>{{ number_format($m->width, 2) }}</td>
                                        <td>{{ number_format($m->height_depth, 2) }}</td>
                                        <td class="fw-bold">{{ number_format($m->total_area_volume, 2) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="table-primary fw-bold">
                                        <td colspan="6" class="text-end">Total:</td>
                                        <td>{{ number_format($desc->total_area, 2) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                            @else
                            <p class="text-muted">No measurements</p>
                            @endif
                        </div>
                        @endforeach
                    @else
                        <div class="border rounded p-3 mb-2">
                            <h6 class="text-success">RIGHT Side</h6>
                            <p class="text-muted">No right descriptions</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endforeach

    @php
        $grandTotal = 0;
        foreach($sheet->items as $item) {
            foreach($item->descriptions as $desc) {
                $grandTotal += $desc->measurements->sum('total_area_volume');
            }
        }
    @endphp

    <div class="card">
        <div class="card-header" style="background:#002060;color:white;">
            <h5 class="mb-0">Grand Total: {{ number_format($grandTotal, 2) }}</h5>
        </div>
    </div>
@else
    <div class="text-center py-5 text-muted">
        <h4>No items added yet</h4>
        <p>This takeoff sheet has no items or measurements.</p>
    </div>
@endif
@endsection
