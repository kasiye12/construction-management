<?php
// database/migrations/2024_01_01_000006_create_material_resources_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('material_resources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('boq_item_id')->constrained()->onDelete('cascade');
            $table->string('description'); // Select Material, Cement
            $table->string('unit'); // m3, bag, kg
            $table->decimal('quantity', 15, 4)->default(0);
            $table->decimal('unit_rate', 15, 2)->default(0);
            $table->decimal('amount', 15, 2)->default(0); // calculated
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('material_resources');
    }
};