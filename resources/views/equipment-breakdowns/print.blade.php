<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance Report - {{ $equipmentBreakdown->report_code }}</title>
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
                size: A4 portrait;
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
        .report-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }
        .report-table th, .report-table td {
            border: 1px solid #000;
            padding: 4px;
        }
        .report-table thead th {
            background-color: #f2f2f2 !important;
            text-align: center;
            font-weight: bold;
        }
        .category-row td {
            background-color: #e5e5e5 !important;
            font-weight: bold;
            text-transform: uppercase;
        }
        .sig-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 10.5px;
        }
        .sig-table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: center;
            width: 33.33%;
        }
    </style>
</head>
<body>
    <div class="no-print p-2 bg-light border-bottom mb-3 text-center">
        <button onclick="window.print()" class="btn btn-primary btn-sm me-2">
            <strong>Print Official Document (A4)</strong>
        </button>
        <button onclick="window.close()" class="btn btn-secondary btn-sm">Close</button>
    </div>

    {{-- Official Header --}}
    <table class="doc-header-table">
        <tr>
            <td rowspan="3" style="width: 15%; text-align: center;">
                <img src="{{ asset('images/company-logo.png') }}" alt="TNT Logo" style="max-height: 48px;" onerror="this.onerror=null; this.src='{{ asset('images/tnt-logo.png') }}';">
                <div style="font-weight: bold; font-size: 9px; margin-top: 2px;">TNT CONSTRUCTION</div>
            </td>
            <td style="width: 55%; text-align: center;">
                <div style="font-size: 9px; color: #555;">Company name:</div>
                <div style="font-size: 13px; font-weight: bold; color: #000;">ቲኤንቲ ኮንስትራክሽንና ንግድ ሥራዎች</div>
                <div style="font-size: 13px; font-weight: bold; color: #000;">TNT CONSTRUCTION AND TRADING</div>
            </td>
            <td style="width: 30%; font-size: 10px;">
                <strong>Document №:</strong> &nbsp; <span style="font-size: 11px; font-weight: bold;">{{ $equipmentBreakdown->document_code ?: 'OF/TNT/ EAMD/030' }}</span>
            </td>
        </tr>
        <tr>
            <td rowspan="2" style="text-align: center;">
                <div style="font-size: 9px; color: #555;">Title:</div>
                <div style="font-size: 14px; font-weight: bold; text-transform: uppercase;">Maintenance Report</div>
            </td>
            <td style="font-size: 10px;">
                <strong>Issue №:</strong> {{ $equipmentBreakdown->issue_no ?: '1' }}
            </td>
        </tr>
        <tr>
            <td style="font-size: 10px;">
                <strong>Page №:</strong> {{ $equipmentBreakdown->page_no ?: 'Page 1 of 1' }}
            </td>
        </tr>
    </table>

    {{-- Date Strip --}}
    <div style="display: flex; justify-content: space-between; font-size: 10.5px; margin-bottom: 6px;">
        <div>
            <strong>Location / Site:</strong> {{ $equipmentBreakdown->location_name ?: ($equipmentBreakdown->project?->name ?: 'Central Workshop & Project Sites') }}
        </div>
        <div>
            <strong>Report Date:</strong> {{ $equipmentBreakdown->report_date ? $equipmentBreakdown->report_date->format('d/m/Y') : date('d/m/Y') }}
        </div>
    </div>

    {{-- Breakdown Items Table --}}
    <table class="report-table mb-2">
        <thead>
            <tr>
                <th style="width: 30px;">No.</th>
                <th>Equipment Type</th>
                <th style="width: 40px;">QTY</th>
                <th style="width: 90px;">PLATE No/ SN</th>
                <th style="width: 75px;">DOWN DATE</th>
                <th>REASON OF DOWN</th>
                <th style="width: 90px;">ACTION TAKEN</th>
                <th style="width: 90px;">LOCATION</th>
                <th style="width: 80px;">REPAIR STATUS</th>
                <th style="width: 75px;">FINISHED DATE</th>
            </tr>
        </thead>
        <tbody>
            @foreach($itemsGrouped as $categoryKey => $items)
                <tr class="category-row">
                    <td colspan="10">{{ $categoryKey }}</td>
                </tr>
                @php $rNo = 1; @endphp
                @foreach($items as $item)
                    <tr>
                        <td style="text-align: center;">{{ $rNo++ }}</td>
                        <td><strong>{{ $item->equipment_type }}</strong></td>
                        <td style="text-align: center;">{{ $item->quantity }}</td>
                        <td style="font-weight: bold;">{{ $item->plate_no_sn ?: '-' }}</td>
                        <td style="text-align: center;">{{ $item->down_date ? $item->down_date->format('d/m/Y') : '' }}</td>
                        <td style="color: #990000; font-weight: 500;">{{ $item->reason_of_down }}</td>
                        <td>{{ $item->action_taken }}</td>
                        <td>{{ $item->location }}</td>
                        <td style="text-align: center; text-transform: uppercase; font-weight: bold;">
                            {{ $item->repair_status }}
                        </td>
                        <td style="text-align: center;">{{ $item->finished_date ? $item->finished_date->format('d/m/Y') : '' }}</td>
                    </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>

    {{-- Official Signatures Table --}}
    <table class="sig-table">
        <tr>
            <td>
                <strong>PREPARED BY:</strong><br><br>
                <span style="font-size: 11px; font-weight: bold;">{{ $equipmentBreakdown->prepared_name ?: ($equipmentBreakdown->preparedByUser?->name ?: '________________') }}</span><br>
                <small style="color: #555;">Date: {{ $equipmentBreakdown->prepared_at ? $equipmentBreakdown->prepared_at->format('d/m/Y') : '___/___/2026' }}</small>
            </td>
            <td>
                <strong>CHECKED BY:</strong><br><br>
                <span style="font-size: 11px; font-weight: bold;">{{ $equipmentBreakdown->checked_name ?: ($equipmentBreakdown->checkedByUser?->name ?: '________________') }}</span><br>
                <small style="color: #555;">Date: {{ $equipmentBreakdown->checked_at ? $equipmentBreakdown->checked_at->format('d/m/Y') : '___/___/2026' }}</small>
            </td>
            <td>
                <strong>APPROVED BY:</strong><br><br>
                <span style="font-size: 11px; font-weight: bold;">{{ $equipmentBreakdown->approved_name ?: ($equipmentBreakdown->approvedByUser?->name ?: '________________') }}</span><br>
                <small style="color: #555;">Date: {{ $equipmentBreakdown->approved_at ? $equipmentBreakdown->approved_at->format('d/m/Y') : '___/___/2026' }}</small>
            </td>
        </tr>
    </table>
</body>
</html>
