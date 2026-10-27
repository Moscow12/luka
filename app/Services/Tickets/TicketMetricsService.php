<?php

namespace App\Services\Tickets;

use App\Enums\TicketStatus;
use App\Models\departments;
use App\Models\Employee;
use App\Models\Ticket;
use App\Models\TicketStatusLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * PRD §6 operational metrics plus per-employee ticket scorecards.
 * Aggregation happens in PHP so the same code runs on MySQL and SQLite.
 */
class TicketMetricsService
{
    /**
     * @param  array{from?:?string, to?:?string, department_id?:?string, department_ids?:array<int, string>, employee_id?:?string}  $filters
     */
    public function query(array $filters = []): Builder
    {
        return Ticket::query()
            ->when($filters['from'] ?? null, fn (Builder $q, $from) => $q->whereDate('created_at', '>=', $from))
            ->when($filters['to'] ?? null, fn (Builder $q, $to) => $q->whereDate('created_at', '<=', $to))
            ->when($filters['department_id'] ?? null, fn (Builder $q, $id) => $q->where('department_id', $id))
            ->when(isset($filters['department_ids']), fn (Builder $q) => $q->whereIn('department_id', $filters['department_ids']))
            ->when($filters['employee_id'] ?? null, fn (Builder $q, $id) => $q->where('assignee_employee_id', $id));
    }

    /**
     * @return array{total:int, active:int, overdue:int, resolved:int, art_hours:?float, reopen_rate:?float}
     */
    public function summary(array $filters = []): array
    {
        $tickets = $this->query($filters)->get(['id', 'status', 'due_date', 'created_at', 'resolved_at', 'reopen_count']);

        return $this->summarise($tickets);
    }

    /**
     * Ticket counts grouped by department and by assignee.
     *
     * @return array{departments: Collection, assignees: Collection}
     */
    public function workload(array $filters = []): array
    {
        $tickets = $this->query($filters)->get(['id', 'status', 'due_date', 'department_id', 'assignee_employee_id']);
        $departmentNames = departments::whereIn('id', $tickets->pluck('department_id')->unique())->pluck('name', 'id');
        $employees = Employee::whereIn('id', $tickets->pluck('assignee_employee_id')->filter()->unique())->get()->keyBy('id');

        $bucket = fn (Collection $group) => [
            'total' => $group->count(),
            'active' => $group->filter(fn (Ticket $t) => in_array($t->status->value, TicketStatus::activeValues(), true))->count(),
            'overdue' => $group->filter(fn (Ticket $t) => $t->isOverdue())->count(),
            'resolved' => $group->filter(fn (Ticket $t) => in_array($t->status, [TicketStatus::Resolved, TicketStatus::Closed], true))->count(),
        ];

        return [
            'departments' => $tickets->groupBy('department_id')
                ->map(fn ($group, $id) => ['id' => $id, 'name' => $departmentNames[$id] ?? 'Unknown'] + $bucket($group))
                ->sortByDesc('active')->values(),
            'assignees' => $tickets->groupBy(fn (Ticket $t) => $t->assignee_employee_id ?? 'unassigned')
                ->map(fn ($group, $id) => [
                    'id' => $id,
                    'name' => $id === 'unassigned' ? 'Unassigned (queue)' : trim(($employees[$id]->first_name ?? '').' '.($employees[$id]->last_name ?? '')),
                    'department_id' => $group->first()->department_id,
                ] + $bucket($group))
                ->sortByDesc('active')->values(),
        ];
    }

    /**
     * Scorecard for each assignee in scope, best first.
     */
    public function scorecards(array $filters = []): Collection
    {
        $tickets = $this->query($filters)
            ->whereNotNull('assignee_employee_id')
            ->get(['id', 'status', 'due_date', 'created_at', 'resolved_at', 'closed_at', 'reopen_count', 'quality_rating', 'assignee_employee_id']);

        $employees = Employee::with('department')->whereIn('id', $tickets->pluck('assignee_employee_id')->unique())->get()->keyBy('id');

        return $tickets->groupBy('assignee_employee_id')
            ->map(function (Collection $group, $employeeId) use ($employees) {
                $employee = $employees[$employeeId] ?? null;

                return [
                    'employee_id' => $employeeId,
                    'name' => $employee ? trim($employee->first_name.' '.$employee->last_name) : 'Unknown',
                    'department' => $employee?->department?->name,
                ] + $this->scorecardFor($group);
            })
            ->sortByDesc(fn ($row) => $row['score'] ?? -1)
            ->values();
    }

