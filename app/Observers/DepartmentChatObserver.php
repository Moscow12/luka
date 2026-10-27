<?php

namespace App\Observers;

use App\Jobs\SyncDepartmentChannel;
use App\Models\departments;

/** New departments get a channel; renames and supervisor-title changes flow through. */
class DepartmentChatObserver
{
    public function saved(departments $department): void
    {
        if ($department->wasRecentlyCreated || $department->wasChanged(['name', 'supervisor_title_id'])) {
            SyncDepartmentChannel::dispatch($department->id)->afterCommit();
        }
    }
}
