<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\User;

class SyncCrmUsersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $adminId;

    public function __construct(int $adminId)
    {
        $this->adminId = $adminId;
    }

    public function handle(): void
    {
        // Example: pull from external CRM
        $crmUsers = $this->fetchCrmUsers();

        DB::transaction(function () use ($crmUsers) {
            foreach ($crmUsers as $crmUser) {
                User::updateOrCreate(
                    ['email' => $crmUser['email']],
                    [
                        'name' => $crmUser['name'],
                        'phone' => $crmUser['phone'],
                    ]
                );
            }
        });
    }

    protected function fetchCrmUsers(): array
    {
        // Simulated response — replace with actual CRM call
        return [
            ['name' => 'Alice', 'email' => 'alice@crm.com', 'phone' => '123456'],
            ['name' => 'Bob', 'email' => 'bob@crm.com', 'phone' => '654321'],
        ];
    }
}
