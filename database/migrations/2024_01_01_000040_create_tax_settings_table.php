<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tax_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('display_name');
            $table->decimal('rate', 8, 2)->default(0);
            $table->string('type')->default('percentage'); // percentage or fixed
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Insert default settings
        DB::table('tax_settings')->insert([
            [
                'key' => 'vat',
                'display_name' => 'VAT (Value Added Tax)',
                'rate' => 15.00,
                'type' => 'percentage',
                'description' => 'Standard VAT rate applied to all certificates',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'retention',
                'display_name' => 'Retention Fee',
                'rate' => 5.00,
                'type' => 'percentage',
                'description' => 'Retention percentage held from each payment',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'withholding_tax',
                'display_name' => 'Withholding Tax',
                'rate' => 2.00,
                'type' => 'percentage',
                'description' => 'Tax withheld at source',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'service_charge',
                'display_name' => 'Service Charge',
                'rate' => 0.00,
                'type' => 'percentage',
                'description' => 'Additional service charge',
                'is_active' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('tax_settings');
    }
};
