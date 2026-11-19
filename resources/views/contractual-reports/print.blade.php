<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $contractualReport->report_number }} - OF/TNT/CAD/01</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            color: #000;
            font-size: 11px;
            margin: 0;
            padding: 0;
        }

        .print-page {
            background: #fff;
            width: 210mm;
            min-height: 297mm;
            padding: 12mm 15mm;
            margin: 10px auto;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            position: relative;
            box-sizing: border-box;
        }

        @media print {
            body {
                background: #fff;
                margin: 0;
            }
            .print-page {
                box-shadow: none;
                margin: 0;
                width: 100%;
                min-height: 100vh;
                padding: 10mm 12mm;
                page-break-after: always;
            }
            .no-print {
                display: none !important;
            }
        }

        .header-table, .data-table, .meta-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table td, .header-table th {
            border: 1px solid #000;
            padding: 4px 6px;
            vertical-align: middle;
        }

        .data-table td, .data-table th {
            border: 1px solid #333;
            padding: 4px 5px;
            font-size: 10.5px;
        }

        .meta-line {
            border-bottom: 1px solid #555;
            padding-bottom: 2px;
            margin-bottom: 4px;
        }

        .check-mark {
            font-weight: bold;
            font-size: 12px;
            color: #000;
        }

        .sig-block {
            border-top: 1px solid #333;
            margin-top: 25px;
            padding-top: 5px;
        }
    </style>
</head>
<body>

<!-- Floating Action Bar for Web View -->
<div class="no-print bg-dark text-white p-2 sticky-top text-center d-flex justify-content-center align-items-center gap-3 shadow">
    <span><strong>OF/TNT/CAD/01</strong> - Weekly Contractual Follow-up Reporting Form</span>
    <button onclick="window.print()" class="btn btn-primary btn-sm"><i class="fas fa-print me-1"></i> Print / Save as PDF</button>
    <a href="{{ route('contractual-reports.excel', $contractualReport) }}" class="btn btn-success btn-sm"><i class="fas fa-file-excel me-1"></i> Download Official Excel</a>
    <a href="{{ route('contractual-reports.show', $contractualReport) }}" class="btn btn-outline-light btn-sm"><i class="fas fa-arrow-left me-1"></i> Back to System</a>
</div>

