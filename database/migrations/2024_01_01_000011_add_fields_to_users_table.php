<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('engineer'); // admin, engineer, manager, viewer
            $table->string('department')->nullable();
            $table->string('phone')->nullable();
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'department', 'phone', 'is_active']);
        });
    }
};
