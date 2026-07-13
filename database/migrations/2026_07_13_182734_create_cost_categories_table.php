<?php
// database/migrations/2024_01_01_000003_create_cost_categories_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('cost_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->string('code')->nullable(); // A, B, C etc
            $table->string('name'); // SUB-STRUCTURE, SUPER-STRUCTURE
            $table->text('description')->nullable();
            $table->integer('display_order')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('cost_categories');
    }
};