<!-- ========================================== -->
<!-- PAGE 1: Causes of Claim - Follow up Form -->
<!-- ========================================== -->
<div class="print-page">
    <!-- Header Block -->
    <table class="header-table mb-3">
        <tr>
            <td rowspan="3" style="width: 15%; text-align: center;">
                <img src="{{ asset('images/company-logo.png') }}" alt="TNT Logo" style="height: 55px; width: 55px; object-fit: contain;">
            </td>
            <td colspan="4" style="text-align: center;">
                <div style="font-size: 10px; color: #555;">Company name:</div>
                <div style="font-size: 15px; font-weight: bold;">ቲኤንቲ ኮንስትራክሽንና ንግድ ሥራዎች</div>
                <div style="font-size: 13px; font-weight: bold; letter-spacing: 0.5px;">TNT CONSTRUCTION AND TRADING</div>
            </td>
            <td colspan="2" style="width: 25%; vertical-align: top;">
                <div style="font-size: 10px;">Document №:</div>
                <div style="font-weight: bold; font-size: 12px;">{{ $contractualReport->document_code }}</div>
            </td>
        </tr>
        <tr>
            <td rowspan="2" style="font-size: 10px; width: 8%;">Title:</td>
            <td rowspan="2" colspan="3" style="text-align: center; font-weight: bold; font-size: 13px;">
                Weekly Contractual Follow-up Reporting Form
            </td>
            <td style="width: 12%; font-size: 9px;">Issue №:</td>
            <td style="width: 13%; font-size: 9px;">Page №:</td>
        </tr>
        <tr>
            <td style="text-align: center; font-weight: bold;">{{ $contractualReport->issue_no }}</td>
            <td style="text-align: center; font-weight: bold;">Page 1 of 3</td>
        </tr>
    </table>

    <!-- Project Info Block -->
    <div class="mb-2">
        <div class="meta-line"><strong>Project:</strong> {{ $contractualReport->project?->name ?? 'Unknown / Archived Project' }}</div>
        <div class="meta-line"><strong>Project Location:</strong> {{ $contractualReport->project_location ?: 'Site Location' }}</div>
        <div class="meta-line"><strong>Client:</strong> {{ $contractualReport->client_name ?: 'N/A' }}</div>
        <div class="meta-line"><strong>Consultant:</strong> {{ $contractualReport->consultant_name ?: 'N/A' }}</div>
        <div class="meta-line"><strong>Contractor:</strong> {{ $contractualReport->contractor_name }}</div>
        <div class="text-end fw-bold mt-2" style="font-size: 11px;">
            Date: From <u>{{ $contractualReport->week_start_date->format('d/m/Y') }}</u> To <u>{{ $contractualReport->week_end_date->format('d/m/Y') }}</u>
        </div>
    </div>

    <!-- Section Title -->
    <div style="font-weight: bold; font-size: 13px; margin: 8px 0 6px 0; border-bottom: 2px solid #000; padding-bottom: 2px;">
        Causes of Claim - Follow up Form
    </div>

    <!-- Page 1 Table -->
    <table class="data-table text-center mb-2">
        <thead>
            <tr style="background-color: #f0f0f0;">
                <th rowspan="2" class="text-start" style="width: 44%;">Claim Issues encountered throughout the week</th>
                <th colspan="2">Monday</th>
                <th colspan="2">Tuesday</th>
                <th colspan="2">Wednesday</th>
                <th colspan="2">Thursday</th>
                <th colspan="2">Friday</th>
                <th colspan="2">Saturday</th>
                <th colspan="2">Sunday</th>
            </tr>
            <tr style="background-color: #f7f7f7; font-size: 9.5px;">
                <th>Yes</th><th>No</th>
                <th>Yes</th><th>No</th>
                <th>Yes</th><th>No</th>
                <th>Yes</th><th>No</th>
                <th>Yes</th><th>No</th>
                <th>Yes</th><th>No</th>
                <th>Yes</th><th>No</th>
            </tr>
        </thead>
        <tbody>
            @php $groupedChecks = $contractualReport->claimChecks->groupBy('category_title'); @endphp
            @foreach($groupedChecks as $catTitle => $checks)
                <tr style="background-color: #e9ecef; font-weight: bold; text-align: left;">
                    <td colspan="15" style="padding: 3px 6px;">{{ $catTitle }}</td>
                </tr>
                @foreach($checks as $chk)
                    <tr>
                        <td class="text-start" style="padding-left: 10px;">{{ $chk->item_code }} {{ $chk->item_title }}</td>
                        @foreach(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $d)
                            @if($chk->$d)
                                <td class="check-mark">✓</td>
                                <td></td>
                            @else
                                <td></td>
                                <td class="check-mark">✓</td>
                            @endif
                        @endforeach
                    </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>

    <div style="font-size: 9.5px; font-style: italic; margin-bottom: 15px;">
        N.B:- Detailed information of the claim case encountered has to be filled and submitted weekly on the Format TNT/CAD/01 Page 02.
    </div>

    @if($contractualReport->page1_notes)
        <div style="border: 1px solid #ddd; padding: 5px; font-size: 10px; margin-bottom: 15px;">
            <strong>Notes:</strong> {{ $contractualReport->page1_notes }}
        </div>
    @endif

    <!-- Sign-off Block -->
    <table class="w-100" style="margin-top: 30px; font-size: 10.5px;">
        <tr>
            <td style="width: 33%; vertical-align: bottom;">
                <div>Prepared By: <u>{{ $contractualReport->prepared_name ?: ($contractualReport->preparedByUser?->name ?: '_______________') }}</u></div>
                <div style="font-size: 9px; color: #555; margin-top: 4px;">Date: {{ $contractualReport->prepared_at ? $contractualReport->prepared_at->format('d/m/Y') : '___________' }}</div>
            </td>
            <td style="width: 34%; text-align: center; vertical-align: bottom;">
                <div>Checked By: <u>{{ $contractualReport->checked_name ?: ($contractualReport->checkedByUser?->name ?: '_______________') }}</u></div>
                <div style="font-size: 9px; color: #555; margin-top: 4px;">Date: {{ $contractualReport->checked_at ? $contractualReport->checked_at->format('d/m/Y') : '___________' }}</div>
            </td>
            <td style="width: 33%; text-align: right; vertical-align: bottom;">
                <div>Approved By: <u>{{ $contractualReport->approved_name ?: ($contractualReport->approvedByUser?->name ?: '_______________') }}</u></div>
                <div style="font-size: 9px; color: #555; margin-top: 4px;">Date: {{ $contractualReport->approved_at ? $contractualReport->approved_at->format('d/m/Y') : '___________' }}</div>
            </td>
        </tr>
    </table>