    public function scorecardForEmployee(Employee $employee, array $filters = []): array
    {
        $tickets = $this->query(['employee_id' => $employee->id] + $filters)
            ->get(['id', 'status', 'due_date', 'created_at', 'resolved_at', 'closed_at', 'reopen_count', 'quality_rating']);

        return $this->scorecardFor($tickets);
    }

    /**
     * Average resolution time (hours) per month, for the last $months months.
     *
     * @return array{labels: array<int, string>, values: array<int, ?float>}
     */
    public function artTrend(array $filters = [], int $months = 6): array
    {
        $start = now()->startOfMonth()->subMonths($months - 1);

        $tickets = $this->query(array_merge($filters, ['from' => null, 'to' => null]))
            ->whereNotNull('resolved_at')
            ->where('resolved_at', '>=', $start)
            ->get(['created_at', 'resolved_at']);

        $labels = [];
        $values = [];
        for ($i = 0; $i < $months; $i++) {
            $month = $start->copy()->addMonths($i);
            $inMonth = $tickets->filter(fn (Ticket $t) => $t->resolved_at->isSameMonth($month));
            $labels[] = $month->format('M Y');
            $values[] = $inMonth->isEmpty() ? null : round($inMonth->avg(fn (Ticket $t) => $this->hoursBetween($t->created_at, $t->resolved_at)), 1);
        }

        return ['labels' => $labels, 'values' => $values];
    }

    // ------------------------------------------------------------------

    protected function summarise(Collection $tickets): array
    {
        $resolvedTickets = $tickets->filter(fn (Ticket $t) => $t->resolved_at !== null);
        $everResolved = $this->everResolvedCount($tickets);
        $reopened = $tickets->filter(fn (Ticket $t) => $t->reopen_count > 0)->count();

        return [
            'total' => $tickets->count(),
            'active' => $tickets->filter(fn (Ticket $t) => in_array($t->status->value, TicketStatus::activeValues(), true))->count(),
            'overdue' => $tickets->filter(fn (Ticket $t) => $t->isOverdue())->count(),
            'resolved' => $tickets->filter(fn (Ticket $t) => in_array($t->status, [TicketStatus::Resolved, TicketStatus::Closed], true))->count(),
            'art_hours' => $resolvedTickets->isEmpty() ? null : round($resolvedTickets->avg(fn (Ticket $t) => $this->hoursBetween($t->created_at, $t->resolved_at)), 1),
            'reopen_rate' => $everResolved === 0 ? null : round($reopened / $everResolved * 100, 1),
        ];
    }

    /**
     * Composite score = 40% on-time rate + 30% (avg rating / 5) + 30% (1 - reopen rate).
     */
    protected function scorecardFor(Collection $tickets): array
    {
        $summary = $this->summarise($tickets);
        $closed = $tickets->filter(fn (Ticket $t) => $t->status === TicketStatus::Closed);

        $onTimeRate = $closed->isEmpty() ? null : $closed->filter(fn (Ticket $t) => TicketDutySync::closedOnTime($t))->count() / $closed->count();
        $rated = $closed->whereNotNull('quality_rating');
        $avgRating = $rated->isEmpty() ? null : round($rated->avg('quality_rating'), 2);

        $score = null;
        if ($onTimeRate !== null) {
            $score = round(
                40 * $onTimeRate
                + 30 * (($avgRating ?? 0) / 5)
                + 30 * (1 - (($summary['reopen_rate'] ?? 0) / 100)),
                1
            );
        }

        return [
            'assigned' => $summary['total'],
            'active' => $summary['active'],
            'overdue' => $summary['overdue'],
            'closed' => $closed->count(),
            'on_time_rate' => $onTimeRate === null ? null : round($onTimeRate * 100, 1),
            'avg_rating' => $avgRating,
            'reopen_rate' => $summary['reopen_rate'],
            'art_hours' => $summary['art_hours'],
            'score' => $score,
        ];
    }

    /** Tickets that reached Resolved at least once (a reopened ticket may no longer carry resolved_at). */
    protected function everResolvedCount(Collection $tickets): int
    {
        if ($tickets->isEmpty()) {
            return 0;
        }

        return TicketStatusLog::whereIn('ticket_id', $tickets->pluck('id'))
            ->where('to_status', TicketStatus::Resolved->value)
            ->distinct()
            ->count('ticket_id');
    }

    protected function hoursBetween(Carbon $from, Carbon $to): float
    {
        return $from->diffInMinutes($to) / 60;
    }
}
