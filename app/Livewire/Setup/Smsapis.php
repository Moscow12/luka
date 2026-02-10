<?php

namespace App\Livewire\Setup;

use App\Models\SmsApiSetting;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Smsapis extends Component
{
    use WithPagination;

    public $search = '';

    public $sms_api_id;

    public $modalMode = 'create';

    public $showModal = false;

    // Form fields
    public $provider_name;

    public $sender_id;

    public $sending_url;

    public $delivery_report_url;

    public $sender_name_url;

    public $api_key;

    public $secret_key;

    public $is_default = false;

    public $is_active = true;

    // Bulk SMS fields
    public $showBulkSmsModal = false;

    public $bulk_phone_numbers = '';

    public $bulk_message = '';

    public function openModal($mode = 'create', $id = null)
    {
        $this->resetErrorBag();
        $this->resetValidation();
        $this->modalMode = $mode;
        $this->showModal = true;

        if ($mode === 'edit' && $id) {
            $smsApi = SmsApiSetting::findOrFail($id);
            $this->sms_api_id = $id;
            $this->provider_name = $smsApi->provider_name;
            $this->sender_id = $smsApi->sender_id;
            $this->sending_url = $smsApi->sending_url;
            $this->delivery_report_url = $smsApi->delivery_report_url;
            $this->sender_name_url = $smsApi->sender_name_url;
            $this->api_key = $smsApi->api_key;
            $this->secret_key = $smsApi->secret_key;
            $this->is_default = $smsApi->is_default;
            $this->is_active = $smsApi->is_active;
        } else {
            $this->resetFormFields();
        }
    }

    public function save()
    {
        $this->validate([
            'provider_name' => ['required', 'string', 'max:255'],
            'sender_id' => ['nullable', 'string', 'max:255'],
            'sending_url' => ['required', 'url', 'max:1000'],
            'delivery_report_url' => ['nullable', 'url', 'max:1000'],
            'sender_name_url' => ['nullable', 'url', 'max:1000'],
            'api_key' => ['required', 'string'],
            'secret_key' => ['required', 'string'],
            'is_default' => ['boolean'],
            'is_active' => ['boolean'],
        ]);

        // If setting as default, unset all other defaults
        if ($this->is_default) {
            SmsApiSetting::query()->update(['is_default' => false]);
        }

        if ($this->modalMode === 'edit' && $this->sms_api_id) {
            $smsApi = SmsApiSetting::findOrFail($this->sms_api_id);
            $smsApi->update([
                'provider_name' => $this->provider_name,
                'sender_id' => $this->sender_id,
                'sending_url' => $this->sending_url,
                'delivery_report_url' => $this->delivery_report_url,
                'sender_name_url' => $this->sender_name_url,
                'api_key' => $this->api_key,
                'secret_key' => $this->secret_key,
                'is_default' => $this->is_default,
                'is_active' => $this->is_active,
            ]);
            session()->flash('success', 'SMS API Setting updated successfully!');
        } else {
            SmsApiSetting::create([
                'provider_name' => $this->provider_name,
                'sender_id' => $this->sender_id,
                'sending_url' => $this->sending_url,
                'delivery_report_url' => $this->delivery_report_url,
                'sender_name_url' => $this->sender_name_url,
                'api_key' => $this->api_key,
                'secret_key' => $this->secret_key,
                'is_default' => $this->is_default,
                'is_active' => $this->is_active,
                'added_by' => Auth::id(),
            ]);
            session()->flash('success', 'SMS API Setting added successfully!');
        }

        $this->showModal = false;
        $this->resetFormFields();
    }

    public function update()
    {
        $this->save();
    }

    public function setDefault($id)
    {
        // Unset all defaults
        SmsApiSetting::query()->update(['is_default' => false]);

        // Set this one as default
        $smsApi = SmsApiSetting::findOrFail($id);
        $smsApi->update(['is_default' => true]);

        session()->flash('success', 'Default SMS API updated successfully!');
    }

    public function toggleActive($id)
    {
        $smsApi = SmsApiSetting::findOrFail($id);
        $smsApi->update(['is_active' => ! $smsApi->is_active]);

        session()->flash('success', 'SMS API status updated successfully!');
    }

    public function delete($id)
    {
        $smsApi = SmsApiSetting::findOrFail($id);

        if ($smsApi->is_default) {
            session()->flash('error', 'Cannot delete the default SMS API setting. Please set another as default first.');

            return;
        }

        $smsApi->delete();
        session()->flash('success', 'SMS API Setting deleted successfully!');
    }

    private function resetFormFields()
    {
        $this->reset([
            'sms_api_id',
            'provider_name',
            'sender_id',
            'sending_url',
            'delivery_report_url',
            'sender_name_url',
            'api_key',
            'secret_key',
            'is_default',
            'is_active',
        ]);
        $this->is_default = false;
        $this->is_active = true;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function openBulkSmsModal()
    {
        $this->resetErrorBag();
        $this->resetValidation();
        $this->showBulkSmsModal = true;
        $this->bulk_phone_numbers = '';
        $this->bulk_message = '';
    }

    public function closeBulkSmsModal()
    {
        $this->showBulkSmsModal = false;
        $this->bulk_phone_numbers = '';
        $this->bulk_message = '';
    }

    public function sendBulkSms()
    {
        $this->validate([
            'bulk_phone_numbers' => ['required', 'string'],
            'bulk_message' => ['required', 'string', 'max:1000'],
        ]);

        // Parse phone numbers (comma or newline separated)
        $phoneNumbers = preg_split('/[\s,;]+/', $this->bulk_phone_numbers, -1, PREG_SPLIT_NO_EMPTY);

        if (empty($phoneNumbers)) {
            session()->flash('error', 'Please provide at least one phone number.');

            return;
        }

        // Send SMS using helper function
        $result = send_sms($phoneNumbers, $this->bulk_message);

        if ($result['success']) {
            session()->flash('success', 'Bulk SMS sent successfully to '.count($result['recipients']).' recipients!');
            $this->closeBulkSmsModal();
        } else {
            session()->flash('error', 'Failed to send SMS: '.$result['message']);
        }
    }

    public function render()
    {
        $smsApiSettings = SmsApiSetting::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('provider_name', 'like', '%'.$this->search.'%')
                        ->orWhere('sender_id', 'like', '%'.$this->search.'%');
                });
            })
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.setup.smsapis', [
            'smsApiSettings' => $smsApiSettings,
        ]);
    }
}
