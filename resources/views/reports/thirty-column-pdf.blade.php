<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>30 Column Budget Report</title>
    <style>
        @page { size: A3 landscape; margin: 10mm; }
        body { font-family: Arial, sans-serif; font-size: 6px; }
        h2 { text-align: center; font-size: 12px; color: #1a237e; margin: 0 0 3px; }
        .subtitle { text-align: center; font-size: 7px; color: #666; margin-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 0.3px solid #999; padding: 2px; text-align: center; }
        thead th { background: #4472C4; color: white; font-size: 5px; font-weight: bold; }
        .cat-row td { background: #D6E4F0; font-weight: bold; text-align: left; font-size: 7px; }
        .total-row td { background: #E2EFDA; font-weight: bold; }
        .grand-total td { background: #002060; color: white; font-weight: bold; font-size: 7px; }
        .profit { color: green; } .loss { color: red; }
        .text-right { text-align: right; }
        .footer { text-align: center; font-size: 5px; color: #999; margin-top: 8px; border-top: 1px solid #ddd; padding-top: 3px; }
    </style>
</head>
<body>
    <h2>30-COLUMN BUDGET & COST BREAKDOWN REPORT</h2>
    <p class="subtitle">Project: {{ $selectedProject->name ?? 'All Projects' }} | Date: {{ now()->format('F d, Y') }}</p>

    <table>
        <thead>
            <tr>
                <th>Cat</th><th>Item No.</th><th>DESCRIPTION</th><th>Unit</th><th>Rate</th><th>Qty</th><th>REVENUE</th>
                <th>Dur</th><th>Start</th><th>End</th>
                <th colspan="5">LABOUR</th>
                <th colspan="4">MATERIAL</th>
                <th colspan="6">EQUIPMENT</th>
                <th>TOTAL BUDGET</th><th>P/L</th><th>%</th><th>STATUS</th>
            </tr>
            <tr>
                <th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th>
                <th>TRADE</th><th>NO.</th><th>HOURS</th><th>WAGE</th><th>AMOUNT</th>
                <th>Desc</th><th>Unit</th><th>QTY</th><th>RATE</th>
                <th>DESC</th><th>DAYS</th><th>NO.</th><th>HOURS</th><th>RATE</th><th>AMOUNT</th>
                <th></th><th></th><th></th><th></th>
            </tr>
        </thead>
        <tbody>
            @php $gr = 0; $gb = 0; @endphp
            @foreach($groupedItems as $cat => $items)
                @php $cr = $items->sum('revenue_amount'); $cb = $items->sum(fn($i)=>$i->total_budget_cost); $gr+=$cr; $gb+=$cb; @endphp
                <tr class="cat-row"><td colspan="29">{{ $cat }} (Rev: {{ number_format($cr,2) }} | Bud: {{ number_format($cb,2) }})</td></tr>
                @foreach($items as $item)
                    @php $l=$item->laborResources; $m=$item->materialResources; $e=$item->equipmentResources; $mx=max($l->count(),$m->count(),$e->count(),1); @endphp
                    @for($i=0;$i<$mx;$i++)
                        <tr>
                            @if($i==0)
                                <td>{{ $item->costCategory->code??'' }}</td><td>{{ $item->item_number }}</td>
                                <td style="text-align:left;">{{ $item->description }}</td><td>{{ $item->unit }}</td>
                                <td class="text-right">{{ number_format($item->unit_rate,2) }}</td>
                                <td class="text-right">{{ number_format($item->quantity,2) }}</td>
                                <td class="text-right">{{ number_format($item->revenue_amount,2) }}</td>
                                <td>{{ $item->duration_days??'-' }}</td>
                                <td>{{ $item->planned_start_date?$item->planned_start_date->format('m/d/Y'):'-' }}</td>
                                <td>{{ $item->planned_end_date?$item->planned_end_date->format('m/d/Y'):'-' }}</td>
                            @else<td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>@endif
                            @if(isset($l[$i]))<td>{{$l[$i]->trade_name}}</td><td>{{number_format($l[$i]->number_of_workers,1)}}</td><td>{{number_format($l[$i]->total_hours,1)}}</td><td>{{number_format($l[$i]->wage_per_day,2)}}</td><td class="text-right">{{number_format($l[$i]->amount,2)}}</td>@else<td>-</td><td>-</td><td>-</td><td>-</td><td>-</td>@endif
                            @if(isset($m[$i]))<td>{{$m[$i]->description}}</td><td>{{$m[$i]->unit}}</td><td class="text-right">{{number_format($m[$i]->quantity,2)}}</td><td class="text-right">{{number_format($m[$i]->unit_rate,2)}}</td>@else<td>-</td><td>-</td><td>-</td><td>-</td>@endif
                            @if(isset($e[$i]))<td>{{$e[$i]->description}}</td><td>{{number_format($e[$i]->duration_days,1)}}</td><td>{{$e[$i]->number_of_units}}</td><td>{{number_format($e[$i]->total_hours,1)}}</td><td>{{number_format($e[$i]->rate_per_hour,2)}}</td><td class="text-right">{{number_format($e[$i]->amount,2)}}</td>@else<td>-</td><td>-</td><td>-</td><td>-</td><td>-</td><td>-</td>@endif
                            @if($i==0)<td class="text-right">{{number_format($item->total_budget_cost,2)}}</td><td class="text-right {{$item->profit_loss>=0?'profit':'loss'}}">{{number_format($item->profit_loss,2)}}</td><td>{{number_format($item->profit_margin_percentage,1)}}%</td><td>{{$item->profit_loss_status}}</td>@else<td></td><td></td><td></td><td></td>@endif
                        </tr>
                    @endfor
                @endforeach
            @endforeach
            @if($groupedItems->count()>0)
            <tr class="grand-total">
                <td colspan="6" style="text-align:right;">GRAND TOTAL:</td>
                <td class="text-right">{{ number_format($gr,2) }}</td>
                <td colspan="18"></td>
                <td class="text-right">{{ number_format($gb,2) }}</td>
                <td class="text-right {{($gr-$gb)>=0?'profit':'loss'}}">{{ number_format($gr-$gb,2) }}</td>
                <td>{{ $gr>0?number_format(($gr-$gb)/$gr*100,1):0 }}%</td>
                <td>{{ ($gr-$gb)>=0?'PROFIT':'LOSS' }}</td>
            </tr>
            @endif
        </tbody>
    </table>
    <div class="footer">Generated by Construction Management System | {{ date('F d, Y H:i') }}</div>
</body>
</html>
