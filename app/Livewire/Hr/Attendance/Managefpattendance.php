<?php

namespace App\Livewire\Hr\Attendance;

use App\Models\employeeattendances;
use App\Models\fpusers;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Livewire\Component;

class Managefpattendance extends Component
{
    public $search = '';

    public $dateFrom = '';

    public $dateTo = '';

    public $perPage = 20;

    public $page = 1;

    public function mount()
    {
        // Default to last 7 days
        $this->dateFrom = now()->subDays(6)->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');
    }

    public function updatingSearch()
    {
        $this->page = 1;
    }

    public function updatingDateFrom()
    {
        $this->page = 1;
    }

    public function updatingDateTo()
    {
        $this->page = 1;
    }

    public function previousPage()
    {
        if ($this->page > 1) {
            $this->page--;
        }
    }

    public function nextPage()
    {
        $this->page++;
    }

    public function clearFilters()
    {
        $this->reset(['search']);
        $this->dateFrom = now()->subDays(6)->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');
        $this->page = 1;
    }

    public function render()
    {
        // Get date range for columns
        $startDate = Carbon::parse($this->dateFrom);
        $endDate = Carbon::parse($this->dateTo);
        $dates = collect(CarbonPeriod::create($startDate, $endDate))->map(fn ($date) => $date->format('Y-m-d'));

        // Get all fpusers with search filter
        $usersQuery = fpusers::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('fpdevice_id', 'like', '%'.$this->search.'%');
            })
            ->orderBy('name');

        $totalUsers = $usersQuery->count();
        $totalPages = ceil($totalUsers / $this->perPage);

        // Paginate users
        $users = $usersQuery
            ->skip(($this->page - 1) * $this->perPage)
            ->take($this->perPage)
            ->get();

        // Get attendance records for these users in date range
        $userIds = $users->pluck('fpdevice_id')->toArray();

        $attendances = employeeattendances::query()
            ->whereIn('fpuser_id', $userIds)
            ->whereBetween('clockdate', [$this->dateFrom, $this->dateTo])
            ->orderBy('clocktime')
            ->get()
            ->groupBy(['fpuser_id', 'clockdate']);

        // Build attendance matrix
        $attendanceMatrix = [];
        foreach ($users as $index => $user) {
            $row = [
                'index' => ($this->page - 1) * $this->perPage + $index + 1,
                'user' => $user,
                'dates' => [],
            ];

            foreach ($dates as $date) {
                $dayAttendance = $attendances[$user->fpdevice_id][$date] ?? collect();

                if ($dayAttendance->isEmpty()) {
                    $row['dates'][$date] = [
                        'status' => 'no_show',
                        'clock_in' => null,
                        'clock_out' => null,
                    ];
                } else {
                    // Get first check-in (earliest time)
                    $checkIn = $dayAttendance->sortBy('clocktime')->first();

                    // Get last check-out (latest time, different from check-in)
                    $checkOut = $dayAttendance->count() > 1
                        ? $dayAttendance->sortByDesc('clocktime')->first()
                        : null;

                    // If only one record, determine if it's IN or OUT based on time
                    if ($dayAttendance->count() === 1) {
                        $hour = (int) Carbon::parse($checkIn->clocktime)->format('H');
                        if ($hour >= 12) {
                            // Afternoon - likely checkout only
                            $checkOut = $checkIn;
                            $checkIn = null;
                        }
                    }

                    $row['dates'][$date] = [
                        'status' => 'present',
                        'clock_in' => $checkIn?->clocktime,
                        'clock_out' => $checkOut?->clocktime,
                        'has_checkout' => $checkOut !== null && $checkIn !== null && $checkOut->id !== $checkIn?->id,
                    ];
                }
            }

            $attendanceMatrix[] = $row;
        }

        return view('livewire.hr.attendance.managefpattendance', [
            'attendanceMatrix' => $attendanceMatrix,
            'dates' => $dates,
            'totalUsers' => $totalUsers,
            'totalPages' => $totalPages,
            'currentPage' => $this->page,
        ]);
    }
}
