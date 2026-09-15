<?php

namespace App\Livewire\Audit;

use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Activitylog\Models\Activity;

class Auditlog extends Component
{
    use WithPagination;

    public $search = '';

    public $log_name = '';

    public $event = '';

    public $causer_type = '';

    public $subject_type = '';

    public $date_from = '';

    public $date_to = '';

    public $perPage = 25;

    // Modal properties
    public $showDetailsModal = false;

    public $selectedActivity = null;

    // Filter visibility
    public $showFilters = false;

    protected $paginationTheme = 'bootstrap';

    protected $queryString = [
        'search' => ['except' => ''],
        'log_name' => ['except' => ''],
        'event' => ['except' => ''],
        'causer_type' => ['except' => ''],
        'subject_type' => ['except' => ''],
        'date_from' => ['except' => ''],
        'date_to' => ['except' => ''],
    ];

    public function mount()
    {
        // Default to today's activity.
        $this->date_from = now()->toDateString();
        $this->date_to = now()->toDateString();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingLogName()
    {
        $this->resetPage();
    }

    public function updatingEvent()
    {
        $this->resetPage();
    }

    public function updatingCauserType()
    {
        $this->resetPage();
    }

    public function updatingSubjectType()
    {
        $this->resetPage();
    }

    public function updatingDateFrom()
    {
        $this->resetPage();
    }

    public function updatingDateTo()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->reset([
            'search',
            'log_name',
            'event',
            'causer_type',
            'subject_type',
        ]);
        $this->date_from = now()->toDateString();
        $this->date_to = now()->toDateString();
        $this->resetPage();
    }

    public function viewDetails($activityId)
    {
        $this->selectedActivity = Activity::with(['causer', 'subject'])->find($activityId);
        $this->showDetailsModal = true;
    }

    public function closeDetailsModal()
    {
        $this->showDetailsModal = false;
        $this->selectedActivity = null;
    }

    public function toggleFilters()
    {
        $this->showFilters = ! $this->showFilters;
    }

    public function getActivitiesProperty()
    {
        $query = Activity::with(['causer', 'subject'])
            ->when($this->search, function ($q) {
                $q->where(function ($query) {
                    $query->where('description', 'like', '%'.$this->search.'%')
                        ->orWhere('log_name', 'like', '%'.$this->search.'%')
                        ->orWhere('event', 'like', '%'.$this->search.'%')
                        ->orWhereHas('causer', function ($q) {
                            $q->where('name', 'like', '%'.$this->search.'%')
                                ->orWhere('email', 'like', '%'.$this->search.'%');
                        });
                });
            })
            ->when($this->log_name, fn ($q) => $q->where('log_name', $this->log_name))
            ->when($this->event, fn ($q) => $q->where('event', $this->event))
            ->when($this->causer_type, fn ($q) => $q->where('causer_type', $this->causer_type))
            ->when($this->subject_type, fn ($q) => $q->where('subject_type', $this->subject_type))
            ->when($this->date_from, fn ($q) => $q->whereDate('created_at', '>=', $this->date_from))
            ->when($this->date_to, fn ($q) => $q->whereDate('created_at', '<=', $this->date_to))
            ->latest()
            ->paginate($this->perPage);

        return $query;
    }

    public function getLogNamesProperty()
    {
        return Activity::select('log_name')
            ->distinct()
            ->whereNotNull('log_name')
            ->orderBy('log_name')
            ->pluck('log_name');
    }

    public function getEventsProperty()
    {
        return Activity::select('event')
            ->distinct()
            ->whereNotNull('event')
            ->orderBy('event')
            ->pluck('event');
    }

    public function getCauserTypesProperty()
    {
        return Activity::select('causer_type')
            ->distinct()
            ->whereNotNull('causer_type')
            ->orderBy('causer_type')
            ->pluck('causer_type')
            ->map(function ($type) {
                return class_basename($type);
            });
    }

    public function getSubjectTypesProperty()
    {
        return Activity::select('subject_type')
            ->distinct()
            ->whereNotNull('subject_type')
            ->orderBy('subject_type')
            ->pluck('subject_type')
            ->map(function ($type) {
                return class_basename($type);
            });
    }

    public function render()
    {
        return view('livewire.audit.auditlog', [
            'activities' => $this->activities,
            'logNames' => $this->logNames,
            'events' => $this->events,
            'causerTypes' => $this->causerTypes,
            'subjectTypes' => $this->subjectTypes,
        ]);
    }
}
