<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Notification;

class NotificationSeeder extends Seeder
{
    public function run()
    {
        $notifications = [
            [
                'user_id' => 1,
                'type' => 'ipc_submitted',
                'title' => 'IPC-001 Submitted for Approval',
                'message' => 'Water proofing IPC has been submitted and requires your approval.',
                'icon' => 'file-invoice',
                'color' => 'warning',
                'link' => '/ipcs/1',
            ],
            [
                'user_id' => 1,
                'type' => 'budget_exceeded',
                'title' => 'Budget Alert: Site Clearance',
                'message' => 'Actual cost has exceeded budget for Site Clearance item.',
                'icon' => 'exclamation-triangle',
                'color' => 'danger',
                'link' => '/boq-items/1',
            ],
            [
                'user_id' => 1,
                'type' => 'deadline',
                'title' => 'Project Deadline Approaching',
                'message' => 'Megenagna Bus Terminal project due in 15 days.',
                'icon' => 'clock',
                'color' => 'info',
                'link' => '/projects/1',
            ],
        ];

        foreach ($notifications as $n) {
            Notification::create($n);
        }

        echo "✅ Sample notifications created!\n";
    }
}