</div>

<!-- ========================================== -->
<!-- PAGE 2: Detailed Information of Claim Event -->
<!-- ========================================== -->
<div class="print-page">
    <!-- Header Block -->
    <table class="header-table mb-3">
        <tr>
            <td rowspan="3" style="width: 15%; text-align: center;">
                <img src="{{ asset('images/company-logo.png') }}" alt="TNT Logo" style="height: 55px; width: 55px; object-fit: contain;">
            </td>
            <td colspan="4" style="text-align: center;">
                <div style="font-size: 10px; color: #555;">Company name:</div>
                <div style="font-size: 15px; font-weight: bold;">ቲኤንቲ ኮንስትራክሽንና ንግድ ሥራዎች</div>
                <div style="font-size: 13px; font-weight: bold; letter-spacing: 0.5px;">TNT CONSTRUCTION AND TRADING</div>
            </td>
            <td colspan="2" style="width: 25%; vertical-align: top;">
                <div style="font-size: 10px;">Document №:</div>
                <div style="font-weight: bold; font-size: 12px;">{{ $contractualReport->document_code }}</div>
            </td>
        </tr>
        <tr>
            <td rowspan="2" style="font-size: 10px; width: 8%;">Title:</td>
            <td rowspan="2" colspan="3" style="text-align: center; font-weight: bold; font-size: 13px;">
                Weekly Contractual Follow-up Reporting Form
            </td>
            <td style="width: 12%; font-size: 9px;">Issue №:</td>
            <td style="width: 13%; font-size: 9px;">Page №:</td>
        </tr>
        <tr>
            <td style="text-align: center; font-weight: bold;">{{ $contractualReport->issue_no }}</td>
            <td style="text-align: center; font-weight: bold;">Page 2 of 3</td>
        </tr>
    </table>

    <!-- Project Info Block -->
    <div class="mb-2">
        <div class="meta-line"><strong>Project:</strong> {{ $contractualReport->project?->name ?? 'Unknown / Archived Project' }}</div>
        <div class="meta-line"><strong>Project Location:</strong> {{ $contractualReport->project_location ?: 'Site Location' }}</div>
        <div class="meta-line"><strong>Client:</strong> {{ $contractualReport->client_name ?: 'N/A' }}</div>
        <div class="meta-line"><strong>Consultant:</strong> {{ $contractualReport->consultant_name ?: 'N/A' }}</div>
        <div class="meta-line"><strong>Contractor:</strong> {{ $contractualReport->contractor_name }}</div>
        <div class="text-end fw-bold mt-2" style="font-size: 11px;">
            Date: From <u>{{ $contractualReport->week_start_date->format('d/m/Y') }}</u> To <u>{{ $contractualReport->week_end_date->format('d/m/Y') }}</u>
        </div>
    </div>

    <!-- Section Title -->
    <div style="font-weight: bold; font-size: 13px; margin: 8px 0 6px 0; border-bottom: 2px solid #000; padding-bottom: 2px;">
        Detailed Information of the Claim Event
    </div>

    <!-- Page 2 Table -->
    <table class="data-table mb-3">
        <thead>
            <tr style="background-color: #f0f0f0;">
                <th style="width: 12%;">Date</th>
                <th style="width: 14%;">Issues enc. (No)</th>
                <th style="width: 28%;">Detail description of the cause</th>
                <th style="width: 20%;">Effect on the progress</th>
                <th style="width: 16%;">Action taken</th>
                <th style="width: 10%;">Remark</th>
            </tr>
        </thead>
        <tbody>
            @php $events = $contractualReport->claimEvents; @endphp
            @for($i = 0; $i < max(6, $events->count()); $i++)
                @php $evt = $events[$i] ?? null; @endphp
                <tr style="min-height: 28px;">
                    <td style="height: 28px;">{{ $evt ? ($evt->event_date ? $evt->event_date->format('d/m/Y') : '') : '' }}</td>
                    <td>{{ $evt?->issue_number }}</td>
                    <td>{{ $evt?->cause_description }}</td>
                    <td>{{ $evt?->effect_on_progress }}</td>
                    <td>{{ $evt?->action_taken }}</td>
                    <td>{{ $evt?->remark }}</td>
                </tr>
            @endfor
        </tbody>
    </table>

    <div style="margin-top: 15px; font-size: 10.5px;">
        <strong>Remark:</strong> {{ $contractualReport->page2_remark ?: '_____________________________________________________________________________________' }}
    </div>

    <!-- Sign-off Block -->
    <table class="w-100" style="margin-top: 40px; font-size: 10.5px;">
        <tr>
            <td style="width: 33%; vertical-align: bottom;">
                <div>Prepared By: <u>{{ $contractualReport->prepared_name ?: ($contractualReport->preparedByUser?->name ?: '_______________') }}</u></div>
                <div style="font-size: 9px; color: #555; margin-top: 4px;">Date: {{ $contractualReport->prepared_at ? $contractualReport->prepared_at->format('d/m/Y') : '___________' }}</div>
            </td>
            <td style="width: 34%; text-align: center; vertical-align: bottom;">
                <div>Checked By: <u>{{ $contractualReport->checked_name ?: ($contractualReport->checkedByUser?->name ?: '_______________') }}</u></div>
                <div style="font-size: 9px; color: #555; margin-top: 4px;">Date: {{ $contractualReport->checked_at ? $contractualReport->checked_at->format('d/m/Y') : '___________' }}</div>
            </td>
            <td style="width: 33%; text-align: right; vertical-align: bottom;">
                <div>Approved By: <u>{{ $contractualReport->approved_name ?: ($contractualReport->approvedByUser?->name ?: '_______________') }}</u></div>
                <div style="font-size: 9px; color: #555; margin-top: 4px;">Date: {{ $contractualReport->approved_at ? $contractualReport->approved_at->format('d/m/Y') : '___________' }}</div>
            </td>
        </tr>
    </table>
