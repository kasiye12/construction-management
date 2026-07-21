<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Takeoff Sheet (Parent)
        Schema::create('takeoff_sheets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->string('sheet_number');
            $table->string('division')->nullable();
            $table->integer('page_no')->default(1);
            $table->date('measurement_date');
            $table->string('measured_by')->nullable();
            $table->string('verified_by')->nullable();
            $table->string('approved_by')->nullable();
            $table->enum('status', ['draft', 'verified', 'approved'])->default('draft');
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        // 2. Item Numbers (under a sheet)
        Schema::create('takeoff_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('takeoff_sheet_id')->constrained()->onDelete('cascade');
            $table->foreignId('boq_item_id')->nullable()->constrained()->onDelete('set null');
            $table->string('item_number');
            $table->string('description')->nullable();
            $table->integer('display_order')->default(0);
            $table->timestamps();
        });

        // 3. Descriptions (Left/Right under an item)
        Schema::create('takeoff_descriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('takeoff_item_id')->constrained()->onDelete('cascade');
            $table->enum('side', ['left', 'right']);
            $table->string('description');
            $table->integer('display_order')->default(0);
            $table->timestamps();
        });

        // 4. Measurement Elements (under a description)
        Schema::create('takeoff_measurements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('takeoff_description_id')->constrained()->onDelete('cascade');
            $table->integer('quantity_count')->default(1);
            $table->decimal('length', 15, 4)->default(0);
            $table->decimal('width', 15, 4)->default(1);
            $table->decimal('height_depth', 15, 4)->default(1);
            $table->decimal('total_area_volume', 15, 4)->default(0);
            $table->text('remarks')->nullable();
            $table->integer('display_order')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('takeoff_measurements');
        Schema::dropIfExists('takeoff_descriptions');
        Schema::dropIfExists('takeoff_items');
        Schema::dropIfExists('takeoff_sheets');
    }
};
