<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('ipcs', function (Blueprint $table) {
            if (!Schema::hasColumn('ipcs', 'prepared_by')) {
                $table->string('prepared_by')->nullable();
                $table->timestamp('prepared_at')->nullable();
            }
            if (!Schema::hasColumn('ipcs', 'checked_by')) {
                $table->string('checked_by')->nullable();
                $table->timestamp('checked_at')->nullable();
            }
            if (!Schema::hasColumn('ipcs', 'submitted_by')) {
                $table->string('submitted_by')->nullable();
                $table->timestamp('submitted_at')->nullable();
            }
            if (!Schema::hasColumn('ipcs', 'approved_by')) {
                $table->string('approved_by')->nullable();
                $table->timestamp('approved_at')->nullable();
            }
            if (!Schema::hasColumn('ipcs', 'rejected_by')) {
                $table->string('rejected_by')->nullable();
                $table->timestamp('rejected_at')->nullable();
            }
            if (!Schema::hasColumn('ipcs', 'paid_by')) {
                $table->string('paid_by')->nullable();
                $table->timestamp('paid_at')->nullable();
            }
        });
    }

    public function down() {}
};
