<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Weekly Contractual Reports (Parent Form OF/TNT/CAD/01)
        Schema::create('contractual_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->string('report_number')->unique();
            $table->string('document_code')->default('OF/TNT/CAD/01');
            $table->string('issue_no')->default('1');
            $table->string('project_location')->nullable();
            $table->string('client_name')->nullable();
            $table->string('consultant_name')->nullable();
            $table->string('contractor_name')->default('TNT CONSTRUCTION AND TRADING');
            $table->date('week_start_date');
            $table->date('week_end_date');
            $table->enum('status', ['draft', 'submitted', 'checked', 'approved', 'rejected'])->default('draft');
            
            // Prepared by
            $table->foreignId('prepared_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('prepared_name')->nullable();
            $table->timestamp('prepared_at')->nullable();
            
            // Checked by
            $table->foreignId('checked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('checked_name')->nullable();
            $table->timestamp('checked_at')->nullable();
            
            // Approved by
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('approved_name')->nullable();
            $table->timestamp('approved_at')->nullable();
            
            // Rejection reason & notes
            $table->text('rejection_reason')->nullable();
            $table->text('page1_notes')->nullable();
            $table->text('page2_remark')->nullable();
            $table->text('page3_remark')->nullable();
            
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Causes of Claim Follow-up Checklist (Page 1)
        Schema::create('contractual_claim_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contractual_report_id')->constrained('contractual_reports')->onDelete('cascade');
            $table->string('category'); // design_issues, work_orders, suspension_of_works
            $table->string('category_title');
            $table->string('item_code'); // 1.1, 1.2, etc.
            $table->string('item_title');
            $table->boolean('monday')->default(false);
            $table->boolean('tuesday')->default(false);
            $table->boolean('wednesday')->default(false);
            $table->boolean('thursday')->default(false);
            $table->boolean('friday')->default(false);
            $table->boolean('saturday')->default(false);
            $table->boolean('sunday')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // 3. Detailed Information of the Claim Event (Page 2)
        Schema::create('contractual_claim_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contractual_report_id')->constrained('contractual_reports')->onDelete('cascade');
            $table->date('event_date')->nullable();
            $table->string('issue_number')->nullable(); // e.g. 1.1, 2.1, 3.2
            $table->text('cause_description')->nullable();
            $table->text('effect_on_progress')->nullable();
            $table->text('action_taken')->nullable();
            $table->text('remark')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // 4. Site Correspondence Follow-up Checklist (Page 3)
        Schema::create('contractual_correspondence_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contractual_report_id')->constrained('contractual_reports')->onDelete('cascade');
            $table->enum('direction', ['incoming', 'outgoing']);
            $table->string('direction_title'); // In coming Letters, Out going Letters
            $table->string('item_code'); // 1.0, 2.0, 3.0
            $table->string('party_title'); // From Client, From Consultant, Others, etc.
            $table->boolean('monday')->default(false);
            $table->boolean('tuesday')->default(false);
            $table->boolean('wednesday')->default(false);
            $table->boolean('thursday')->default(false);
            $table->boolean('friday')->default(false);
            $table->boolean('saturday')->default(false);
            $table->boolean('sunday')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contractual_correspondence_checks');
        Schema::dropIfExists('contractual_claim_events');
        Schema::dropIfExists('contractual_claim_checks');
        Schema::dropIfExists('contractual_reports');
    }
};
