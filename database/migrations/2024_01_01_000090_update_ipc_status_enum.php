<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Change status column to accept all workflow statuses
        DB::statement("ALTER TABLE ipcs MODIFY COLUMN status VARCHAR(20) DEFAULT 'draft'");
    }

    public function down()
    {
        DB::statement("ALTER TABLE ipcs MODIFY COLUMN status ENUM('draft','submitted','approved','paid') DEFAULT 'draft'");
    }
};
