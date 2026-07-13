#!/bin/bash

echo "🏗️ Building Complete Construction & Engineering Management System"
echo "================================================================"

# ==========================================
# 1. ENHANCED MIGRATIONS
# ==========================================

# Additional migrations for full system
cat > database/migrations/2024_01_01_000020_create_subcontractor_contracts_table.php << 'MIGRATION'
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('subcontractor_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('subcontractor_id')->constrained()->onDelete('cascade');
            $table->string('contract_number')->unique();
            $table->date('contract_date');
            $table->decimal('contract_amount', 15, 2);
            $table->decimal('advance_payment', 15, 2)->default(0);
            $table->decimal('retention_percentage', 5, 2)->default(5);
            $table->decimal('vat_percentage', 5, 2)->default(15);
            $table->text('scope_of_work')->nullable();
            $table->date('start_date')->nullable();
            $table->date('completion_date')->nullable();
            $table->enum('status', ['draft', 'active', 'completed', 'terminated'])->default('draft');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('subcontractor_contracts');
    }
};
MIGRATION

# Take-off sheets table
cat > database/migrations/2024_01_01_000021_create_takeoff_sheets_table.php << 'MIGRATION'
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('takeoff_sheets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('boq_item_id')->constrained()->onDelete('cascade');
            $table->string('sheet_number');
            $table->string('description');
            $table->date('measurement_date');
            $table->string('measured_by')->nullable();
            $table->string('checked_by')->nullable();
            $table->decimal('total_quantity', 15, 4)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->enum('status', ['draft', 'checked', 'approved'])->default('draft');
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('takeoff_sheets');
    }
};
MIGRATION

# Take-off details (for rebar, concrete, etc.)
cat > database/migrations/2024_01_01_000022_create_takeoff_details_table.php << 'MIGRATION'
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('takeoff_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('takeoff_sheet_id')->constrained()->onDelete('cascade');
            $table->string('item_type')->default('general'); // general, rebar, concrete, membrane
            $table->string('bar_type')->nullable(); // Ø8, Ø10, Ø12 etc for rebar
            $table->decimal('diameter', 10, 2)->nullable();
            $table->integer('number_of_bars')->nullable();
            $table->decimal('length', 10, 2)->nullable();
            $table->decimal('width', 10, 2)->nullable();
            $table->decimal('depth', 10, 2)->nullable();
            $table->decimal('unit_weight', 10, 4)->nullable(); // kg/m for rebar
            $table->decimal('quantity', 15, 4)->default(0);
            $table->decimal('rate', 15, 2)->default(0);
            $table->decimal('amount', 15, 2)->default(0);
            $table->string('location')->nullable(); // Grid reference
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('takeoff_details');
    }
};
MIGRATION

# Payment certificates (enhanced)
cat > database/migrations/2024_01_01_000023_create_payment_certificates_table.php << 'MIGRATION'
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('payment_certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('subcontractor_contract_id')->constrained()->onDelete('cascade');
            $table->string('certificate_number');
            $table->integer('payment_number');
            $table->date('certificate_date');
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();
            
            // Financial breakdown
            $table->decimal('net_amount', 15, 2)->default(0);
            $table->decimal('vat_percentage', 5, 2)->default(15);
            $table->decimal('vat_amount', 15, 2)->default(0);
            $table->decimal('gross_amount', 15, 2)->default(0);
            
            // Deductions
            $table->decimal('previous_payment', 15, 2)->default(0);
            $table->decimal('retention_percentage', 5, 2)->default(5);
            $table->decimal('retention_amount', 15, 2)->default(0);
            $table->decimal('penalty_amount', 15, 2)->default(0);
            $table->decimal('advance_repayment', 15, 2)->default(0);
            $table->decimal('other_deductions', 15, 2)->default(0);
            $table->string('other_deductions_description')->nullable();
            $table->decimal('total_deductions', 15, 2)->default(0);
            
            // Final
            $table->decimal('net_sum_due', 15, 2)->default(0);
            $table->string('amount_in_words')->nullable();
            
            // Approval workflow
            $table->string('prepared_by')->nullable();
            $table->string('checked_by')->nullable();
            $table->string('approved_by')->nullable();
            $table->string('certified_by')->nullable();
            $table->timestamp('prepared_at')->nullable();
            $table->timestamp('checked_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('certified_at')->nullable();
            
            $table->enum('status', ['draft', 'prepared', 'checked', 'approved', 'certified', 'paid'])->default('draft');
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('payment_certificates');
    }
};
MIGRATION

# Certificate items (linking to take-off)
cat > database/migrations/2024_01_01_000024_create_certificate_items_table.php << 'MIGRATION'
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('certificate_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_certificate_id')->constrained()->onDelete('cascade');
            $table->foreignId('boq_item_id')->constrained()->onDelete('cascade');
            $table->string('description');
            $table->string('unit');
            $table->decimal('contract_quantity', 15, 4);
            $table->decimal('contract_rate', 15, 2);
            $table->decimal('previous_quantity', 15, 4)->default(0);
            $table->decimal('previous_amount', 15, 2)->default(0);
            $table->decimal('current_quantity', 15, 4)->default(0);
            $table->decimal('current_amount', 15, 2)->default(0);
            $table->decimal('to_date_quantity', 15, 4)->default(0);
            $table->decimal('to_date_amount', 15, 2)->default(0);
            $table->decimal('percentage_complete', 5, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('certificate_items');
    }
};
MIGRATION

# Actual costs tracking
cat > database/migrations/2024_01_01_000025_create_actual_costs_table.php << 'MIGRATION'
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('actual_costs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('boq_item_id')->nullable()->constrained()->onDelete('set null');
            $table->string('cost_type'); // labor, material, equipment, other
            $table->string('description');
            $table->decimal('amount', 15, 2);
            $table->date('cost_date');
            $table->string('vendor')->nullable();
            $table->string('invoice_number')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('actual_costs');
    }
};
MIGRATION

# Approval workflow tracking
cat > database/migrations/2024_01_01_000026_create_approvals_table.php << 'MIGRATION'
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('approvals', function (Blueprint $table) {
            $table->id();
            $table->morphs('approvable'); // Can be payment_certificate, takeoff_sheet, etc.
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('action'); // prepared, checked, approved, certified, rejected
            $table->text('comments')->nullable();
            $table->timestamp('action_date');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('approvals');
    }
};
MIGRATION

echo "✅ All enhanced migrations created!"
