<?php

namespace App\Livewire\Hr\Attendance;

use App\Models\employeeattendances;
use Livewire\Component;
use Livewire\WithPagination;

class Managefpattendance extends Component
{
    use WithPagination;

    public $search = '';

    public $dateFrom = '';

    public $dateTo = '';

    public $deviceFilter = '';

    public $statusFilter = '';

    protected $paginationTheme = 'bootstrap';

    public function mount()
    {
        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');
    }

    public function updatingSearch()
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

    public function updatingDeviceFilter()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->reset(['search', 'deviceFilter', 'statusFilter']);
        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');
    }

    public function deleteAttendance($id)
    {
        try {
            employeeattendances::findOrFail($id)->delete();
            $this->dispatch('toaster', [
                'type' => 'success',
                'message' => 'Attendance record deleted successfully',
            ]);
        } catch (\Exception) {
            $this->dispatch('toaster', [
                'type' => 'error',
                'message' => 'Error deleting attendance record',
            ]);
        }
    }

    public function render()
    {
        $attendances = employeeattendances::query()
            ->with('fpuser')
            ->when($this->dateFrom, function ($query) {
                $query->whereDate('clockdate', '>=', $this->dateFrom);
            })
            ->when($this->dateTo, function ($query) {
                $query->whereDate('clockdate', '<=', $this->dateTo);
            })
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('fpuser_id', 'like', '%'.$this->search.'%')
                        ->orWhere('device_id', 'like', '%'.$this->search.'%')
                        ->orWhere('clocktimestamp', 'like', '%'.$this->search.'%')
                        ->orWhereHas('fpuser', function ($q) {
                            $q->where('name', 'like', '%'.$this->search.'%');
                        });
                });
            })
            ->when($this->deviceFilter, function ($query) {
                $query->where('device_id', $this->deviceFilter);
            })
            ->when($this->statusFilter, function ($query) {
                $query->where('clock_status', $this->statusFilter);
            })
            ->latest('clockdate')
            ->latest('clocktime')
            ->paginate(15);

        $devices = employeeattendances::distinct()->pluck('device_id');
        $statuses = employeeattendances::distinct()->whereNotNull('clock_status')->pluck('clock_status');

        return view('livewire.hr.attendance.managefpattendance', [
            'attendances' => $attendances,
            'devices' => $devices,
            'statuses' => $statuses,
        ]);
    }
}
