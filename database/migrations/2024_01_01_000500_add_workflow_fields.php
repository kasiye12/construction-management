<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Material Delivery workflow fields
        if (!Schema::hasColumn('material_deliveries', 'status')) {
            Schema::table('material_deliveries', function (Blueprint $table) {
                $table->string('status')->default('recorded')->after('remarks');
                $table->foreignId('confirmed_by')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamp('confirmed_at')->nullable();
            });
        }

        // Quantity Takeoff verified_by already exists, just ensure approved status works
    }

    public function down() {}
};
