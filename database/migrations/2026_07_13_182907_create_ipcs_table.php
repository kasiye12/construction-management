<?php
// database/migrations/2024_01_01_000008_create_ipcs_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('ipcs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('subcontractor_id')->constrained()->onDelete('cascade');
            $table->string('ipc_number'); // IPC-1, IPC-2, Payment No. 01
            $table->integer('issue_number')->default(1);
            $table->date('ipc_date');
            $table->date('period_start_date');
            $table->date('period_end_date');
            $table->decimal('total_previous_amount', 15, 2)->default(0);
            $table->decimal('total_current_amount', 15, 2)->default(0);
            $table->decimal('total_to_date_amount', 15, 2)->default(0);
            $table->decimal('retention_percentage', 5, 2)->default(5.00);
            $table->decimal('retention_amount', 15, 2)->default(0);
            $table->decimal('net_payment_amount', 15, 2)->default(0);
            $table->text('remarks')->nullable();
            $table->enum('status', ['draft', 'submitted', 'approved', 'paid'])->default('draft');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('ipcs');
    }
};