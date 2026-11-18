<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            if (!Schema::hasColumn('projects', 'project_code')) {
                $table->string('project_code', 100)->nullable()->after('id');
            }
            if (!Schema::hasColumn('projects', 'contract_signing_date')) {
                $table->date('contract_signing_date')->nullable()->after('contractor_name');
            }
            if (!Schema::hasColumn('projects', 'tnt_share')) {
                $table->decimal('tnt_share', 5, 2)->nullable()->default(100.00)->after('contract_amount');
            }
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            if (Schema::hasColumn('projects', 'tnt_share')) {
                $table->dropColumn('tnt_share');
            }
            if (Schema::hasColumn('projects', 'contract_signing_date')) {
                $table->dropColumn('contract_signing_date');
            }
            if (Schema::hasColumn('projects', 'project_code')) {
                $table->dropColumn('project_code');
            }
        });
    }
};
