<?php

namespace App\Livewire\Engineering;

use App\Livewire\Engineering\Concerns\InteractsWithEngineering;
use App\Models\departments;
use App\Models\Engineering\MaintenanceJob;
use App\Models\Engineering\Room;
use App\Services\Engineering\EngineeringAccess;
use App\Services\Engineering\RequisitionService;
use App\Support\Engineering\Eng;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Ward orders for medical oxygen cylinders, delivered and timed like any other job.
 */
class OxygenOrders extends Component
{
    use InteractsWithEngineering, WithPagination;

    protected $paginationTheme = 'bootstrap';

    public string $cylinderType = '';

    public int $quantity = 1;

    public bool $emergency = false;

    public string $phone = '';

    public ?string $departmentId = null;

    public ?string $roomId = null;

    public string $locationText = '';

    public function mount(): void
    {
        $user = Auth::user();
        $employee = EngineeringAccess::employee($user);

        $this->phone = (string) ($employee?->phone ?? $user->phone_number);
        $this->departmentId = $employee?->department_id;
    }

    public function updatedDepartmentId(): void
    {
        $this->roomId = null;
    }

    public function order(RequisitionService $service): void
    {
        $this->validate([
            'cylinderType' => ['required', Rule::in(config('engineering.oxygen.cylinder_types'))],
            'quantity' => ['required', 'integer', 'min:1', 'max:100'],
            'departmentId' => ['required'],
            'phone' => ['required', 'string', 'max:30'],
            'locationText' => ['required_without:roomId', 'nullable', 'string', 'max:200'],
        ], [
            'departmentId.required' => 'Select the ward or department.',
            'locationText.required_without' => 'Pick a room or describe where to deliver.',
        ], ['cylinderType' => 'cylinder type']);

        $ordered = $this->attempt(function () use ($service) {
            $job = $service->orderOxygen(Auth::user(), [
                'cylinder_type' => $this->cylinderType,
                'qty_ordered' => $this->quantity,
                'emergency' => $this->emergency,
                'phone' => $this->phone,
                'department_id' => $this->departmentId,
                'room_id' => $this->roomId,
                'location_text' => $this->locationText,
            ]);

            session()->flash('success', "Oxygen order {$job->requisition->number} sent to engineering.");
        });

        if ($ordered) {
            $this->reset(['cylinderType', 'emergency', 'roomId', 'locationText']);
            $this->quantity = 1;
            $this->resetPage();
        }
    }

    protected function ordersQuery(): Builder
    {
        return MaintenanceJob::query()
            ->whereHas('oxygenOrder')
            ->whereHas('requisition', fn (Builder $q) => $q->where('requested_by', Auth::id()))
            ->with(['oxygenOrder.deliveredBy:id,first_name,last_name', 'requisition.room', 'requisition.department:id,name', 'engineer:id,first_name,last_name,phone'])
            ->latest();
    }

    public function export()
    {
        return $this->exportTable(
            'Oxygen cylinder orders',
            ['S/N', 'Order No.', 'Ordered', 'Ward / department', 'Room / place', 'Cylinder', 'Qty ordered', 'Urgency', 'Status', 'Delivered by', 'Delivered at', 'Qty delivered', 'Received by'],
            $this->ordersQuery()->get()->values()->map(fn (MaintenanceJob $job, int $i) => [
                $i + 1,
                $job->requisition->number,
                Eng::dt($job->requisition->requested_at),
                $job->requisition->department?->name,
                $job->requisition->locationLabel(),
                $job->oxygenOrder->cylinder_type,
                $job->oxygenOrder->qty_ordered,
                ucfirst($job->requisition->priority),
                $job->status->label(),
                Eng::name($job->oxygenOrder->deliveredBy),
                Eng::dt($job->oxygenOrder->delivered_at),
                $job->oxygenOrder->qty_delivered,
                $job->oxygenOrder->received_by,
            ]),
        );
    }

    public function render()
    {
        return view('livewire.engineering.oxygen-orders', [
            'orders' => $this->ordersQuery()->paginate(10),
            'cylinderTypes' => config('engineering.oxygen.cylinder_types'),
            'departments' => departments::orderBy('name')->get(['id', 'name']),
            'rooms' => $this->departmentId
                ? Room::where('department_id', $this->departmentId)->where('status', 'active')->orderBy('name')->get()
                : collect(),
        ]);
    }
}
