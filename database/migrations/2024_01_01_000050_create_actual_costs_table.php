<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('actual_costs')) {
            Schema::create('actual_costs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('project_id')->constrained()->onDelete('cascade');
                $table->foreignId('boq_item_id')->nullable()->constrained()->onDelete('set null');
                $table->string('cost_type'); // labor, material, equipment, other
                $table->string('description');
                $table->decimal('amount', 15, 2);
                $table->date('cost_date');
                $table->string('vendor')->nullable();
                $table->string('invoice_number')->nullable();
                $table->text('remarks')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('actual_costs');
    }
};
