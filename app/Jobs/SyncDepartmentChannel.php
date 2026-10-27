<?php

namespace App\Jobs;

use App\Models\departments;
use App\Services\Chat\DepartmentChannelSync;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Re-syncs one department channel roster. Unique per department so a bulk
 * staff import queues one sync per department, not one per employee.
 */
class SyncDepartmentChannel implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $uniqueFor = 60;

    public function __construct(public string $departmentId) {}

    public function uniqueId(): string
    {
        return $this->departmentId;
    }

    public function handle(DepartmentChannelSync $sync): void
    {
        $department = departments::find($this->departmentId);
        if ($department) {
            $sync->sync($department);
        }
    }
}
