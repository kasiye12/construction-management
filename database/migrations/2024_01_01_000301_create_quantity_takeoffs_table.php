<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('quantity_takeoffs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('boq_item_id')->constrained()->onDelete('cascade');
            $table->string('structure_type')->nullable(); // e.g., Foundation Footing
            $table->string('element_id')->nullable(); // e.g., F1, F2, F3
            $table->string('location_axis')->nullable(); // e.g., B/n axis(( B-I //10-3))
            $table->integer('quantity_count')->default(1);
            $table->decimal('length', 15, 4)->nullable();
            $table->decimal('width', 15, 4)->nullable();
            $table->decimal('height_depth', 15, 4)->nullable();
            $table->decimal('total_area_volume', 15, 4)->nullable(); // Qty × L × W × H
            $table->date('measurement_date');
            $table->string('measured_by')->nullable();
            $table->string('verified_by')->nullable();
            $table->enum('status', ['draft', 'verified', 'approved'])->default('draft');
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down() { Schema::dropIfExists('quantity_takeoffs'); }
};
