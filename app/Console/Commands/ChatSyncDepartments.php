<?php

namespace App\Console\Commands;

use App\Services\Chat\DepartmentChannelSync;
use Illuminate\Console\Command;

class ChatSyncDepartments extends Command
{
    protected $signature = 'chat:sync-departments';

    protected $description = 'Create a chat channel per department and sync its members from HR records';

    public function handle(DepartmentChannelSync $sync): int
    {
        $totals = $sync->syncAll();

        $this->info("Synced {$totals['channels']} department channel(s): {$totals['added']} member(s) added, {$totals['removed']} removed.");

        return self::SUCCESS;
    }
}
