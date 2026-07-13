<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>30 Column Budget Report</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 10px; }
        h2 { text-align: center; margin-bottom: 5px; color: #002060; font-size: 14px; }
        .subtitle { text-align: center; color: #666; margin-bottom: 15px; font-size: 10px; }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            font-size: 6px;
        }
        th, td { 
            border: 0.5px solid #333; 
            padding: 2px; 
            text-align: center;
        }
        thead th { 
            background-color: #4472C4; 
            color: white; 
            font-weight: bold;
            font-size: 5px;
        }
        .category-header { 
            background-color: #D6E4F0; 
            font-weight: bold; 
            text-align: left;
            font-size: 7px;
        }
        .total-row { 
            background-color: #E2EFDA; 
            font-weight: bold;
        }
        .grand-total { 
            background-color: #002060; 
            color: white; 
            font-weight: bold;
            font-size: 7px;
        }
        .profit { color: green; }
        .loss { color: red; }
        .amount-cell { text-align: right; }
    </style>
</head>
<body>
    <h2>30-COLUMN BUDGET & COST BREAKDOWN REPORT</h2>
    <p class="subtitle">
        Project: {{ $selectedProject->name ?? 'All Projects' }} | 
        Date: {{ now()->format('F d, Y') }}
    </p>

    <table>
        <thead>
            <tr>
                <th>Cost Category</th><th>Item No.</th><th>ITEM DESCRIPTION</th>
                <th>UNIT</th><th>Rate</th><th>Qty</th><th>REVENUE</th>
                <th>Dur.</th><th>Start</th><th>End</th>
                <th colspan="5">LABOUR</th>
                <th colspan="4">MATERIAL</th>
                <th colspan="6">EQUIPMENT</th>
                <th>TOTAL BUDGET</th><th>P/L</th><th>%</th><th>STATUS</th>
            </tr>
        </thead>
        <tbody>
            @foreach($groupedItems as $categoryName => $categoryItems)
                <tr class="category-header">
                    <td colspan="30">{{ $categoryName }}</td>
                </tr>
                @foreach($categoryItems as $item)
                    @php
                        $laborResources = $item->laborResources;
                        $materialResources = $item->materialResources;
                        $equipmentResources = $item->equipmentResources;
                        $maxRows = max($laborResources->count(), $materialResources->count(), $equipmentResources->count(), 1);
                    @endphp
                    @for($i = 0; $i < $maxRows; $i++)
                        <tr>
                            @if($i == 0)
                                <td rowspan="{{ $maxRows }}">{{ $item->costCategory->code ?? '' }}</td>
                                <td rowspan="{{ $maxRows }}">{{ $item->item_number }}</td>
                                <td rowspan="{{ $maxRows }}" style="text-align:left;">{{ $item->description }}</td>
                                <td rowspan="{{ $maxRows }}">{{ $item->unit }}</td>
                                <td rowspan="{{ $maxRows }}" class="amount-cell">{{ number_format($item->unit_rate, 2) }}</td>
                                <td rowspan="{{ $maxRows }}" class="amount-cell">{{ number_format($item->quantity, 2) }}</td>
                                <td rowspan="{{ $maxRows }}" class="amount-cell">{{ number_format($item->revenue_amount, 2) }}</td>
                                <td rowspan="{{ $maxRows }}">{{ $item->duration_days ?? '-' }}</td>
                                <td rowspan="{{ $maxRows }}">{{ $item->planned_start_date ? $item->planned_start_date->format('m/d/Y') : '-' }}</td>
                                <td rowspan="{{ $maxRows }}">{{ $item->planned_end_date ? $item->planned_end_date->format('m/d/Y') : '-' }}</td>
                            @endif
                            
                            @if(isset($laborResources[$i]))
                                <td>{{ $laborResources[$i]->trade_name }}</td>
                                <td>{{ number_format($laborResources[$i]->number_of_workers, 2) }}</td>
                                <td>{{ number_format($laborResources[$i]->total_hours, 2) }}</td>
                                <td>{{ number_format($laborResources[$i]->wage_per_day, 2) }}</td>
                                <td class="amount-cell">{{ number_format($laborResources[$i]->amount, 2) }}</td>
                            @else
                                <td>-</td><td>-</td><td>-</td><td>-</td><td>-</td>
                            @endif
                            
                            @if(isset($materialResources[$i]))
                                <td>{{ $materialResources[$i]->description }}</td>
                                <td>{{ $materialResources[$i]->unit }}</td>
                                <td class="amount-cell">{{ number_format($materialResources[$i]->quantity, 2) }}</td>
                                <td class="amount-cell">{{ number_format($materialResources[$i]->unit_rate, 2) }}</td>
                            @else
                                <td>-</td><td>-</td><td>-</td><td>-</td>
                            @endif
                            
                            @if(isset($equipmentResources[$i]))
                                <td>{{ $equipmentResources[$i]->description }}</td>
                                <td>{{ number_format($equipmentResources[$i]->duration_days, 2) }}</td>
                                <td>{{ $equipmentResources[$i]->number_of_units }}</td>
                                <td>{{ number_format($equipmentResources[$i]->total_hours, 2) }}</td>
                                <td>{{ number_format($equipmentResources[$i]->rate_per_hour, 2) }}</td>
                                <td class="amount-cell">{{ number_format($equipmentResources[$i]->amount, 2) }}</td>
                            @else
                                <td>-</td><td>-</td><td>-</td><td>-</td><td>-</td><td>-</td>
                            @endif
                            
                            @if($i == 0)
                                <td rowspan="{{ $maxRows }}" class="amount-cell">{{ number_format($item->total_budget_cost, 2) }}</td>
                                <td rowspan="{{ $maxRows }}" class="amount-cell {{ $item->profit_loss >= 0 ? 'profit' : 'loss' }}">
                                    {{ number_format($item->profit_loss, 2) }}
                                </td>
                                <td rowspan="{{ $maxRows }}">{{ number_format($item->profit_margin_percentage, 1) }}%</td>
                                <td rowspan="{{ $maxRows }}">{{ $item->profit_loss_status }}</td>
                            @endif
                        </tr>
                    @endfor
                @endforeach
            @endforeach
        </tbody>
    </table>
</body>
</html>
