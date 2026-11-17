<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            if (!Schema::hasColumn('projects', 'location')) {
                $table->string('location')->nullable()->after('name');
            }
            if (!Schema::hasColumn('projects', 'consultant_name')) {
                $table->string('consultant_name')->nullable()->after('client_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            if (Schema::hasColumn('projects', 'consultant_name')) {
                $table->dropColumn('consultant_name');
            }
            if (Schema::hasColumn('projects', 'location')) {
                $table->dropColumn('location');
            }
        });
    }
};