</div>

<!-- ========================================== -->
<!-- PAGE 3: Site Correspondence - Follow up Form -->
<!-- ========================================== -->
<div class="print-page">
    <!-- Header Block -->
    <table class="header-table mb-3">
        <tr>
            <td rowspan="3" style="width: 15%; text-align: center;">
                <img src="{{ asset('images/company-logo.png') }}" alt="TNT Logo" style="height: 55px; width: 55px; object-fit: contain;">
            </td>
            <td colspan="4" style="text-align: center;">
                <div style="font-size: 10px; color: #555;">Company name:</div>
                <div style="font-size: 15px; font-weight: bold;">ቲኤንቲ ኮንስትራክሽንና ንግድ ሥራዎች</div>
                <div style="font-size: 13px; font-weight: bold; letter-spacing: 0.5px;">TNT CONSTRUCTION AND TRADING</div>
            </td>
            <td colspan="2" style="width: 25%; vertical-align: top;">
                <div style="font-size: 10px;">Document №:</div>
                <div style="font-weight: bold; font-size: 12px;">{{ $contractualReport->document_code }}</div>
            </td>
        </tr>
        <tr>
            <td rowspan="2" style="font-size: 10px; width: 8%;">Title:</td>
            <td rowspan="2" colspan="3" style="text-align: center; font-weight: bold; font-size: 13px;">
                Weekly Contractual Follow-up Reporting Form
            </td>
            <td style="width: 12%; font-size: 9px;">Issue №:</td>
            <td style="width: 13%; font-size: 9px;">Page №:</td>
        </tr>
        <tr>
            <td style="text-align: center; font-weight: bold;">{{ $contractualReport->issue_no }}</td>
            <td style="text-align: center; font-weight: bold;">Page 3 of 3</td>
        </tr>
    </table>

    <!-- Project Info Block -->
    <div class="mb-2">
        <div class="meta-line"><strong>Project Location:</strong> {{ $contractualReport->project_location ?: 'Site Location' }}</div>
        <div class="meta-line"><strong>Client:</strong> {{ $contractualReport->client_name ?: 'N/A' }}</div>
        <div class="meta-line"><strong>Consultant:</strong> {{ $contractualReport->consultant_name ?: 'N/A' }}</div>
        <div class="meta-line"><strong>Contractor:</strong> {{ $contractualReport->contractor_name }}</div>
        <div class="text-end fw-bold mt-2" style="font-size: 11px;">
            Date: From <u>{{ $contractualReport->week_start_date->format('d/m/Y') }}</u> To <u>{{ $contractualReport->week_end_date->format('d/m/Y') }}</u>
        </div>
    </div>

    <!-- Section Title -->
    <div style="font-weight: bold; font-size: 13px; margin: 8px 0 6px 0; border-bottom: 2px solid #000; padding-bottom: 2px;">
        Site Correspondence - Follow up Form
    </div>

    <!-- Page 3 Table -->
    <table class="data-table text-center mb-2">
        <thead>
            <tr style="background-color: #f0f0f0;">
                <th rowspan="2" class="text-start" style="width: 44%;">Correspondences</th>
                <th colspan="2">Monday</th>
                <th colspan="2">Tuesday</th>
                <th colspan="2">Wednesday</th>
                <th colspan="2">Thursday</th>
                <th colspan="2">Friday</th>
                <th colspan="2">Saturday</th>
                <th colspan="2">Sunday</th>
            </tr>
            <tr style="background-color: #f7f7f7; font-size: 9.5px;">
                <th>Yes</th><th>No</th>
                <th>Yes</th><th>No</th>
                <th>Yes</th><th>No</th>
                <th>Yes</th><th>No</th>
                <th>Yes</th><th>No</th>
                <th>Yes</th><th>No</th>
                <th>Yes</th><th>No</th>
            </tr>
        </thead>
        <tbody>
            @php $groupedCorresp = $contractualReport->correspondenceChecks->groupBy('direction_title'); @endphp
            @foreach($groupedCorresp as $dirTitle => $checks)
                <tr style="background-color: #e9ecef; font-weight: bold; text-align: left;">
                    <td colspan="15" style="padding: 3px 6px;">{{ $dirTitle }}</td>
                </tr>
                @foreach($checks as $chk)
                    <tr>
                        <td class="text-start" style="padding-left: 10px;">{{ $chk->item_code }} {{ $chk->party_title }}</td>
                        @foreach(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $d)
                            @if($chk->$d)
                                <td class="check-mark">✓</td>
                                <td></td>
                            @else
                                <td></td>
                                <td class="check-mark">✓</td>
                            @endif
                        @endforeach
                    </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>

    <div style="margin-top: 15px; font-size: 10.5px;">
        <strong>Remark:</strong> {{ $contractualReport->page3_remark ?: 'None' }}
    </div>

    <div style="font-size: 10px; font-style: italic; margin-top: 10px;">
        Note: The Letters shall be attached.
    </div>

    @if($contractualReport->documents->count() > 0)
        <div style="margin-top: 10px; border: 1px solid #ccc; padding: 6px; font-size: 10px;">
            <strong>Attached Correspondence Letters:</strong>
            <ul style="margin: 4px 0 0 15px; padding: 0;">
                @foreach($contractualReport->documents as $doc)
                    <li>{{ $doc->name }} ({{ $doc->file_size_formatted }})</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Sign-off Block -->
    <table class="w-100" style="margin-top: 40px; font-size: 10.5px;">
        <tr>
            <td style="width: 33%; vertical-align: bottom;">
                <div>Prepared By: <u>{{ $contractualReport->prepared_name ?: ($contractualReport->preparedByUser?->name ?: '_______________') }}</u></div>
                <div style="font-size: 9px; color: #555; margin-top: 4px;">Date: {{ $contractualReport->prepared_at ? $contractualReport->prepared_at->format('d/m/Y') : '___________' }}</div>
            </td>
            <td style="width: 34%; text-align: center; vertical-align: bottom;">
                <div>Checked By: <u>{{ $contractualReport->checked_name ?: ($contractualReport->checkedByUser?->name ?: '_______________') }}</u></div>
                <div style="font-size: 9px; color: #555; margin-top: 4px;">Date: {{ $contractualReport->checked_at ? $contractualReport->checked_at->format('d/m/Y') : '___________' }}</div>
            </td>
            <td style="width: 33%; text-align: right; vertical-align: bottom;">
                <div>Approved By: <u>{{ $contractualReport->approved_name ?: ($contractualReport->approvedByUser?->name ?: '_______________') }}</u></div>
                <div style="font-size: 9px; color: #555; margin-top: 4px;">Date: {{ $contractualReport->approved_at ? $contractualReport->approved_at->format('d/m/Y') : '___________' }}</div>
            </td>
        </tr>
    </table>
</div>

</body>
</html>
