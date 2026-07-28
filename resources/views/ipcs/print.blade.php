<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Payment Certificate · {{ $ipc->ipc_number }}</title>
  <style>
    * { box-sizing: border-box; font-family: Arial, Helvetica, sans-serif; }
    body {
      background: #e6e9f0;
      display: flex;
      justify-content: center;
      align-items: flex-start;
      min-height: 100vh;
      padding: 20px;
      margin: 0;
    }
    .page {
      width: 1000px;
      margin: 0 auto;
      background: #fff;
      padding: 10px 15px 10px 15px;
      border: 1px solid #ccc;
      box-shadow: 0 8px 20px rgba(0,0,0,0.15);
    }
    @media print {
      body { background: white; padding: 0; margin: 0; }
      .page { box-shadow: none; border: none; padding: 5px 10px; width: 100%; }
      .no-print { display: none !important; }
    }

    .header-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; border: 2px solid #000; }
    .header-table td { padding: 8px 10px; font-size: 11px; vertical-align: middle; }

    .cert-number { text-align: center; font-size: 14px; font-weight: bold; margin: 10px 0 14px 0; letter-spacing: 0.5px; }

    .top-section { display: flex; justify-content: space-between; gap: 15px; margin-bottom: 10px; }
    .project-info { flex: 1; font-size: 11px; line-height: 1.8; }
    .project-info div { margin-bottom: 2px; }

    table.data-table { border-collapse: collapse; width: 100%; font-size: 10px; }
    table.data-table th, table.data-table td { border: 1px solid #000; padding: 3px 6px; }
    .text-right { text-align: right; }
    .text-center { text-align: center; }
    .bold { font-weight: bold; }
    .contract-table { width: 280px; }

    .main-grid { display: flex; gap: 15px; align-items: flex-start; }
    .left-col { width: 44%; }
    .right-col { width: 56%; }

    .statement-note { font-size: 10px; font-weight: bold; margin: 6px 0; font-style: italic; }

    .amount-words { font-weight: bold; font-size: 11px; margin-top: 8px; padding: 6px; line-height: 1.5; }

    .print-wrapper { text-align: right; margin-bottom: 5px; }
    .print-btn {
      background: #1a237e; color: white; border: none; padding: 6px 14px;
      border-radius: 30px; font-weight: 600; font-size: 10px; cursor: pointer;
      box-shadow: 0 2px 8px rgba(0,0,0,0.2); display: inline-block;
    }
    .print-btn:hover { background: #0f1a5e; }
  </style>
</head>
<body>
<div class="page">
  <div class="print-wrapper no-print">
    <button class="print-btn" onclick="window.print()">🖨️ Print Certificate</button>
  </div>

  @php
    $logoUrl = \App\Models\CompanySetting::getLogoUrl();
    $companyName = \App\Models\CompanySetting::get('company_name', 'TNT CONSTRUCTION AND TRADING');
    $companyAmh = \App\Models\CompanySetting::get('company_name_amh', 'ቲኤንቲ ኮንስትራክሽንና ንግድ ሥራዎች');
    $project = $ipc->project;
    $subcontractor = $ipc->subcontractor;

    $contractAmount = $ipc->ipcItems->sum('contract_amount');
    $previousAmount = $ipc->ipcItems->sum('previous_amount');
    $currentAmount = $ipc->total_current_amount ?? $ipc->ipcItems->sum('current_amount');
    $vatAmount = $currentAmount * 0.15;
    $executedWithVat = $currentAmount + $vatAmount;
    $retentionPct = $ipc->retention_percentage ?? 5;
    $retentionAmount = $currentAmount * ($retentionPct / 100);
    $advanceRepayment = $ipc->advance_repayment ?? 0;
    $totalDeductions = $previousAmount + $retentionAmount + $advanceRepayment;
    $netPayment = $executedWithVat - $totalDeductions;

    $prevPayments = \App\Models\Ipc::where('project_id', $ipc->project_id)
        ->where('id', '<', $ipc->id)
        ->whereIn('status', ['approved', 'paid'])
        ->orderBy('ipc_date')
        ->get();
  @endphp

  <!-- ====== HEADER TABLE ====== -->
  <table class="header-table">
    <tr>
      <!-- LEFT: Logo -->
      <td style="width: 15%; text-align: center; vertical-align: middle; border-right: 1px solid #000;">
        @if($logoUrl)
          <img src="{{ $logoUrl }}" style="width:60px;height:60px;object-fit:contain;" alt="Logo">
        @else
          <div style="width:60px;height:60px;background:linear-gradient(135deg,#1a237e,#3949ab);color:white;display:inline-flex;align-items:center;justify-content:center;font-size:24px;border-radius:8px;">🏗️</div>
        @endif
      </td>
      <!-- CENTER: Company Name + Certificate Title -->
      <td style="width: 50%; text-align: center; vertical-align: middle;">
        <div style="font-size: 13px; font-weight: bold; color: #1a237e; margin-bottom: 2px;">{{ $companyAmh }}</div>
        <div style="font-size: 15px; font-weight: bold; color: #1a237e; margin-bottom: 8px;">{{ $companyName }}</div>
        <div style="font-size: 17px; font-weight: bold; letter-spacing: 3px; color: #000;">SUBCONTRACT PAYMENT CERTEFICATE</div>
      </td>
      <!-- RIGHT: Document Info -->
      <td style="width: 35%; padding: 0; vertical-align: top;">
        <table style="width: 100%; border-collapse: collapse; height: 100%;">
          <tr>
            <td style="border: none; font-size: 10px; padding: 6px 8px; text-align: right;">
              Document №: <strong>01</strong>
            </td>
          </tr>
          <tr>
            <td style="border: none; border-bottom: 1px solid #000; font-size: 10px; padding: 6px 8px; text-align: right;">
              <strong>{{ $ipc->ipc_number }}</strong>
            </td>
          </tr>
          <tr>
            <td style="border: none; padding: 0;">
              <table style="width: 100%; border-collapse: collapse;">
                <tr>
                  <td style="border: none; font-size: 9px; padding: 6px 8px; text-align: center; border-right: 1px solid #000;">
                    Issue №:<br><strong>1</strong>
                  </td>
                  <td style="border: none; font-size: 9px; padding: 6px 8px; text-align: center;">
                    Page №:<br><strong>Page 1 of 1</strong>
                  </td>
                </tr>
              </table>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>

  <!-- ====== CERTIFICATE TITLE ====== -->
  <div class="cert-number">CERTIFICATE OF PAYMENT No. {{ str_replace(['IPC-', 'IPC '], '', $ipc->ipc_number) }}</div>

  <!-- ====== PROJECT INFO + CONTRACT AMOUNT ====== -->
  <div class="top-section">
    <div class="project-info">
      <div><strong>Project :-</strong> <u>{{ $project->name ?? 'N/A' }}</u></div>
      <div><strong>Location :-</strong> <u>Addis Ababa</u></div>
      <div><strong>Client :-</strong> <u>{{ $project->client_name ?? '________________________________' }}</u></div>
      <div><strong>Consultant :-</strong> <u>Ethiopian Engineering Corporation</u></div>
      <div><strong>Contractor :-</strong> <u>{{ $companyName }}</u></div>
      <div><strong>Sub Contractor :-</strong> <u>{{ $subcontractor->name ?? 'N/A' }}</u></div>
    </div>

    <div>
      <table class="data-table contract-table">
        <thead>
          <tr><th colspan="2" class="text-right">Amount (Birr)</th></tr>
        </thead>
        <tbody>
          <tr><td class="bold text-right">Main Contract</td><td class="text-right" style="width:110px;">{{ number_format($contractAmount, 2) }}</td></tr>
          <tr><td class="bold text-right">Supplementary Contract</td><td></td></tr>
          <tr><td class="bold text-right">Variation Order</td><td></td></tr>
          <tr><td class="bold text-right">Total Sum</td><td class="text-right bold">{{ number_format($contractAmount, 2) }}</td></tr>
        </tbody>
      </table>
    </div>
  </div>

  <div class="statement-note">
    (As per the attached statement the value of work executed and or materials supplied to date is)
  </div>

  <!-- ====== MAIN CONTENT GRID ====== -->
  <div class="main-grid">
    
    <!-- LEFT: Previous Payments -->
    <div class="left-col">
      <table class="data-table">
        <thead>
          <tr>
            <th style="width:28px;">No.</th>
            <th style="width:55px;">Date</th>
            <th colspan="2">Previous Payment</th>
          </tr>
          <tr>
            <th></th><th></th>
            <th>Without Vat (Birr)</th>
            <th>With VAT (15%)</th>
          </tr>
        </thead>
        <tbody>
          @foreach($prevPayments as $k => $prev)
          <tr>
            <td class="text-center">{{ $k + 1 }}</td>
            <td class="text-center">{{ optional($prev->ipc_date)->format('d/m/Y') ?? '-' }}</td>
            <td class="text-right">{{ number_format($prev->total_current_amount ?? 0, 2) }}</td>
            <td class="text-right">{{ number_format(($prev->total_current_amount ?? 0) * 1.15, 2) }}</td>
          </tr>
          @endforeach
          @for($i = $prevPayments->count(); $i < 6; $i++)
          <tr><td class="text-center">{{ $i + 1 }}</td><td></td><td></td><td></td></tr>
          @endfor
          <tr style="height: 50px;"><td></td><td></td><td></td><td></td></tr>
          <tr>
            <td colspan="2" class="bold text-center">Total</td>
            <td class="text-right bold">{{ number_format($prevPayments->sum('total_current_amount'), 2) }}</td>
            <td class="text-right bold">{{ number_format($prevPayments->sum('total_current_amount') * 1.15, 2) }}</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- RIGHT: Executed Amount + Deductions -->
    <div class="right-col">
      <table class="data-table">
        <thead>
          <tr>
            <th colspan="2"></th>
            <th class="text-right" style="width:120px;">Amount (Birr)</th>
            <th style="width:120px;"></th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td colspan="2" class="bold text-right">Executed Amount</td>
            <td class="text-right">{{ number_format($currentAmount, 2) }}</td>
            <td></td>
          </tr>
          <tr>
            <td colspan="2" class="bold text-right">VAT 15%</td>
            <td class="text-right">{{ number_format($vatAmount, 2) }}</td>
            <td></td>
          </tr>
          <tr>
            <td colspan="2" class="bold text-right">Total</td>
            <td class="text-right bold">{{ number_format($executedWithVat, 2) }}</td>
            <td></td>
          </tr>
          
          <tr><td colspan="4" style="height:5px;border:none;"></td></tr>
          
          <tr>
            <td rowspan="8" style="width:70px; vertical-align:middle;" class="bold text-center">Deduction</td>
            <td></td>
            <td class="bold text-center">Amount (Birr)</td>
            <td></td>
          </tr>
          <tr><td><strong>1)</strong> Previous Payments</td><td class="text-right">{{ number_format($previousAmount, 2) }}</td><td></td></tr>
          <tr><td><strong>2)</strong> Rebate</td><td></td><td></td></tr>
          <tr><td><strong>3)</strong> Retention {{ $retentionPct }}%</td><td class="text-right">{{ number_format($retentionAmount, 2) }}</td><td></td></tr>
          <tr><td><strong>4)</strong> Penalty</td><td></td><td></td></tr>
          <tr><td><strong>5)</strong> Advance Repayment</td><td class="text-right">{{ number_format($advanceRepayment, 2) }}</td><td></td></tr>
          <tr><td><strong>6)</strong> Other</td><td></td><td></td></tr>
          <tr>
            <td class="bold text-right">Total Deductions</td>
            <td class="text-right bold">{{ number_format($totalDeductions, 2) }}</td>
            <td class="text-right bold">{{ number_format($totalDeductions, 2) }}</td>
          </tr>
          <tr><td colspan="4" style="height:3px;border:none;"></td></tr>
          <tr>
            <td colspan="2" class="bold text-right">Net Sum Due to the Contractor including 15% VAT</td>
            <td></td>
            <td class="text-right bold" style="font-size:12px;">{{ number_format($netPayment, 2) }}</td>
          </tr>
        </tbody>
      </table>

      <div class="amount-words">
        Amount in Words: {{ number_format($netPayment, 2) }} Birr Only
      </div>
    </div>

  </div>

  <!-- ====== SIGNATURES ====== -->
  <table style="width:100%;font-size:8px;margin-top:14px;border-collapse:collapse;">
    <tr>
      <td width="33%" class="text-center" style="padding:6px;">
        <strong>Prepared By</strong><br><br>
        {{ $ipc->prepared_by ?? '_______________________' }}<br><br>
        <div style="border-top:1px solid #000;padding-top:2px;margin:0 10px;">_______________</div>
        Date : {{ optional($ipc->ipc_date)->format('d/m/Y') ?? '_______________' }}
      </td>
      <td width="33%" class="text-center" style="padding:6px;">
        <strong>Approved By</strong><br><br>
        {{ $ipc->approved_by ?? '_____________' }}<br><br>
        <div style="border-top:1px solid #000;padding-top:2px;margin:0 10px;">_____________</div>
        Date : {{ optional($ipc->ipc_date)->format('d/m/Y') ?? '_______________' }}
      </td>
      <td width="34%" class="text-center" style="padding:6px;">
        <strong>Certified By</strong><br><br>
        {{ $ipc->checked_by ?? '________________' }}<br><br>
        <div style="border-top:1px solid #000;padding-top:2px;margin:0 10px;">________________</div>
        Date : {{ optional($ipc->ipc_date)->format('d/m/Y') ?? '_______________' }}
      </td>
    </tr>
  </table>

  <!-- ====== FOOTER ====== -->
  <div style="text-align:center;font-size:8px;font-weight:600;color:#1a237e;margin-top:10px;font-style:italic;">
    Striving for building the future !
  </div>

</div>
</body>
</html>
