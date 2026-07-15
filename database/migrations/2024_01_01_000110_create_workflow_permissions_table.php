<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('workflow_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('workflow_step'); // prepare, check, submit, approve, reject, pay
            $table->boolean('can_act')->default(true);
            $table->timestamps();
            
            $table->unique(['user_id', 'workflow_step']);
        });

        // Insert default permissions for existing users
        $users = DB::table('users')->get();
        $steps = ['prepare', 'check', 'submit', 'approve', 'reject', 'pay'];
        
        foreach ($users as $user) {
            $role = $user->role ?? 'viewer';
            foreach ($steps as $step) {
                $canAct = false;
                switch ($role) {
                    case 'admin': $canAct = true; break;
                    case 'manager': $canAct = in_array($step, ['prepare','check','submit','approve','reject']); break;
                    case 'engineer': $canAct = in_array($step, ['prepare','check','submit']); break;
                    case 'finance': $canAct = in_array($step, ['approve','reject','pay']); break;
                }
                if ($canAct) {
                    DB::table('workflow_permissions')->insert([
                        'user_id' => $user->id,
                        'workflow_step' => $step,
                        'can_act' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    public function down() { Schema::dropIfExists('workflow_permissions'); }
};
