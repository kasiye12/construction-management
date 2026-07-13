<?php
// database/migrations/2024_01_01_000004_create_boq_items_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('boq_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('cost_category_id')->nullable()->constrained('cost_categories')->onDelete('set null');
            $table->foreignId('parent_id')->nullable()->constrained('boq_items')->onDelete('cascade');
            $table->string('item_number'); // 1, 1.01, 1.02 etc
            $table->text('description');
            $table->string('unit'); // m2, m3, kg, pcs
            $table->decimal('quantity', 15, 4)->default(0);
            $table->decimal('unit_rate', 15, 2)->default(0);
            $table->decimal('revenue_amount', 15, 2)->default(0); // calculated: quantity * unit_rate
            $table->integer('duration_days')->nullable();
            $table->date('planned_start_date')->nullable();
            $table->date('planned_end_date')->nullable();
            $table->integer('display_order')->default(0);
            $table->boolean('is_parent')->default(false); // for grouping items
            $table->enum('status', ['pending', 'in_progress', 'completed'])->default('pending');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('boq_items');
    }
};