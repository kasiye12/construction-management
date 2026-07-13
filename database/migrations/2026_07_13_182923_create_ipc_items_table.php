<?php
// database/migrations/2024_01_01_000009_create_ipc_items_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('ipc_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ipc_id')->constrained()->onDelete('cascade');
            $table->foreignId('boq_item_id')->constrained()->onDelete('cascade');
            $table->decimal('contract_quantity', 15, 4)->default(0);
            $table->decimal('contract_amount', 15, 2)->default(0);
            $table->decimal('previous_quantity', 15, 4)->default(0);
            $table->decimal('previous_amount', 15, 2)->default(0);
            $table->decimal('current_quantity', 15, 4)->default(0);
            $table->decimal('current_amount', 15, 2)->default(0);
            $table->decimal('to_date_quantity', 15, 4)->default(0);
            $table->decimal('to_date_amount', 15, 2)->default(0);
            $table->decimal('percentage_complete', 5, 2)->default(0);
            $table->string('remark')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('ipc_items');
    }
};