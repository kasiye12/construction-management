@extends('layouts.app')
@section('title', $costCategory->name . ' - CMS')
@section('content')
<div class="page-header"><div class="d-flex justify-content-between"><div><h2>📂 {{ $costCategory->code }} - {{ $costCategory->name }}</h2></div><a href="{{ route('cost-categories.edit', $costCategory) }}" class="btn btn-warning"><i class="fas fa-edit me-1"></i> Edit</a></div></div>
<div class="row"><div class="col-md-8"><div class="card"><div class="card-header"><h5 class="mb-0">BOQ Items ({{ $costCategory->boqItems->count() }})</h5></div>
<div class="table-responsive"><table class="table mb-0"><thead class="table-light"><tr><th>Item No</th><th>Description</th><th>Unit</th><th>Revenue</th></tr></thead><tbody>
@foreach($costCategory->boqItems as $item)
<tr><td>{{ $item->item_number }}</td><td><a href="{{ route('boq-items.show', $item) }}">{{ Str::limit($item->description, 50) }}</a></td><td>{{ $item->unit }}</td><td class="text-end">{{ number_format($item->revenue_amount, 2) }}</td></tr>
@endforeach
</tbody></table></div></div></div></div>
@endsection
