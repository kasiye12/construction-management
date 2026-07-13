#!/bin/bash

# Subcontractor Contract Model
cat > app/Models/SubcontractorContract.php << 'PHP'
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubcontractorContract extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'project_id', 'subcontractor_id', 'contract_number', 'contract_date',
        'contract_amount', 'advance_payment', 'retention_percentage', 'vat_percentage',
        'scope_of_work', 'start_date', 'completion_date', 'status'
    ];

    protected $casts = [
        'contract_date' => 'date',
        'start_date' => 'date',
        'completion_date' => 'date',
        'contract_amount' => 'decimal:2',
        'advance_payment' => 'decimal:2',
        'retention_percentage' => 'decimal:2',
        'vat_percentage' => 'decimal:2'
    ];

    public function project() { return $this->belongsTo(Project::class); }
    public function subcontractor() { return $this->belongsTo(Subcontractor::class); }
    public function paymentCertificates() { return $this->hasMany(PaymentCertificate::class); }
}
PHP

# Takeoff Sheet Model
cat > app/Models/TakeoffSheet.php << 'PHP'
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TakeoffSheet extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'project_id', 'boq_item_id', 'sheet_number', 'description',
        'measurement_date', 'measured_by', 'checked_by', 'total_quantity',
        'total_amount', 'status', 'remarks'
    ];

    protected $casts = [
        'measurement_date' => 'date',
        'total_quantity' => 'decimal:4',
        'total_amount' => 'decimal:2'
    ];

    public function project() { return $this->belongsTo(Project::class); }
    public function boqItem() { return $this->belongsTo(BoqItem::class); }
    public function details() { return $this->hasMany(TakeoffDetail::class); }
    public function approvals() { return $this->morphMany(Approval::class, 'approvable'); }
}
PHP

# Takeoff Detail Model
cat > app/Models/TakeoffDetail.php << 'PHP'
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TakeoffDetail extends Model
{
    protected $fillable = [
        'takeoff_sheet_id', 'item_type', 'bar_type', 'diameter',
        'number_of_bars', 'length', 'width', 'depth', 'unit_weight',
        'quantity', 'rate', 'amount', 'location', 'remarks'
    ];

    protected $casts = [
        'diameter' => 'decimal:2',
        'length' => 'decimal:2',
        'width' => 'decimal:2',
        'depth' => 'decimal:2',
        'unit_weight' => 'decimal:4',
        'quantity' => 'decimal:4',
        'rate' => 'decimal:2',
        'amount' => 'decimal:2'
    ];

    public function takeoffSheet() { return $this->belongsTo(TakeoffSheet::class); }

    // Calculate rebar weight
    public static function calculateRebarWeight($diameter, $length, $numberOfBars)
    {
        // Weight = (d²/162) × length × number of bars
        $unitWeight = ($diameter * $diameter) / 162;
        return $unitWeight * $length * $numberOfBars;
    }
}
PHP

# Payment Certificate Model
cat > app/Models/PaymentCertificate.php << 'PHP'
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentCertificate extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'project_id', 'subcontractor_contract_id', 'certificate_number',
        'payment_number', 'certificate_date', 'period_start', 'period_end',
        'net_amount', 'vat_percentage', 'vat_amount', 'gross_amount',
        'previous_payment', 'retention_percentage', 'retention_amount',
        'penalty_amount', 'advance_repayment', 'other_deductions',
        'other_deductions_description', 'total_deductions', 'net_sum_due',
        'amount_in_words', 'prepared_by', 'checked_by', 'approved_by',
        'certified_by', 'prepared_at', 'checked_at', 'approved_at',
        'certified_at', 'status', 'remarks'
    ];

    protected $casts = [
        'certificate_date' => 'date',
        'period_start' => 'date',
        'period_end' => 'date',
        'prepared_at' => 'datetime',
        'checked_at' => 'datetime',
        'approved_at' => 'datetime',
        'certified_at' => 'datetime'
    ];

    public function project() { return $this->belongsTo(Project::class); }
    public function contract() { return $this->belongsTo(SubcontractorContract::class, 'subcontractor_contract_id'); }
    public function items() { return $this->hasMany(CertificateItem::class); }
    public function approvals() { return $this->morphMany(Approval::class, 'approvable'); }

    // Calculate all financial fields
    public function calculateFinances()
    {
        $this->net_amount = $this->items->sum('current_amount');
        $this->vat_amount = $this->net_amount * ($this->vat_percentage / 100);
        $this->gross_amount = $this->net_amount + $this->vat_amount;
        $this->retention_amount = $this->net_amount * ($this->retention_percentage / 100);
        
        $this->total_deductions = $this->previous_payment + $this->retention_amount + 
                                   $this->penalty_amount + $this->advance_repayment + 
                                   $this->other_deductions;
        
        $this->net_sum_due = $this->gross_amount - $this->total_deductions;
        $this->amount_in_words = $this->numberToWords($this->net_sum_due);
        
        return $this;
    }

    // Convert number to words (simplified)
    private function numberToWords($number)
    {
        // This is a simplified version. Use a proper library for production
        $formatter = new \NumberFormatter('en', \NumberFormatter::SPELLOUT);
        return ucfirst($formatter->format($number)) . ' Ethiopian Birr Only';
    }
}
PHP

# Certificate Item Model
cat > app/Models/CertificateItem.php << 'PHP'
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CertificateItem extends Model
{
    protected $fillable = [
        'payment_certificate_id', 'boq_item_id', 'description', 'unit',
        'contract_quantity', 'contract_rate', 'previous_quantity',
        'previous_amount', 'current_quantity', 'current_amount',
        'to_date_quantity', 'to_date_amount', 'percentage_complete'
    ];

    protected $casts = [
        'contract_quantity' => 'decimal:4',
        'contract_rate' => 'decimal:2',
        'previous_quantity' => 'decimal:4',
        'previous_amount' => 'decimal:2',
        'current_quantity' => 'decimal:4',
        'current_amount' => 'decimal:2',
        'to_date_quantity' => 'decimal:4',
        'to_date_amount' => 'decimal:2',
        'percentage_complete' => 'decimal:2'
    ];

    public function paymentCertificate() { return $this->belongsTo(PaymentCertificate::class); }
    public function boqItem() { return $this->belongsTo(BoqItem::class); }
}
PHP

# Actual Cost Model
cat > app/Models/ActualCost.php << 'PHP'
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActualCost extends Model
{
    protected $fillable = [
        'project_id', 'boq_item_id', 'cost_type', 'description',
        'amount', 'cost_date', 'vendor', 'invoice_number', 'remarks'
    ];

    protected $casts = [
        'cost_date' => 'date',
        'amount' => 'decimal:2'
    ];

    public function project() { return $this->belongsTo(Project::class); }
    public function boqItem() { return $this->belongsTo(BoqItem::class); }
}
PHP

# Approval Model
cat > app/Models/Approval.php << 'PHP'
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Approval extends Model
{
    protected $fillable = [
        'approvable_type', 'approvable_id', 'user_id', 'action',
        'comments', 'action_date'
    ];

    protected $casts = [
        'action_date' => 'datetime'
    ];

    public function approvable() { return $this->morphTo(); }
    public function user() { return $this->belongsTo(User::class); }
}
PHP

echo "✅ All enhanced models created!"
