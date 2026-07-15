<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('project_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('role'); // project_manager, site_engineer, quantity_surveyor, supervisor, foreman
            $table->date('assigned_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('responsibilities')->nullable();
            $table->timestamps();
            
            $table->unique(['project_id', 'user_id', 'role']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('project_user');
    }
};
