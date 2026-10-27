<?php

namespace App\Services\Chat;

use App\Models\ChatConversation;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Global employee search (PRD 3): name, job title, department or employee number,
 * plus the rooms the viewer belongs to.
 */
class EmployeeDirectory
{
    /**
     * @return array{people: Collection, rooms: Collection}
     */
    public function search(string $term, User $viewer): array
    {
        $term = trim(preg_replace('/\s+/', ' ', $term));
        if (mb_strlen($term) < config('chat.search_min_chars')) {
            return ['people' => collect(), 'rooms' => collect()];
        }

        $words = array_slice(explode(' ', $term), 0, 4);
        $limit = config('chat.search_limit');

        $people = Employee::query()
            ->select('employees.id', 'employees.user_id', 'employees.first_name', 'employees.middle_name', 'employees.last_name',
                'employees.employee_no', 'employees.email as employee_email', 'employees.department_id',
                'jobtitles.name as job_title', 'departments.name as department_name')
            ->leftJoin('jobtitles', 'jobtitles.id', '=', 'employees.title_id')
            ->leftJoin('departments', 'departments.id', '=', 'employees.department_id')
            ->whereNotNull('employees.user_id')
            ->where('employees.user_id', '!=', $viewer->id)
            ->where('employees.status', 'Active')
            ->where(function (Builder $q) use ($words) {
                foreach ($words as $word) {
                    $like = '%'.addcslashes($word, '%_\\').'%';
                    $q->where(fn (Builder $w) => $w->where('employees.first_name', 'like', $like)
                        ->orWhere('employees.last_name', 'like', $like)
                        ->orWhere('employees.employee_no', 'like', $like)
                        ->orWhere('jobtitles.name', 'like', $like)
                        ->orWhere('departments.name', 'like', $like));
                }
            })
            ->orderBy('employees.first_name')
            ->limit($limit)
            ->get();

        $users = User::whereIn('id', $people->pluck('user_id'))->get(['id', 'email', 'profile_picture', 'last_seen_at'])->keyBy('id');

        $people = $people->map(fn (Employee $e) => [
            'user_id' => $e->user_id,
            'name' => trim($e->first_name.' '.$e->last_name),
            'initials' => strtoupper(mb_substr($e->first_name, 0, 1).mb_substr($e->last_name, 0, 1)),
            'email' => $users[$e->user_id]->email ?? $e->employee_email,
            'avatar' => $users[$e->user_id]?->profile_picture_url,
            'job_title' => $e->job_title,
            'department_id' => $e->department_id,
            'department' => $e->department_name,
            'employee_id' => $e->id,
            'last_seen_at' => $users[$e->user_id]?->last_seen_at,
        ]);

        $rooms = ChatConversation::query()
            ->whereIn('type', ['group', 'department'])
            ->whereHas('members', fn (Builder $m) => $m->where('user_id', $viewer->id))
            ->where('name', 'like', '%'.addcslashes($term, '%_\\').'%')
            ->orderBy('name')
            ->limit(8)
            ->get(['id', 'type', 'name', 'topic']);

        return ['people' => $people, 'rooms' => $rooms];
    }
}
