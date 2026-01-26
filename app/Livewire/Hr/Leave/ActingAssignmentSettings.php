<?php

namespace App\Livewire\Hr\Leave;

use App\Models\designations;
use App\Models\LeaveSetting;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ActingAssignmentSettings extends Component
{
    public $acting_assignment_enabled;
    public $acting_assignment_mandatory;
    public $acting_assignment_min_days;
    public $acting_assignment_auto_approve;
    public $acting_assignment_notify_employee;
    public $acting_assignment_designations = [];
    public $allDesignations = [];

    public function mount()
    {
        // Load current settings
        $this->acting_assignment_enabled = LeaveSetting::get('acting_assignment_enabled', true);
        $this->acting_assignment_mandatory = LeaveSetting::get('acting_assignment_mandatory', false);
        $this->acting_assignment_min_days = LeaveSetting::get('acting_assignment_min_days', 5);
        $this->acting_assignment_auto_approve = LeaveSetting::get('acting_assignment_auto_approve', false);
        $this->acting_assignment_notify_employee = LeaveSetting::get('acting_assignment_notify_employee', true);
        $this->acting_assignment_designations = LeaveSetting::get('acting_assignment_designations', []);

        // Load all designations
        $this->allDesignations = designations::where('status', 'active')->get();
    }

    public function save()
    {
        $this->validate([
            'acting_assignment_min_days' => 'required|integer|min:1|max:365',
        ]);

        // Save settings
        LeaveSetting::set('acting_assignment_enabled', $this->acting_assignment_enabled);
        LeaveSetting::set('acting_assignment_mandatory', $this->acting_assignment_mandatory);
        LeaveSetting::set('acting_assignment_min_days', (int) $this->acting_assignment_min_days);
        LeaveSetting::set('acting_assignment_auto_approve', $this->acting_assignment_auto_approve);
        LeaveSetting::set('acting_assignment_notify_employee', $this->acting_assignment_notify_employee);
        LeaveSetting::set('acting_assignment_designations', $this->acting_assignment_designations ?? []);

        session()->flash('success', 'Acting assignment settings updated successfully!');
    }

    public function resetToDefaults()
    {
        $this->acting_assignment_enabled = true;
        $this->acting_assignment_mandatory = false;
        $this->acting_assignment_min_days = 5;
        $this->acting_assignment_auto_approve = false;
        $this->acting_assignment_notify_employee = true;
        $this->acting_assignment_designations = [];

        session()->flash('info', 'Settings reset to defaults. Click Save to apply changes.');
    }

    public function render()
    {
        return view('livewire.hr.leave.acting-assignment-settings');
    }
}
