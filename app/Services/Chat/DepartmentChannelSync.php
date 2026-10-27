<?php

namespace App\Services\Chat;

use App\Events\Chat\ChatActivity;
use App\Models\ChatConversation;
use App\Models\ChatMember;
use App\Models\departments;
use App\Models\Employee;
use App\Support\DepartmentSupervision;
use Illuminate\Support\Facades\DB;

/**
 * Keeps one channel per department whose roster mirrors HR records (PRD 2.1):
 * active staff with a login are members, department supervisors are admins.
 */
class DepartmentChannelSync
{
    public function channelFor(departments $department): ChatConversation
    {
        $channel = ChatConversation::firstOrCreate(
            ['department_id' => $department->id],
            [
                'type' => 'department',
                'name' => $department->name,
                'topic' => 'Official channel for '.$department->name,
                'status' => 'active',
            ]
        );

        if ($channel->name !== $department->name) {
            $channel->update(['name' => $department->name]);
        }

        return $channel;
    }

    /**
     * @return array{added: int, removed: int}
     */
    public function sync(departments $department): array
    {
        $channel = $this->channelFor($department);

        $staff = Employee::where('department_id', $department->id)
            ->where('status', 'Active')
            ->whereNotNull('user_id')
            ->pluck('user_id')
            ->all();
        $supervisors = DepartmentSupervision::supervisorUserIds($department);

        $desired = [];
        foreach ($staff as $id) {
            $desired[$id] = 'member';
        }
        foreach ($supervisors as $id) {
            $desired[$id] = 'admin';
        }

        $current = ChatMember::where('conversation_id', $channel->id)->get()->keyBy('user_id');

        $added = array_diff_key($desired, $current->all());
        $removed = $current->keys()->diff(array_keys($desired))->values()->all();

        DB::transaction(function () use ($channel, $desired, $current, $added, $removed) {
            foreach ($added as $userId => $role) {
                // New members start "caught up" rather than with years of unread history
                ChatMember::create(['conversation_id' => $channel->id, 'user_id' => $userId, 'role' => $role, 'last_read_at' => now()]);
            }

            if ($removed !== []) {
                ChatMember::where('conversation_id', $channel->id)->whereIn('user_id', $removed)->delete();
            }

            foreach ($current as $userId => $member) {
                if (isset($desired[$userId]) && $member->role !== $desired[$userId]) {
                    $member->update(['role' => $desired[$userId]]);
                }
            }
        });

        $changed = array_merge(array_keys($added), $removed);
        foreach (array_chunk($changed, 100) as $chunk) {
            try {
                event(new ChatActivity($chunk, 'membership', $channel->id, true));
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return ['added' => count($added), 'removed' => count($removed)];
    }

    /**
     * @return array{channels: int, added: int, removed: int}
     */
    public function syncAll(): array
    {
        $totals = ['channels' => 0, 'added' => 0, 'removed' => 0];

        departments::query()->orderBy('name')->each(function (departments $department) use (&$totals) {
            $result = $this->sync($department);
            $totals['channels']++;
            $totals['added'] += $result['added'];
            $totals['removed'] += $result['removed'];
        });

        return $totals;
    }

    /**
     * Departments affected by an employee change: old and new department, plus any department
     * supervised through the old or new job title.
     *
     * @return array<int, string>
     */
    public static function departmentsAffectedBy(Employee $employee): array
    {
        $ids = array_filter([$employee->department_id, $employee->getOriginal('department_id')]);
        $titles = array_filter([$employee->title_id, $employee->getOriginal('title_id')]);

        if ($titles !== []) {
            $ids = array_merge($ids, departments::whereIn('supervisor_title_id', $titles)->pluck('id')->all());
        }

        return array_values(array_unique($ids));
    }
}
