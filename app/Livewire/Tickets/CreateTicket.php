<?php

namespace App\Livewire\Tickets;

use App\Models\departments;
use App\Models\Employee;
use App\Models\EmployeeAssignedDuty;
use App\Models\TicketCategory;
use App\Services\Tickets\TicketActionException;
use App\Services\Tickets\TicketService;
use App\Support\EncryptedFileStore;
use App\Support\HtmlSanitizer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;

class CreateTicket extends Component
{
    use WithFileUploads;

    public const ATTACHMENT_RULES = EncryptedFileStore::UPLOAD_RULES;

    public $subject = '';

    public $description = '';

    public $category_id = '';

    public $priority = 'medium';

    public $department_id = '';

    public $assignee_employee_id = '';

    public $employee_assigned_duty_id = '';

    public $due_date = '';

    public $visibility = 'participants';

    public $attachments = [];

    public function mount()
    {
        abort_unless(Auth::user()->isSuperAdmin() || Auth::user()->can('create-tickets'), 403);

        $this->due_date = now()->addDays(3)->toDateString();

        // Pre-fill from chat shortcuts: ?assignee=<employee id>&subject=...
        $this->subject = Str::limit((string) request()->query('subject', ''), 150, '');
        $assignee = Employee::where('id', (string) request()->query('assignee'))->whereNotNull('user_id')->first();
        if ($assignee) {
            $this->department_id = $assignee->department_id;
            $this->assignee_employee_id = $assignee->id;
        }
    }

    public function updatedCategoryId($value)
    {
        $category = TicketCategory::find($value);
        if (! $category) {
            return;
        }

        if ($category->default_department_id && ! $this->department_id) {
            $this->department_id = $category->default_department_id;
        }

        if ($category->default_sla_hours) {
            $this->due_date = now()->addHours($category->default_sla_hours)->toDateString();
        }
    }

    public function updatedDepartmentId()
    {
        $this->assignee_employee_id = '';
        $this->employee_assigned_duty_id = '';
    }

    public function updatedAssigneeEmployeeId()
    {
        $this->employee_assigned_duty_id = '';
    }

    public function removeAttachment($index)
    {
        unset($this->attachments[$index]);
        $this->attachments = array_values($this->attachments);
    }

    public function save(TicketService $service)
    {
        $this->validate([
            'subject' => ['required', 'string', 'max:150'],
            'description' => ['required', 'string', 'max:65000'],
            'category_id' => ['required', 'exists:ticket_categories,id'],
            'priority' => ['required', 'in:low,medium,high,critical'],
            'department_id' => ['required', 'exists:departments,id'],
            'assignee_employee_id' => ['nullable', 'exists:employees,id'],
            'employee_assigned_duty_id' => ['nullable', 'exists:employee_assigned_duties,id'],
            'due_date' => ['required', 'date', 'after_or_equal:today'],
            'visibility' => ['required', 'in:participants,department'],
            'attachments' => ['array', 'max:10'],
            'attachments.*' => self::ATTACHMENT_RULES,
        ], [], ['assignee_employee_id' => 'assigned officer', 'employee_assigned_duty_id' => 'duty']);

        if (HtmlSanitizer::isBlank($this->description)) {
            $this->addError('description', 'The description field is required.');

            return;
        }

        try {
            $ticket = $service->create([
                'subject' => $this->subject,
                'description' => $this->description,
                'category_id' => $this->category_id,
                'priority' => $this->priority,
                'department_id' => $this->department_id,
                'assignee_employee_id' => $this->assignee_employee_id ?: null,
                'employee_assigned_duty_id' => $this->employee_assigned_duty_id ?: null,
                'due_date' => $this->due_date,
                'visibility' => $this->visibility,
            ], Auth::user(), $this->attachments);
        } catch (TicketActionException $e) {
            session()->flash('error', $e->getMessage());

            return;
        }

        session()->flash('success', "Ticket {$ticket->ticket_number} created successfully.");

        return $this->redirectRoute('tickets.view', ['ticket' => $ticket->id]);
    }

    public function render()
    {
        $employees = $this->department_id
            ? Employee::where('department_id', $this->department_id)
                ->where('status', 'Active')
                ->whereNotNull('user_id')
                ->orderBy('first_name')
                ->get(['id', 'first_name', 'middle_name', 'last_name', 'employee_no'])
            : collect();

        $duties = $this->assignee_employee_id
            ? EmployeeAssignedDuty::linkableTo($this->assignee_employee_id)
                ->orderBy('end_date')
                ->get(['id', 'duty_name', 'measurement_type', 'target_value', 'target_unit', 'end_date'])
            : collect();

        return view('livewire.tickets.create-ticket', [
            'categories' => TicketCategory::active()->orderBy('name')->get(['id', 'name', 'default_sla_hours']),
            'departments' => departments::orderBy('name')->get(['id', 'name']),
            'employees' => $employees,
            'duties' => $duties,
        ]);
    }
}
