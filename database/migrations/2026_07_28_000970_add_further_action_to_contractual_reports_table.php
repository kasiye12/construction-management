<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contractual_reports', function (Blueprint $table) {
            $table->boolean('needs_further_action')->default(false)->after('page3_remark');
            $table->text('further_action_notes')->nullable()->after('needs_further_action');
        });
    }

    public function down(): void
    {
        Schema::table('contractual_reports', function (Blueprint $table) {
            $table->dropColumn(['needs_further_action', 'further_action_notes']);
        });
    }
};
