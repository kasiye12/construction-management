<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment Certificate - {{ $ipc->ipc_number }}</title>
    <style>
        @page { size: A4; margin: 8mm 10mm; }
        
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 7.5px;
            color: #1a1a1a;
            line-height: 1.35;
        }
        
        .certificate {
            width: 100%;
            max-width: 190mm;
            margin: 0 auto;
            border: 2px solid #1a237e;
            padding: 5px 7px;
            position: relative;
        }
        
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 4px; }
        .header-table td { padding: 2px 4px; vertical-align: middle; font-size: 7.5px; }
        .logo-cell { width: 55px; text-align: center; }
        .logo-img { width: 50px; height: 50px; object-fit: contain; border-radius: 4px; }
        .logo-placeholder { font-size: 30px; }
        .company-name { font-size: 11px; font-weight: bold; color: #1a237e; text-align: center; }
        .company-sub { font-size: 9px; font-weight: bold; color: #1a237e; text-align: center; }
        .doc-box { text-align: right; font-size: 6.5px; border: 1px solid #333; padding: 3px 5px; }
        
        .cert-title { text-align: center; font-size: 11px; font-weight: bold; border: 1.5px solid #1a237e; padding: 5px; margin: 4px 0; background: #e8eaf6; color: #1a237e; letter-spacing: 1px; }
        .cert-subtitle { text-align: center; font-size: 8.5px; font-weight: bold; margin: 2px 0; color: #1a237e; }
        
        .info-table { width: 100%; border-collapse: collapse; font-size: 7.5px; margin: 4px 0; }
        .info-table td { padding: 2px 4px; vertical-align: top; }
        .info-label { font-weight: bold; width: 85px; font-size: 7px; color: #555; }
        
        .contract-table { width: 100%; border-collapse: collapse; font-size: 6.5px; }
        .contract-table td { border: 0.5px solid #ccc; padding: 2px 4px; }
        .contract-table .ct-header { background: #e8eaf6; font-weight: bold; font-size: 6.5px; text-align: center; }
        .contract-table .ct-value { text-align: right; font-weight: bold; color: #1a237e; }
        
        .work-desc { border: 1px solid #999; padding: 5px 6px; margin: 4px 0; font-size: 7.5px; background: #fafafa; }
        
        .amount-section { border: 1.5px solid #1a237e; margin: 4px 0; }
        .amount-header { background: #1a237e; color: white; padding: 3px 6px; font-size: 7.5px; font-weight: bold; text-align: center; }
        .amount-table { width: 100%; border-collapse: collapse; font-size: 7.5px; }
        .amount-table td { padding: 2px 4px; border-bottom: 0.5px solid #ddd; }
        .amount-table .text-right { text-align: right; }
        .amount-table .text-center { text-align: center; }
        .amount-table .total-row td { font-weight: bold; border-top: 1px solid #1a237e; font-size: 8.5px; }
        .amount-table .net-due td { background: #1a237e; color: white; font-weight: bold; font-size: 9px; padding: 4px; }
        
        .deduction-table { width: 100%; border-collapse: collapse; font-size: 7px; margin: 3px 0; }
        .deduction-table th { background: #e8eaf6; border: 0.5px solid #999; padding: 2px 4px; font-size: 6.5px; text-align: center; font-weight: bold; }
        .deduction-table td { border: 0.5px solid #999; padding: 2px 4px; }
        .deduction-table .text-right { text-align: right; }
        .deduction-table .text-center { text-align: center; }
        .deduction-table .ded-total td { font-weight: bold; background: #f5f5f5; }
        
        .amount-words { text-align: center; font-weight: bold; font-size: 7.5px; margin: 4px 0; padding: 5px; border: 1px solid #f59e0b; background: #fffde7; }
        .amount-words .birr { font-size: 9px; color: #1a237e; }
        
        .signatures { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 7.5px; }
        .signatures td { text-align: center; padding: 3px 8px; width: 33%; }
        .sig-box { border: 1px solid #ddd; padding: 8px 6px; min-height: 42px; }
        .sig-name { font-weight: bold; font-size: 8.5px; color: #1a237e; min-height: 12px; }
        .sig-title { font-size: 6.5px; color: #666; text-transform: uppercase; }
        .sig-line { border-top: 1px solid #333; margin: 8px 10px 2px; }
        .sig-date { font-size: 6.5px; color: #999; }
        
        .stamp { position: absolute; right: 25px; top: 50%; transform: translateY(-50%) rotate(-20deg); border: 4px solid; padding: 8px 18px; border-radius: 15px; font-weight: 900; font-size: 22px; letter-spacing: 2px; opacity: 0.25; pointer-events: none; z-index: 10; text-transform: uppercase; }
        .stamp-approved { border-color: #10b981; color: #10b981; }
        .stamp-rejected { border-color: #ef4444; color: #ef4444; }
        .stamp-paid { border-color: #3b82f6; color: #3b82f6; }
        
        .status-badge { display: inline-block; padding: 3px 12px; border-radius: 4px; font-weight: bold; font-size: 8px; text-transform: uppercase; letter-spacing: 1px; }
        .status-approved { background: #d1fae5; color: #065f46; border: 1px solid #10b981; }
        .status-rejected { background: #fee2e2; color: #991b1b; border: 1px solid #ef4444; }
        .status-paid { background: #dbeafe; color: #1e40af; border: 1px solid #3b82f6; }
        .status-submitted { background: #fef3c7; color: #92400e; border: 1px solid #f59e0b; }
        .status-draft { background: #f3f4f6; color: #374151; border: 1px solid #9ca3af; }
        
        .footer { text-align: center; font-size: 6.5px; color: #999; margin-top: 6px; padding-top: 3px; border-top: 1px solid #ddd; }
        .print-btn { position: fixed; top: 10px; right: 10px; background: #1a237e; color: white; border: none; padding: 8px 16px; border-radius: 5px; cursor: pointer; font-size: 12px; font-weight: 600; z-index: 1000; }
        @media print { .print-btn { display: none !important; } }
    </style>
</head>
<body>
    <button class="print-btn" onclick="window.print()">🖨️ Print (A4)</button>

    @php
        $logoUrl = \App\Models\CompanySetting::getLogoUrl();
        $companyName = \App\Models\CompanySetting::get('company_name', 'TNT Construction and Trading');
        $companyTagline = \App\Models\CompanySetting::get('company_tagline', 'General Contractor & Engineering Services');
        
        // GET DYNAMIC TAX RATES FROM SETTINGS
        $vatRate = \App\Models\TaxSetting::vatRate();
        $retentionRate = \App\Models\TaxSetting::retentionRate();
        $withholdingTaxRate = \App\Models\TaxSetting::withholdingTaxRate();
        
        // Get contract details
        $subcontractor = $ipc->subcontractor;
        $project = $ipc->project;
        $contractData = null;
        $totalSum = 0;
        if ($subcontractor && $project) {
            $pivot = $project->subcontractors()->where('subcontractor_id', $subcontractor->id)->first();
            if ($pivot) {
                $contractData = $pivot->pivot;
                $totalSum = $contractData->contract_amount ?? 0;
            }
        }
        
        // Calculate amounts with dynamic tax rates
        $total = 0;
        foreach($ipc->ipcItems as $item) { $total += $item->current_amount ?? ($item->current_quantity * ($item->boqItem->unit_rate ?? 0)); }
        
        $vatAmount = $total * ($vatRate / 100);
        $gross = $total + $vatAmount;
        $retentionAmount = $total * ($retentionRate / 100);
        $withholdingAmount = $total * ($withholdingTaxRate / 100);
        $prev = $ipc->total_previous_amount ?? 0;
        $ded = $prev + $retentionAmount + $withholdingAmount;
        $due = $gross - $ded;
        
        $statusClass = match($ipc->status) {
            'approved' => 'status-approved', 'rejected' => 'status-rejected',
            'paid' => 'status-paid', 'submitted' => 'status-submitted',
            default => 'status-draft',
        };
    @endphp

    <div class="certificate">
        
        @if($ipc->status == 'approved')<div class="stamp stamp-approved">APPROVED</div>@endif
        @if($ipc->status == 'rejected')<div class="stamp stamp-rejected">REJECTED</div>@endif
        @if($ipc->status == 'paid')<div class="stamp stamp-paid">PAID</div>@endif
        
        <table class="header-table">
            <tr>
                <td class="logo-cell">
                    @if($logoUrl)<img src="{{ $logoUrl }}" class="logo-img" alt="Logo">@else<span class="logo-placeholder">🏗️</span>@endif
                </td>
                <td width="50%" style="text-align:center;">
                    <div class="company-name">{{ $companyName }}</div>
                    <div class="company-sub">{{ $companyTagline }}</div>
                    <div style="margin-top:3px;">
                        <span class="status-badge {{ $statusClass }}">{{ strtoupper($ipc->status) }}</span>
                    </div>
                </td>
                <td width="35%">
                    <div class="doc-box">
                        <strong>Document No:</strong> {{ $ipc->ipc_number }}<br>
                        <strong>Issue:</strong> {{ $ipc->issue_number ?? '1' }} | <strong>Page:</strong> 1/1
                    </div>
                </td>
            </tr>
        </table>

        <div class="cert-title">SUBCONTRACT PAYMENT CERTIFICATE</div>
        <div class="cert-subtitle">CERTIFICATE OF PAYMENT No. {{ str_pad($ipc->issue_number ?? '01', 2, '0', STR_PAD_LEFT) }}</div>

        <table class="info-table">
            <tr>
                <td width="55%" style="vertical-align:top;">
                    <table width="100%">
                        <tr><td class="info-label">Project:</td><td>{{ $ipc->project->name ?? 'N/A' }}</td></tr>
                        <tr><td class="info-label">Client:</td><td>{{ $ipc->project->client_name ?? '_________________' }}</td></tr>
                        <tr><td class="info-label">Location:</td><td>Addis Ababa, Ethiopia</td></tr>
                        <tr><td class="info-label">Contractor:</td><td>{{ $companyName }}</td></tr>
                        <tr><td class="info-label">Sub Contractor:</td><td><strong>{{ $ipc->subcontractor->name ?? 'N/A' }}</strong></td></tr>
                    </table>
                </td>
                <td width="45%" style="vertical-align:top;">
                    <table class="contract-table">
                        <tr><td class="ct-header" colspan="2">Contract Details</td></tr>
                        <tr>
                            <td width="60%">Main Contract</td>
                            <td width="40%" class="ct-value">
                                {{ $totalSum > 0 ? number_format($totalSum, 2) : '_______________' }}
                            </td>
                        </tr>
                        <tr><td>Supplementary Contract</td><td class="ct-value">_______________</td></tr>
                        <tr><td>Variation Order</td><td class="ct-value">_______________</td></tr>
                        <tr><td><strong>Total Sum</strong></td><td class="ct-value"><strong>{{ $totalSum > 0 ? number_format($totalSum, 2) : '_______________' }}</strong></td></tr>
                    </table>
                </td>
            </tr>
        </table>

        <div class="work-desc">
            <strong>Scope of Work Executed:</strong><br>
            @foreach($ipc->ipcItems as $item)• {{ $item->boqItem->description ?? 'N/A' }}<br>@endforeach
            <small><em>(Value of work executed and materials supplied to date)</em></small>
        </div>

        {{-- PAYMENT SUMMARY WITH DYNAMIC TAXES --}}
        <div class="amount-section">
            <div class="amount-header">PAYMENT SUMMARY (VAT: {{ $vatRate }}%, Retention: {{ $retentionRate }}%, WHT: {{ $withholdingTaxRate }}%)</div>
            <table class="amount-table">
                <tr><td width="5%" class="text-center"><strong>No.</strong></td><td width="12%" class="text-center"><strong>Date</strong></td><td width="43%"><strong>Description</strong></td><td width="25%" class="text-right"><strong>Amount (Birr)</strong></td><td width="15%" class="text-right"><strong>Remarks</strong></td></tr>
                <tr><td class="text-center">1</td><td class="text-center">{{ optional($ipc->ipc_date)->format('d-m-Y') ?? date('d-m-Y') }}</td><td>Work Executed</td><td class="text-right"><strong>{{ number_format($total, 2) }}</strong></td><td class="text-right">Executed</td></tr>
                <tr class="total-row"><td colspan="3" class="text-right"><strong>Net Amount:</strong></td><td class="text-right"><strong>{{ number_format($total, 2) }}</strong></td><td></td></tr>
                <tr><td colspan="3" class="text-right">VAT ({{ $vatRate }}%):</td><td class="text-right">{{ number_format($vatAmount, 2) }}</td><td></td></tr>
                <tr class="total-row"><td colspan="3" class="text-right"><strong>Gross Total:</strong></td><td class="text-right"><strong>{{ number_format($gross, 2) }}</strong></td><td></td></tr>
            </table>
        </div>

        {{-- DEDUCTIONS WITH DYNAMIC RATES --}}
        <table class="deduction-table">
            <tr><th width="5%">No.</th><th width="50%">Deduction</th><th width="25%" class="text-right">Amount (Birr)</th><th width="20%" class="text-right">Remarks</th></tr>
            <tr><td class="text-center">1</td><td>Previous Payments</td><td class="text-right">{{ number_format($prev, 2) }}</td><td></td></tr>
            <tr><td class="text-center">2</td><td>Rebate</td><td class="text-right">-</td><td></td></tr>
            <tr><td class="text-center">3</td><td>Retention ({{ $retentionRate }}%)</td><td class="text-right">{{ number_format($retentionAmount, 2) }}</td><td></td></tr>
            <tr><td class="text-center">4</td><td>Withholding Tax ({{ $withholdingTaxRate }}%)</td><td class="text-right">{{ number_format($withholdingAmount, 2) }}</td><td></td></tr>
            <tr><td class="text-center">5</td><td>Penalty</td><td class="text-right">-</td><td></td></tr>
            <tr><td class="text-center">6</td><td>Advance Repayment</td><td class="text-right">-</td><td></td></tr>
            <tr><td class="text-center">7</td><td>Other</td><td class="text-right">-</td><td></td></tr>
            <tr class="ded-total"><td colspan="2" class="text-right"><strong>Total Deductions:</strong></td><td class="text-right"><strong>{{ number_format($ded, 2) }}</strong></td><td></td></tr>
        </table>

        <table class="amount-table" style="margin-top:3px;">
            <tr class="net-due"><td width="55%" class="text-right"><strong>NET SUM DUE (Incl. {{ $vatRate }}% VAT):</strong></td><td width="30%" class="text-right"><strong>{{ number_format($due, 2) }} ETB</strong></td><td width="15%"></td></tr>
        </table>

        <div class="amount-words">
            We certify that the Subcontractor is entitled to:<br>
            <span class="birr">{{ \App\Helpers\NumberToWordsHelper::convert($due) }}</span>
        </div>

        <table class="signatures">
            <tr>
                <td><div class="sig-box"><div class="sig-title">Prepared By</div>@if($ipc->prepared_by)<div class="sig-name">{{ $ipc->prepared_by }}</div>@endif<div class="sig-line"></div>@if($ipc->prepared_at)<div class="sig-date">{{ \Carbon\Carbon::parse($ipc->prepared_at)->format('d-m-Y') }}</div>@endif</div></td>
                <td><div class="sig-box"><div class="sig-title">Checked By</div>@if($ipc->checked_by)<div class="sig-name">{{ $ipc->checked_by }}</div>@endif<div class="sig-line"></div>@if($ipc->checked_at)<div class="sig-date">{{ \Carbon\Carbon::parse($ipc->checked_at)->format('d-m-Y') }}</div>@endif</div></td>
                <td><div class="sig-box"><div class="sig-title">Approved By</div>@if($ipc->approved_by)<div class="sig-name">{{ $ipc->approved_by }}</div>@endif<div class="sig-line"></div>@if($ipc->approved_at)<div class="sig-date">{{ \Carbon\Carbon::parse($ipc->approved_at)->format('d-m-Y') }}</div>@endif</div></td>
            </tr>
        </table>

        <div class="footer">Striving for building the future! | {{ $companyName }} CMS | {{ date('d-m-Y H:i') }}</div>
    </div>
</body>
</html>
