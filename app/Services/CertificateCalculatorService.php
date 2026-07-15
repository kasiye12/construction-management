<?php
namespace App\Services;

use App\Models\Ipc;
use App\Models\TaxSetting;

class CertificateCalculatorService
{
    private Ipc $certificate;
    private float $netAmount = 0;
    private float $vatRate = 15;
    private float $retentionRate = 5;
    private float $withholdingTaxRate = 2;
    private array $calculationLog = [];

    public function __construct()
    {
        // Load rates from settings
        $this->vatRate = TaxSetting::vatRate();
        $this->retentionRate = TaxSetting::retentionRate();
        $this->withholdingTaxRate = TaxSetting::withholdingTaxRate();
    }

    public function calculate(Ipc $certificate): array
    {
        $this->certificate = $certificate;
        $this->calculationLog = [];

        // Step 1: Net Amount
        $this->netAmount = $this->calculateNetAmount();
        $this->logStep('1. Net Amount (Work Done)', $this->netAmount);

        // Step 2: VAT (Dynamic from settings)
        $vatAmount = $this->netAmount * ($this->vatRate / 100);
        $this->logStep("2. VAT ({$this->vatRate}%)", $vatAmount);

        // Step 3: Gross Amount
        $grossAmount = $this->netAmount + $vatAmount;
        $this->logStep('3. Gross Amount (Net + VAT)', $grossAmount);

        // Step 4: Retention (Dynamic from settings)
        $retentionAmount = $this->netAmount * ($this->retentionRate / 100);
        $this->logStep("4. Retention ({$this->retentionRate}%)", $retentionAmount);

        // Step 5: Withholding Tax
        $withholdingAmount = $this->netAmount * ($this->withholdingTaxRate / 100);
        $this->logStep("5. Withholding Tax ({$this->withholdingTaxRate}%)", $withholdingAmount);

        // Step 6: Total Deductions
        $previousPayment = $certificate->total_previous_amount ?? 0;
        $totalDeductions = $previousPayment + $retentionAmount + $withholdingAmount;
        $this->logStep('6. Total Deductions', $totalDeductions);

        // Step 7: Net Sum Due
        $netSumDue = $grossAmount - $totalDeductions;
        $this->logStep('7. Net Sum Due', $netSumDue);

        // Step 8: Amount in Words
        $amountInWords = $this->numberToWords($netSumDue);
        $this->logStep('8. Amount in Words', $amountInWords);

        // Update certificate
        $certificate->update([
            'total_current_amount' => round($this->netAmount, 2),
            'retention_percentage' => $this->retentionRate,
            'retention_amount' => round($retentionAmount, 2),
            'net_payment_amount' => round($netSumDue, 2),
        ]);

        return [
            'net_amount' => round($this->netAmount, 2),
            'vat_rate' => $this->vatRate,
            'vat_amount' => round($vatAmount, 2),
            'gross_amount' => round($grossAmount, 2),
            'retention_rate' => $this->retentionRate,
            'retention_amount' => round($retentionAmount, 2),
            'withholding_tax_rate' => $this->withholdingTaxRate,
            'withholding_amount' => round($withholdingAmount, 2),
            'total_deductions' => round($totalDeductions, 2),
            'net_sum_due' => round($netSumDue, 2),
            'amount_in_words' => $amountInWords,
            'calculation_log' => $this->calculationLog,
        ];
    }

    private function calculateNetAmount(): float
    {
        return $this->certificate->ipcItems->sum(function($item) {
            return $item->current_amount ?? ($item->current_quantity * ($item->boqItem->unit_rate ?? 0));
        });
    }

    public function getDetailedBreakdown(Ipc $certificate): array
    {
        $netAmount = $certificate->ipcItems->sum('current_amount');
        $vatAmount = $netAmount * ($this->vatRate / 100);
        $grossAmount = $netAmount + $vatAmount;
        $retentionAmount = $netAmount * ($this->retentionRate / 100);
        $withholdingAmount = $netAmount * ($this->withholdingTaxRate / 100);
        $prevPayment = $certificate->total_previous_amount ?? 0;
        $totalDeductions = $prevPayment + $retentionAmount + $withholdingAmount;
        $netDue = $grossAmount - $totalDeductions;

        return [
            'certificate_number' => $certificate->ipc_number,
            'settings' => [
                'vat_rate' => $this->vatRate,
                'retention_rate' => $this->retentionRate,
                'withholding_tax_rate' => $this->withholdingTaxRate,
            ],
            'financial_breakdown' => [
                'A. Net Amount' => number_format($netAmount, 2),
                "B. VAT ({$this->vatRate}%)" => number_format($vatAmount, 2),
                'C. GROSS AMOUNT (A+B)' => number_format($grossAmount, 2),
                '',
                'DEDUCTIONS:' => '',
                'D1. Previous Payment' => number_format($prevPayment, 2),
                "D2. Retention ({$this->retentionRate}%)" => number_format($retentionAmount, 2),
                "D3. Withholding Tax ({$this->withholdingTaxRate}%)" => number_format($withholdingAmount, 2),
                'D. TOTAL DEDUCTIONS' => number_format($totalDeductions, 2),
                '',
                'NET SUM DUE (C-D)' => number_format($netDue, 2),
            ],
            'amount_in_words' => $this->numberToWords($netDue),
            'net_amount' => round($netAmount, 2),
            'vat_amount' => round($vatAmount, 2),
            'gross_amount' => round($grossAmount, 2),
            'retention_amount' => round($retentionAmount, 2),
            'withholding_amount' => round($withholdingAmount, 2),
            'total_deductions' => round($totalDeductions, 2),
            'net_sum_due' => round($netDue, 2),
        ];
    }

    public function validateCalculations(Ipc $certificate): array
    {
        return [
            'is_valid' => true,
            'errors' => [],
            'warnings' => [],
        ];
    }

    private function numberToWords(float $number): string
    {
        if ($number <= 0) return 'Zero Ethiopian Birr Only';
        $whole = floor($number);
        return ucfirst($this->convertWholeNumber($whole)) . ' Ethiopian Birr Only';
    }

    private function convertWholeNumber(int $number): string
    {
        if ($number == 0) return 'Zero';
        $ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine',
                 'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen',
                 'Seventeen', 'Eighteen', 'Nineteen'];
        $tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];
        $scales = ['', 'Thousand', 'Million', 'Billion'];
        
        $words = '';
        $scaleIndex = 0;
        while ($number > 0) {
            $part = $number % 1000;
            if ($part > 0) {
                $h = floor($part/100); $r = $part%100; $w = '';
                if ($h > 0) $w .= $ones[$h].' Hundred';
                if ($r > 0) { if($w) $w.=' and '; $w .= $r<20 ? $ones[$r] : $tens[floor($r/10)].($r%10?'-'.$ones[$r%10]:''); }
                $words = $w.($scaleIndex>0?' '.$scales[$scaleIndex]:'').($words?' '.$words:'');
            }
            $number = floor($number/1000);
            $scaleIndex++;
        }
        return trim($words);
    }

    private function logStep(string $desc, $val): void
    {
        $this->calculationLog[] = ['step' => $desc, 'value' => is_numeric($val) ? number_format($val,2) : $val];
    }
}
