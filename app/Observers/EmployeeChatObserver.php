<?php

namespace App\Observers;

use App\Jobs\SyncDepartmentChannel;
use App\Models\Employee;
use App\Services\Chat\DepartmentChannelSync;

/** Hire, transfer, title change or termination → department channel rosters follow. */
class EmployeeChatObserver
{
    public function saved(Employee $employee): void
    {
        if ($employee->wasRecentlyCreated || $employee->wasChanged(['department_id', 'status', 'user_id', 'title_id'])) {
            $this->queue($employee);
        }
    }

    public function deleted(Employee $employee): void
    {
        $this->queue($employee);
    }

    public function restored(Employee $employee): void
    {
        $this->queue($employee);
    }

    protected function queue(Employee $employee): void
    {
        foreach (DepartmentChannelSync::departmentsAffectedBy($employee) as $departmentId) {
            SyncDepartmentChannel::dispatch($departmentId)->afterCommit();
        }
    }
}
