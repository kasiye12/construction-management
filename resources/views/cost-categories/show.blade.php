@extends('layouts.app')

@section('title', $costCategory->name . ' - CMS')

@section('content')
<div class="page-header">
    <h2>{{ $costCategory->code }} - {{ $costCategory->name }}</h2>
</div>

<div class="table-card">
    <h5>BOQ Items in this Category</h5>
    <hr>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Item No.</th>
                    <th>Description</th>
                    <th>Unit</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse($costCategory->boqItems as $item)
                <tr>
                    <td>{{ $item->item_number }}</td>
                    <td><a href="{{ route('boq-items.show', $item) }}">{{ $item->description }}</a></td>
                    <td>{{ $item->unit }}</td>
                    <td>{{ number_format($item->revenue_amount, 2) }}</td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center">No items</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
