<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('company_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('text'); // text, image, textarea
            $table->string('group')->default('general');
            $table->timestamps();
        });

        // Insert defaults
        DB::table('company_settings')->insert([
            ['key' => 'company_name', 'value' => 'TNT Construction and Trading', 'type' => 'text', 'group' => 'company', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'company_name_amharic', 'value' => 'ቲኤንቲ ኮንስትራክሽንና ንግድ ሥራዎች', 'type' => 'text', 'group' => 'company', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'company_tagline', 'value' => 'General Contractor & Engineering Services', 'type' => 'text', 'group' => 'company', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'company_logo', 'value' => null, 'type' => 'image', 'group' => 'company', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'company_phone', 'value' => '+251-000-000000', 'type' => 'text', 'group' => 'company', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'company_email', 'value' => 'info@tnt-constructions.com', 'type' => 'text', 'group' => 'company', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'company_address', 'value' => 'Addis Ababa, Ethiopia', 'type' => 'text', 'group' => 'company', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'company_tin', 'value' => '000000000', 'type' => 'text', 'group' => 'company', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'company_website', 'value' => 'www.tnt-constructions.com', 'type' => 'text', 'group' => 'company', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'document_prefix', 'value' => 'OF/TNT/ECD', 'type' => 'text', 'group' => 'document', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down() { Schema::dropIfExists('company_settings'); }
};
