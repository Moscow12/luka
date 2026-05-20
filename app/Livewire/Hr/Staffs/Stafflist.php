<?php

namespace App\Livewire\Hr\Staffs;

use App\Models\departments;
use App\Models\designations;
use App\Models\Employee;
use App\Models\Role;
use App\Models\SmsApiSetting;
use App\Models\User;
use App\Models\workstations;
use App\Notifications\StaffCredentials;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Stafflist extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public $search = '';

    #[Url]
    public $department = '';

    #[Url]
    public $designation = '';

    #[Url]
    public $workstation = '';

    #[Url]
    public $status = 'active';

    #[Url]
    public $gender = '';

    #[Url]
    public $employmentType = '';

    public $perPage = 10;

    public $showFilters = false;

    public $selectedEmployees = [];

    public $selectAll = false;

    public $bulkDepartmentId = '';

    public $bulkDesignationId = '';

    public function updatingSearch()
    {
        $this->resetPage();
        $this->clearSelection();
    }

    public function updatingDepartment()
    {
        $this->resetPage();
        $this->clearSelection();
    }

    public function updatingDesignation()
    {
        $this->resetPage();
        $this->clearSelection();
    }

    public function updatingWorkstation()
    {
        $this->resetPage();
        $this->clearSelection();
    }

    public function updatingStatus()
    {
        $this->resetPage();
        $this->clearSelection();
    }

    public function updatingGender()
    {
        $this->resetPage();
        $this->clearSelection();
    }

    public function updatingEmploymentType()
    {
        $this->resetPage();
        $this->clearSelection();
    }

    public function resetFilters()
    {
        $this->reset([
            'search',
            'department',
            'designation',
            'workstation',
            'status',
            'gender',
            'employmentType',
        ]);
        $this->resetPage();
        $this->clearSelection();
    }

    public function toggleFilters()
    {
        $this->showFilters = ! $this->showFilters;
    }

    private function clearSelection(): void
    {
        $this->selectedEmployees = [];
        $this->selectAll = false;
    }

    private function getEmployeesQuery()
    {
        return Employee::query()
            ->with(['department', 'designation', 'workstation', 'account'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('first_name', 'like', '%'.$this->search.'%')
                        ->orWhere('last_name', 'like', '%'.$this->search.'%')
                        ->orWhere('middle_name', 'like', '%'.$this->search.'%')
                        ->orWhere('employee_no', 'like', '%'.$this->search.'%')
                        ->orWhere('email', 'like', '%'.$this->search.'%')
                        ->orWhere('phone', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->department, fn ($query) => $query->where('department_id', $this->department))
            ->when($this->designation, fn ($query) => $query->where('designation_id', $this->designation))
            ->when($this->workstation, fn ($query) => $query->where('workstation_id', $this->workstation))
            ->when($this->status && $this->status !== 'all', fn ($query) => $query->where('status', $this->status))
            ->when($this->gender, fn ($query) => $query->where('gender', $this->gender))
            ->when($this->employmentType, fn ($query) => $query->where('employment_type', $this->employmentType))
            ->latest();
    }

    public function updatedSelectAll($value)
    {
        $this->selectedEmployees = $value
            ? $this->getEmployeesQuery()->pluck('id')->toArray()
            : [];
    }

    public function sendCredentialsToSelected()
    {
        if (empty($this->selectedEmployees)) {
            $this->dispatch('toastMagic', status: 'warning', title: 'No selection', message: 'Please select at least one staff member.');

            return;
        }

        $hasSmsProvider = SmsApiSetting::where('is_active', true)->exists();
        $defaultRole = Role::where('name', 'user')->first()?->name;

        $created = 0;
        $smsSent = 0;
        $emailFailed = 0;
        $skipped = [];

        $employees = Employee::whereIn('id', $this->selectedEmployees)->get();

        foreach ($employees as $employee) {
            $name = trim($employee->first_name.' '.$employee->last_name);

            try {
                if ($employee->user_id) {
                    $skipped[] = "{$name} (already has account)";

                    continue;
                }
                if (empty($employee->email)) {
                    $skipped[] = "{$name} (no email)";

                    continue;
                }
                $password = (string) $employee->employee_no;
                if ($password === '') {
                    $skipped[] = "{$name} (no employee number)";

                    continue;
                }

                DB::beginTransaction();

                // Link to a pre-existing user with this email/username instead of erroring on the unique constraint.
                $existing = User::where('email', $employee->email)
                    ->orWhere('username', $employee->email)
                    ->first();

                if ($existing) {
                    $employee->user_id = $existing->id;
                    $employee->save();
                    DB::commit();
                    $skipped[] = "{$name} (linked to existing account)";

                    continue;
                }

                $phoneTaken = $employee->phone
                    ? User::where('phone_number', $employee->phone)->exists()
                    : true;

                $user = User::create([
                    'first_name' => $employee->first_name,
                    'middle_name' => $employee->middle_name,
                    'surname' => $employee->last_name,
                    'gender' => $employee->gender,
                    'dob' => $employee->dob,
                    'email' => $employee->email,
                    'phone_number' => $phoneTaken ? null : $employee->phone,
                    'username' => $employee->email,
                    'password' => Hash::make($password),
                    'profile_picture' => $employee->photo,
                ]);

                $employee->user_id = $user->id;
                $employee->save();
                DB::commit();
                $created++;

                // Assign role after commit (mirror Addstaff) to avoid FK timing issues.
                if ($defaultRole && ! $user->hasRole($defaultRole)) {
                    $user->assignRole($defaultRole);
                }

                // Email leg (email is required, so always attempted).
                try {
                    $user->notify(new StaffCredentials($employee->email, $password, $employee->first_name ?? 'Staff'));
                } catch (\Throwable $e) {
                    $emailFailed++;
                    Log::error('StaffCredentials email failed', ['employee' => $employee->id, 'error' => $e->getMessage()]);
                }

                // SMS leg (only when a provider is active and the staff has a phone).
                if ($hasSmsProvider && $employee->phone) {
                    $msg = "Dear {$employee->first_name},\n";
                    $msg .= "Login Details:\n";
                    $msg .= "Username: {$employee->email}\n";
                    $msg .= "Password: {$password}\n";
                    $msg .= '-HRP System Link https://hrp.stjosephhospitalmoshi.or.tz/auth/login';

                    $result = send_sms($employee->phone, $msg);
                    if (! empty($result['success'])) {
                        $smsSent++;
                    }
                }
            } catch (\Throwable $e) {
                DB::rollBack();
                $skipped[] = "{$name} (error)";
                Log::error('sendCredentialsToSelected failed', ['employee' => $employee->id, 'error' => $e->getMessage()]);
            }
        }

        $summary = [];
        if ($created) {
            $summary[] = "{$created} account(s) created";
        }
        if ($smsSent) {
            $summary[] = "{$smsSent} SMS sent";
        }
        if ($emailFailed) {
            $summary[] = "{$emailFailed} email(s) failed";
        }

        $this->dispatch('toastMagic',
            status: empty($skipped) ? 'success' : 'warning',
            title: 'Send Credentials',
            message: (count($summary) ? implode(', ', $summary).'.' : 'Nothing processed.')
                .(count($skipped) ? ' Skipped: '.implode('; ', $skipped) : '')
        );

        $this->clearSelection();
    }

    public function bulkUpdateDepartment()
    {
        if (empty($this->selectedEmployees)) {
            $this->dispatch('toastMagic', status: 'warning', title: 'No selection', message: 'Please select staff first.');

            return;
        }
        if (empty($this->bulkDepartmentId)) {
            $this->dispatch('toastMagic', status: 'warning', title: 'No department', message: 'Please choose a department.');

            return;
        }

        $count = Employee::whereIn('id', $this->selectedEmployees)
            ->update(['department_id' => $this->bulkDepartmentId]);

        $this->dispatch('toastMagic', status: 'success', title: 'Department updated', message: "Updated department for {$count} staff member(s).");
        $this->bulkDepartmentId = '';
        $this->clearSelection();
        $this->dispatch('close-bulk-dept-modal');
    }

    public function bulkUpdateDesignation()
    {
        if (empty($this->selectedEmployees)) {
            $this->dispatch('toastMagic', status: 'warning', title: 'No selection', message: 'Please select staff first.');

            return;
        }
        if (empty($this->bulkDesignationId)) {
            $this->dispatch('toastMagic', status: 'warning', title: 'No designation', message: 'Please choose a designation.');

            return;
        }

        $count = Employee::whereIn('id', $this->selectedEmployees)
            ->update(['designation_id' => $this->bulkDesignationId]);

        $this->dispatch('toastMagic', status: 'success', title: 'Designation updated', message: "Updated designation for {$count} staff member(s).");
        $this->bulkDesignationId = '';
        $this->clearSelection();
        $this->dispatch('close-bulk-desig-modal');
    }

    public function render()
    {
        $employees = $this->getEmployeesQuery()->paginate($this->perPage);

        $departments = departments::where('status', 'active')->get();
        $designations = designations::where('status', 'Active')->get();
        $workstations = workstations::all();

        return view('livewire.hr.staffs.stafflist', [
            'employees' => $employees,
            'departments' => $departments,
            'designations' => $designations,
            'workstations' => $workstations,
        ]);
    }
}
