<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('database_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->string('batch_id', 64)->unique();
            $table->string('uploaded_filename');
            $table->string('file_path');
            $table->unsignedBigInteger('file_size')->default(0);
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('admin_name')->nullable();
            $table->enum('status', ['previewed', 'applied', 'rolled_back', 'failed'])->default('previewed');
            $table->integer('tables_created_count')->default(0);
            $table->integer('columns_added_count')->default(0);
            $table->integer('records_inserted_count')->default(0);
            $table->integer('records_updated_count')->default(0);
            $table->integer('records_unchanged_count')->default(0);
            $table->string('backup_file')->nullable();
            $table->json('tables_created')->nullable();
            $table->json('columns_added')->nullable();
            $table->json('changes_summary')->nullable();
            $table->json('incompatible_warnings')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('applied_at')->nullable();
            $table->timestamp('rolled_back_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('database_sync_logs');
    }
};
