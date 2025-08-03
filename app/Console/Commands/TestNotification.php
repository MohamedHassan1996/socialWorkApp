<?php

namespace App\Console\Commands;

use App\Services\Notification\NotificationService;
use Illuminate\Console\Command;

class TestNotification extends Command
{
        protected $signature = 'notification:test {userId}';
    protected $description = 'Send a test notification';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->argument('userId');

        NotificationService::send(
            $userId,
            'test',
            'Test Notification',
            'This is a test notification!',
            ['test_data' => 'Hello World']
        );

        $this->info("Test notification sent to user {$userId}");
    }
}
