<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('material_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('subcontractor_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('boq_item_id')->nullable()->constrained()->onDelete('set null');
            $table->string('item_description');
            $table->string('unit');
            $table->decimal('quantity', 15, 4);
            $table->decimal('unit_multiplier', 10, 4)->default(1); // e.g., 1 roll = 10 m²
            $table->decimal('converted_quantity', 15, 4)->nullable(); // Auto-calculated
            $table->string('gate_pass_number')->nullable();
            $table->date('delivery_date');
            $table->string('source_location')->nullable(); // e.g., Head Office
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down() { Schema::dropIfExists('material_deliveries'); }
};
