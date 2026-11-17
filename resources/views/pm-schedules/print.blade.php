<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preventive Maintenance Schedule - {{ $pmSchedule->schedule_code }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, "Helvetica Neue", Helvetica, sans-serif;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 10px;
            font-size: 11px;
        }
        @media print {
            body { padding: 0; margin: 0; }
            .no-print { display: none !important; }
            @page {
                size: A4 landscape;
                margin: 8mm 6mm;
            }
            .table-bordered th, .table-bordered td {
                border: 1px solid #000 !important;
            }
        }
        .doc-header-table {
            border: 2px solid #000;
            width: 100%;
            margin-bottom: 8px;
            border-collapse: collapse;
        }
        .doc-header-table td {
            border: 1px solid #000;
            padding: 4px 6px;
            vertical-align: middle;
        }
        .schedule-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }
        .schedule-table th, .schedule-table td {
            border: 1px solid #000;
            padding: 3px 4px;
        }
        .schedule-table thead th {
            background-color: #f2f2f2 !important;
            text-align: center;
            font-weight: bold;
        }
        .category-row td {
            background-color: #e5e5e5 !important;
            font-weight: bold;
            text-transform: uppercase;
        }
        .legend-box {
            border: 1px solid #000;
            padding: 5px;
            font-size: 9.5px;
            background-color: #fafafa;
        }
        .sig-box {
            border: 1px solid #000;
            padding: 6px;
            font-size: 10px;
        }
    </style>
