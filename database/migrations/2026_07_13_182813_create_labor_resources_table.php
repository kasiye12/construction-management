<?php
// database/migrations/2024_01_01_000005_create_labor_resources_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('labor_resources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('boq_item_id')->constrained()->onDelete('cascade');
            $table->string('trade_name'); // Equipment Operator, Grease Boy, Carpenter
            $table->decimal('number_of_workers', 10, 2)->default(0);
            $table->decimal('total_hours', 10, 2)->default(0);
            $table->decimal('wage_per_day', 10, 2)->default(0);
            $table->decimal('amount', 15, 2)->default(0); // calculated
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('labor_resources');
    }
};