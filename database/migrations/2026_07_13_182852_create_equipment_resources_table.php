<?php
// database/migrations/2024_01_01_000007_create_equipment_resources_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('equipment_resources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('boq_item_id')->constrained()->onDelete('cascade');
            $table->string('description'); // Excavator, Loader
            $table->decimal('duration_days', 10, 2)->nullable();
            $table->integer('number_of_units')->default(1);
            $table->decimal('total_hours', 10, 2)->default(0);
            $table->decimal('rate_per_hour', 15, 2)->default(0);
            $table->decimal('amount', 15, 2)->default(0); // calculated
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('equipment_resources');
    }
};