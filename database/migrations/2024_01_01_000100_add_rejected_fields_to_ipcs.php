<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('ipcs', function (Blueprint $table) {
            if (!Schema::hasColumn('ipcs', 'rejected_by')) {
                $table->string('rejected_by')->nullable();
                $table->timestamp('rejected_at')->nullable();
            }
        });
    }

    public function down() {}
};
