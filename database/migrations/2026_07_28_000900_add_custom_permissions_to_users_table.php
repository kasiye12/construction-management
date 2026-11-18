<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->json('custom_permissions')->nullable()->after('role_id');
            $table->boolean('is_super_admin')->default(false)->after('custom_permissions');
        });

        // Set existing admin user(s) as super admin
        \DB::table('users')->where('role', 'admin')->update(['is_super_admin' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['custom_permissions', 'is_super_admin']);
        });
    }
};
