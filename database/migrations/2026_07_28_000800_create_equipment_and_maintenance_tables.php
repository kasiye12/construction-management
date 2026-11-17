<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Equipment Fleet Master Table
        Schema::create('equipment', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->nullable()->unique();
            $table->string('name')->nullable();
            $table->string('equipment_type', 100); // EXCAVATOR, LOADER, CARMIX, DUMP TRUCK, PICK UP, GENERATOR, etc.
            $table->string('category', 100); // EARTH MOVING MACHINE, DUMP TRUCK, LIGHT VEHICLES, AUXILIARY & PLANT
            $table->string('make', 100)->nullable(); // CATERPILLAR, KOMATSU, SINOTRUK, TOYOTA, etc.
            $table->string('model', 100)->nullable();
            $table->string('plate_serial_no', 100)->nullable()->index(); // EX-0889, 3-50327, 3-A44801, 36KVA
            $table->string('serial_no', 100)->nullable(); // Chassis / VIN / Manufacturer SN
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->string('location_name', 150)->nullable(); // Central Workshop, Bishoftu, Ambo Referral, Arba Minch, etc.
            $table->enum('reading_unit', ['hr', 'km'])->default('hr'); // hr for heavy machinery, km for light vehicles
            $table->decimal('current_reading', 14, 2)->default(0);
            $table->date('current_reading_date')->nullable();
            
            // Last Service
            $table->decimal('last_service_reading', 14, 2)->nullable();
            $table->date('last_service_date')->nullable();
            $table->string('last_service_type', 50)->nullable(); // A, B, C, D, A2, B2, C2, D2, Overhaul
            
            // Next Service
            $table->decimal('next_service_reading', 14, 2)->nullable();
            $table->string('next_service_type', 50)->nullable();
            
            // Status & Condition
            $table->enum('status', ['active', 'due_service', 'under_maintenance', 'breakdown', 'idle'])->default('active');
            $table->text('remarks')->nullable();
            
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Preventive Maintenance Schedules (OF/EAMD/074)
        Schema::create('pm_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('schedule_code', 50)->unique(); // PMS-2026-001
            $table->string('title')->default('Preventive Maintenance Schedule');
            $table->string('document_code', 50)->default('OF/EAMD/074');
            $table->string('issue_no', 20)->default('1');
            $table->string('page_no', 30)->default('Page 1 of 1');
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->string('project_location', 150)->nullable();
            $table->date('schedule_date')->nullable();
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();
            $table->enum('status', ['draft', 'submitted', 'checked', 'approved', 'rejected'])->default('draft');
            
            // Workflow Sign-offs
            $table->foreignId('prepared_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('prepared_name', 150)->nullable();
            $table->timestamp('prepared_at')->nullable();
            
            $table->foreignId('checked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('checked_name', 150)->nullable();
            $table->timestamp('checked_at')->nullable();
            
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('approved_name', 150)->nullable();
            $table->timestamp('approved_at')->nullable();
            
            $table->text('rejection_reason')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        // 3. PM Schedule Items (Rows inside OF/EAMD/074)
        Schema::create('pm_schedule_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pm_schedule_id')->constrained('pm_schedules')->cascadeOnDelete();
            $table->foreignId('equipment_id')->nullable()->constrained('equipment')->nullOnDelete();
            $table->string('category', 100); // EARTH MOVING MACHINE, DUMP TRUCK, LIGHT VEHICLES, AUXILIARY
            $table->string('equipment_type', 100);
            $table->string('make', 100)->nullable();
            $table->string('model', 100)->nullable();
            $table->string('plate_serial_no', 100)->nullable();
            $table->string('serial_no', 100)->nullable();
            $table->string('location', 150)->nullable();
            
            // Last Service
            $table->decimal('last_service_reading', 14, 2)->nullable();
            $table->date('last_service_date')->nullable();
            $table->string('last_service_type', 50)->nullable();
            
            // Next Service
            $table->decimal('next_service_reading', 14, 2)->nullable();
            $table->string('next_service_type', 50)->nullable();
            
            // Current Reading
            $table->decimal('current_reading', 14, 2)->nullable();
            $table->date('current_reading_date')->nullable();
            
            // Calculation
            $table->decimal('remain_reading', 14, 2)->nullable(); // Next - Current
            $table->string('service_status', 50)->default('Normal'); // Normal, Due, Overdue, Completed
            $table->text('remark')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // 4. Equipment Breakdown & Maintenance Reports (OF/TNT/ EAMD/030)
        Schema::create('equipment_breakdown_reports', function (Blueprint $table) {
            $table->id();
            $table->string('report_code', 50)->unique(); // MR-2026-001
            $table->string('title')->default('Maintenance Report');
            $table->string('document_code', 50)->default('OF/TNT/ EAMD/030');
            $table->string('issue_no', 20)->default('1');
            $table->string('page_no', 30)->default('Page 1 of 1');
            $table->date('report_date');
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->string('location_name', 150)->nullable();
            $table->enum('status', ['draft', 'submitted', 'checked', 'approved', 'rejected'])->default('draft');
            
            // Sign-offs
            $table->foreignId('prepared_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('prepared_name', 150)->nullable();
            $table->timestamp('prepared_at')->nullable();
            
            $table->foreignId('checked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('checked_name', 150)->nullable();
            $table->timestamp('checked_at')->nullable();
            
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('approved_name', 150)->nullable();
            $table->timestamp('approved_at')->nullable();
            
            $table->text('rejection_reason')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        // 5. Equipment Breakdown Items (Rows inside OF/TNT/ EAMD/030)
        Schema::create('equipment_breakdown_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('breakdown_report_id')->constrained('equipment_breakdown_reports')->cascadeOnDelete();
            $table->foreignId('equipment_id')->nullable()->constrained('equipment')->nullOnDelete();
            $table->string('category', 100); // EARTH MOVING MACHINE AND PLANT, DUMP TRUCK, LIGHT VEHICLES, AUXILIARY, INSURANCE CASE
            $table->string('equipment_type', 100);
            $table->integer('quantity')->default(1);
            $table->string('plate_no_sn', 100)->nullable();
            $table->date('down_date')->nullable();
            $table->string('reason_of_down')->nullable(); // Engine Overhaul, Overheating, Hydraulic Pump, etc.
            $table->string('action_taken')->nullable(); // Down, Under Maintenance, Proforma, Purchase, etc.
            $table->string('location', 150)->nullable();
            $table->string('repair_status', 50)->default('down'); // down, under_maintenance, proforma, purchase, repaired
            $table->date('finished_date')->nullable();
            $table->text('remarks')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // 6. Daily Equipment Maintenance & Spare Parts Cost Logs
        Schema::create('equipment_maintenance_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipment_id')->nullable()->constrained('equipment')->nullOnDelete();
            $table->string('equipment_type', 100);
            $table->string('equipment_plate', 100)->nullable();
            $table->string('spare_part_name', 255);
            $table->string('po_no', 100)->nullable(); // EQ-MPR000035, POEQ/16/00006, etc.
            $table->string('unit', 30)->default('PCS'); // PCS, LIT, SET, HR, etc.
            $table->decimal('quantity', 12, 2)->default(1);
            $table->decimal('unit_cost', 14, 2)->default(0);
            $table->decimal('total_cost_with_vat', 14, 2)->default(0);
            $table->string('supplier', 150)->nullable();
            $table->date('log_date');
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->string('location_name', 150)->nullable();
            $table->foreignId('logged_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment_maintenance_logs');
        Schema::dropIfExists('equipment_breakdown_items');
        Schema::dropIfExists('equipment_breakdown_reports');
        Schema::dropIfExists('pm_schedule_items');
        Schema::dropIfExists('pm_schedules');
        Schema::dropIfExists('equipment');
    }
};
