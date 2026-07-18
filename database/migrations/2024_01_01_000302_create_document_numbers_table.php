<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('document_numbers', function (Blueprint $table) {
            $table->id();
            $table->string('prefix'); // e.g., OF/TNT/ECD
            $table->string('type'); // certificate, takeoff, delivery
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->integer('sequence_number')->default(0);
            $table->integer('year');
            $table->timestamps();
            
            $table->unique(['prefix', 'type', 'project_id', 'year']);
        });
    }

    public function down() { Schema::dropIfExists('document_numbers'); }
};