</head>
<body>
    <div class="no-print p-2 bg-light border-bottom mb-3 text-center">
        <button onclick="window.print()" class="btn btn-primary btn-sm me-2">
            <strong>Print Official Document (A4 Landscape)</strong>
        </button>
        <button onclick="window.close()" class="btn btn-secondary btn-sm">Close</button>
    </div>

    {{-- Official Header --}}
    <table class="doc-header-table">
        <tr>
            <td rowspan="3" style="width: 14%; text-align: center;">
                <img src="{{ asset('images/company-logo.png') }}" alt="TNT Logo" style="max-height: 48px;" onerror="this.onerror=null; this.src='{{ asset('images/tnt-logo.png') }}';">
                <div style="font-weight: bold; font-size: 9px; margin-top: 2px;">TNT CONSTRUCTION</div>
            </td>
            <td style="width: 58%; text-align: center;">
                <div style="font-size: 9px; color: #555;">Company name:</div>
                <div style="font-size: 13px; font-weight: bold; color: #000;">ቲ ኤን ቲ ኮንስትራክሽንና ንግድ ሥራዎች</div>
                <div style="font-size: 13px; font-weight: bold; color: #000;">TNT CONSTRUCTION AND TRADING</div>
            </td>
            <td style="width: 28%; font-size: 10px;">
                <strong>Document №:</strong> &nbsp; <span style="font-size: 11px; font-weight: bold;">{{ $pmSchedule->document_code ?: 'OF/EAMD/074' }}</span>
            </td>
        </tr>
        <tr>
            <td rowspan="2" style="text-align: center;">
                <div style="font-size: 9px; color: #555;">Title:</div>
                <div style="font-size: 14px; font-weight: bold; text-transform: uppercase;">Preventive Maintenance Schedule</div>
            </td>
            <td style="font-size: 10px;">
                <strong>Issue №:</strong> {{ $pmSchedule->issue_no ?: '1' }}
            </td>
        </tr>
        <tr>
            <td style="font-size: 10px;">
                <strong>Page №:</strong> {{ $pmSchedule->page_no ?: 'Page 1 of 1' }}
            </td>
        </tr>
    </table>

    {{-- Meta Strip --}}
    <div style="display: flex; justify-content: space-between; font-size: 10.5px; margin-bottom: 6px;">
        <div>
            <strong>Project / Site:</strong> {{ $pmSchedule->project_location ?: ($pmSchedule->project?->name ?: 'All Sites / Central Workshop') }}
        </div>
        <div>
            <strong>Date:</strong> {{ $pmSchedule->schedule_date ? $pmSchedule->schedule_date->format('d/m/Y') : date('d/m/Y') }}
            @if($pmSchedule->period_start && $pmSchedule->period_end)
                &nbsp;|&nbsp; <strong>Period:</strong> {{ $pmSchedule->period_start->format('d/m/Y') }} – {{ $pmSchedule->period_end->format('d/m/Y') }}
            @endif
        </div>
    </div>

    {{-- Schedule Items Table --}}
    <table class="schedule-table mb-2">
        <thead>
            <tr>
                <th rowspan="2" style="width: 25px;">No.</th>
                <th colspan="5">EQUIPMENT SPECIFICATION</th>
                <th rowspan="2" style="width: 80px;">Location</th>
                <th colspan="3">Last Service</th>
                <th colspan="2">Next Service</th>
                <th colspan="2">Current Reading</th>
                <th rowspan="2" style="width: 55px;">Remain<br>Hour/Km</th>
                <th rowspan="2" style="width: 55px;">Service<br>Status</th>
                <th rowspan="2" style="width: 100px;">Remark</th>
            </tr>
            <tr>
                <th>Type</th>
                <th>Make</th>
                <th>Model</th>
                <th>Plate/Serial No</th>
                <th>Serial No</th>
                <th>HR/Km</th>
                <th>Date</th>
                <th>Type</th>
                <th>HR/KM</th>
                <th>Type</th>
                <th>HR/KM</th>
                <th>DATE</th>
            </tr>
        </thead>
        <tbody>
            @php
                $categoryNames = [
                    'EARTH MOVING MACHINE' => 'EARTH MOVING MACHINE',
                    'DUMP TRUCK' => 'HEAVY TRUCKS / DUMP TRUCK',
                    'LIGHT VEHICLES' => 'LIGHT VEHICLES',
                    'AUXILIARY' => 'AUXILIARY & PLANT EQUIPMENT',
                ];
            @endphp
            @foreach($itemsGrouped as $categoryKey => $items)
                <tr class="category-row">
                    <td colspan="17">{{ $categoryNames[$categoryKey] ?? $categoryKey }}</td>
                </tr>
                @php $rNo = 1; @endphp
                @foreach($items as $item)
                    @php $rem = $item->remain_reading; @endphp
                    <tr>
                        <td style="text-align: center;">{{ $rNo++ }}</td>
                        <td><strong>{{ $item->equipment_type }}</strong></td>
                        <td>{{ $item->make }}</td>
                        <td>{{ $item->model }}</td>
                        <td style="font-weight: bold;">{{ $item->plate_serial_no }}</td>
                        <td>{{ $item->serial_no }}</td>
                        <td>{{ $item->location }}</td>
                        <td style="text-align: right;">{{ $item->last_service_reading !== null ? number_format($item->last_service_reading, 0) : '' }}</td>
                        <td style="text-align: center;">{{ $item->last_service_date ? $item->last_service_date->format('d/m/Y') : '' }}</td>
                        <td style="text-align: center; font-weight: bold;">{{ $item->last_service_type }}</td>
                        <td style="text-align: right; font-weight: bold;">{{ $item->next_service_reading !== null ? number_format($item->next_service_reading, 0) : '' }}</td>
                        <td style="text-align: center; font-weight: bold;">{{ $item->next_service_type }}</td>
                        <td style="text-align: right;">{{ $item->current_reading !== null ? number_format($item->current_reading, 0) : '' }}</td>
                        <td style="text-align: center;">{{ $item->current_reading_date ? $item->current_reading_date->format('d/m/Y') : '' }}</td>
                        <td style="text-align: right; font-weight: bold; {{ $rem < 0 ? 'color: red;' : '' }}">
                            {{ $rem !== null ? number_format($rem, 0) : '' }}
                        </td>
                        <td style="text-align: center; font-weight: bold; {{ $item->service_status == 'Overdue' ? 'color: red;' : ($item->service_status == 'Due' ? 'color: #b8860b;' : 'color: green;') }}">
                            {{ $item->service_status }}
                        </td>
                        <td>{{ $item->remark }}</td>
                    </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>

    {{-- Footer Legend & Sign-offs --}}
    <div style="display: flex; gap: 8px; margin-top: 8px;">
        <div style="flex: 1.2;" class="legend-box">
            <div style="font-weight: bold; margin-bottom: 2px;">Service Type Reference Legend:</div>
            <div><strong>Earth Moving / Heavy:</strong> Type A (250 hr), Type B (500 hr), Type C (1,000 hr), Type D (2,000 hr)</div>
            <div><strong>Light Vehicles / Pickups:</strong> Type A2 (5,000 km), Type B2 (10,000 km), Type C2 (20,000 km), Type D2 (40,000 km)</div>
        </div>
        <div style="flex: 1.5; display: flex; justify-content: space-between; border: 1px solid #000; padding: 6px; font-size: 10px;">
            <div style="width: 32%;">
                <strong>Prepared By:</strong><br>
                {{ $pmSchedule->prepared_name ?: ($pmSchedule->preparedByUser?->name ?: '________________') }}<br>
                <span style="font-size: 9px; color: #555;">Date: {{ $pmSchedule->prepared_at ? $pmSchedule->prepared_at->format('d/m/Y') : '___/___/2026' }}</span>
            </div>
            <div style="width: 32%;">
                <strong>Checked By:</strong><br>
                {{ $pmSchedule->checked_name ?: ($pmSchedule->checkedByUser?->name ?: '________________') }}<br>
                <span style="font-size: 9px; color: #555;">Date: {{ $pmSchedule->checked_at ? $pmSchedule->checked_at->format('d/m/Y') : '___/___/2026' }}</span>
            </div>
            <div style="width: 32%;">
                <strong>Approved By:</strong><br>
                {{ $pmSchedule->approved_name ?: ($pmSchedule->approvedByUser?->name ?: '________________') }}<br>
                <span style="font-size: 9px; color: #555;">Date: {{ $pmSchedule->approved_at ? $pmSchedule->approved_at->format('d/m/Y') : '___/___/2026' }}</span>
            </div>
        </div>
    </div>
</body>
</html>
