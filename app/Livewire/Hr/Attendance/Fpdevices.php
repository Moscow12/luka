<?php

namespace App\Livewire\Hr\Attendance;

use App\Models\employeeattendances;
use App\Models\FpDevice;
use App\Models\fpusers;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Livewire\Component;
use Livewire\WithPagination;

class Fpdevices extends Component
{
    use WithPagination;

    // Search and Filters
    public $search = '';

    public $statusFilter = '';

    public $perPage = 10;

    // Modal States
    public $showModal = false;

    public $modalMode = 'create';

    public $editingDeviceId = null;

    // Device Form
    public $deviceForm = [
        'name' => '',
        'ip_address' => '',
        'port' => 4370,
        'location' => '',
        'description' => '',
        'is_active' => true,
    ];

    // Connection Test
    public $testResult = null;

    public $testStatus = null;

    // Pagination reset
    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->reset(['search', 'statusFilter']);
        $this->resetPage();
    }

    // Device CRUD Operations
    public function createDevice()
    {
        $this->resetErrorBag();
        $this->resetValidation();
        $this->modalMode = 'create';
        $this->editingDeviceId = null;
        $this->deviceForm = [
            'name' => '',
            'ip_address' => '',
            'port' => 4370,
            'location' => '',
            'description' => '',
            'is_active' => true,
        ];
        $this->showModal = true;
    }

    public function editDevice($deviceId)
    {
        $this->resetErrorBag();
        $this->resetValidation();
        $this->modalMode = 'edit';
        $this->editingDeviceId = $deviceId;

        $device = FpDevice::findOrFail($deviceId);
        $this->deviceForm = [
            'name' => $device->name,
            'ip_address' => $device->ip_address,
            'port' => $device->port,
            'location' => $device->location,
            'description' => $device->description,
            'is_active' => $device->is_active,
        ];
        $this->showModal = true;
    }

    public function saveDevice()
    {
        $rules = [
            'deviceForm.name' => 'required|string|max:255',
            'deviceForm.ip_address' => 'required|ip',
            'deviceForm.port' => 'required|integer|min:1|max:65535',
            'deviceForm.location' => 'nullable|string|max:255',
            'deviceForm.description' => 'nullable|string',
            'deviceForm.is_active' => 'boolean',
        ];

        $messages = [
            'deviceForm.name.required' => 'Device name is required.',
            'deviceForm.ip_address.required' => 'IP address is required.',
            'deviceForm.ip_address.ip' => 'Please enter a valid IP address.',
            'deviceForm.port.required' => 'Port number is required.',
            'deviceForm.port.integer' => 'Port must be a number.',
        ];

        $this->validate($rules, $messages);

        try {
            DB::beginTransaction();

            $data = [
                'name' => $this->deviceForm['name'],
                'ip_address' => $this->deviceForm['ip_address'],
                'port' => $this->deviceForm['port'],
                'location' => $this->deviceForm['location'],
                'description' => $this->deviceForm['description'],
                'is_active' => $this->deviceForm['is_active'],
            ];

            if ($this->modalMode === 'edit' && $this->editingDeviceId) {
                $device = FpDevice::findOrFail($this->editingDeviceId);
                $device->update($data);
                session()->flash('success', 'Device updated successfully!');
            } else {
                $data['created_by'] = Auth::id();
                $data['status'] = 'inactive';
                FpDevice::create($data);
                session()->flash('success', 'Device registered successfully!');
            }

            DB::commit();
            $this->closeModal();
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'An error occurred: '.$e->getMessage());
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->editingDeviceId = null;
        $this->resetErrorBag();
    }

    public function deleteDevice($deviceId)
    {
        try {
            $device = FpDevice::findOrFail($deviceId);

            DB::beginTransaction();
            $device->delete();
            DB::commit();

            session()->flash('success', 'Device deleted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'An error occurred: '.$e->getMessage());
        }
    }

    public function toggleActive($deviceId)
    {
        try {
            $device = FpDevice::findOrFail($deviceId);

            DB::beginTransaction();
            $device->update(['is_active' => ! $device->is_active]);
            DB::commit();

            $status = $device->is_active ? 'activated' : 'deactivated';
            session()->flash('success', "Device {$status} successfully!");
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'An error occurred: '.$e->getMessage());
        }
    }

    // Connection Test
    public function testConnection($deviceId)
    {
        $this->testResult = null;
        $this->testStatus = 'testing';

        try {
            $device = FpDevice::findOrFail($deviceId);

            // Test basic network connectivity using ping
            $pingResult = $this->pingDevice($device->ip_address);

            if ($pingResult) {
                // Try to connect to the port
                $socketResult = $this->testSocketConnection($device->ip_address, $device->port);

                if ($socketResult) {
                    // Update device status
                    $device->update([
                        'status' => 'active',
                        'last_connected_at' => now(),
                    ]);

                    $this->testStatus = 'success';
                    $this->testResult = 'Connection successful! Device is reachable and responding on port '.$device->port;
                    session()->flash('success', 'Device connection test passed!');
                } else {
                    $device->update(['status' => 'offline']);
                    $this->testStatus = 'error';
                    $this->testResult = 'Device is reachable but port '.$device->port.' is not responding. Please check if the ZKTeco service is running.';
                    session()->flash('error', 'Port connection failed!');
                }
            } else {
                $device->update(['status' => 'offline']);
                $this->testStatus = 'error';
                $this->testResult = 'Cannot reach device at '.$device->ip_address.'. Please check the IP address and network connectivity.';
                session()->flash('error', 'Device unreachable!');
            }
        } catch (\Exception $e) {
            $this->testStatus = 'error';
            $this->testResult = 'Error testing connection: '.$e->getMessage();
            session()->flash('error', 'Connection test failed: '.$e->getMessage());
        }
    }

    private function pingDevice($ip): bool
    {
        // Use system ping command
        $result = Process::timeout(5)->run("ping -c 1 -W 2 {$ip}");

        return $result->successful();
    }

    private function testSocketConnection($ip, $port): bool
    {
        $connection = @fsockopen($ip, $port, $errno, $errstr, 5);

        if ($connection) {
            fclose($connection);

            return true;
        }

        return false;
    }

    // Sync Attendance
    public function syncAttendance($deviceId)
    {
        try {
            $device = FpDevice::findOrFail($deviceId);

            // Check if device is reachable first
            if (! $this->testSocketConnection($device->ip_address, $device->port)) {
                $device->update(['status' => 'offline']);
                session()->flash('error', 'Cannot sync - device is offline!');

                return;
            }

            // Call Python script to get attendance data
            $pythonScript = base_path('public/zkteco_sync.py');
            $pythonVenv = base_path('zkteco_venv/bin/python3');

            // Check if Python script exists
            if (! file_exists($pythonScript)) {
                Log::warning('ZKTeco sync script not found');
                session()->flash('error', 'Sync script not found!');

                return;
            }

            // Use virtual environment if available, otherwise system python
            $pythonCmd = file_exists($pythonVenv) ? $pythonVenv : 'python3';
            $daysBack = 1; // Only sync last 1 day of attendance
            $result = Process::timeout(200)->run("{$pythonCmd} {$pythonScript} {$device->ip_address} {$device->port} {$daysBack} 2>&1");

            // Try to parse JSON from output (regardless of exit code)
            $outputText = $result->output();
            $output = json_decode($outputText, true);

            if ($output && isset($output['success'])) {
                if ($output['success']) {
                    // Process the sync data
                    $syncStats = $this->processSyncData($device, $output);

                    // Update device stats
                    $device->update([
                        'status' => 'active',
                        'last_sync_at' => now(),
                        'total_synced_logs' => $device->total_synced_logs + $syncStats['new_attendance'],
                    ]);

                    session()->flash('success', "Synced {$syncStats['new_attendance']} attendance records from {$syncStats['total_users']} users!");
                } else {
                    $errorMsg = $output['error'] ?? 'Unknown error during sync';
                    session()->flash('error', $errorMsg);
                }
            } else {
                // Could not parse JSON - show raw output
                $errorOutput = $outputText ?: $result->errorOutput() ?: 'Unknown error';
                session()->flash('error', 'Sync script error: '.substr($errorOutput, 0, 200));
                Log::error('ZKTeco sync failed', ['output' => $outputText, 'error' => $result->errorOutput()]);
            }
        } catch (\Illuminate\Process\Exceptions\ProcessTimedOutException $e) {
            session()->flash('error', 'Sync timed out - device has too many records. Try clearing old attendance logs from the device.');
            Log::error('ZKTeco sync timeout', ['device_id' => $deviceId]);
        } catch (\Exception $e) {
            session()->flash('error', 'Sync error: '.$e->getMessage());
            Log::error('ZKTeco sync exception', ['error' => $e->getMessage()]);
        }
    }

    // Clear attendance logs from device
    public function clearDeviceLogs($deviceId)
    {
        try {
            $device = FpDevice::findOrFail($deviceId);

            $pythonVenv = base_path('zkteco_venv/bin/python3');
            $pythonCmd = file_exists($pythonVenv) ? $pythonVenv : 'python3';

            $script = <<<PYTHON
from zk import ZK
import json

zk = ZK('{$device->ip_address}', port={$device->port}, timeout=30)
try:
    conn = zk.connect()
    if conn:
        conn.disable_device()
        conn.clear_attendance()
        conn.enable_device()
        conn.disconnect()
        print(json.dumps({"success": True}))
    else:
        print(json.dumps({"success": False, "error": "Connection failed"}))
except Exception as e:
    print(json.dumps({"success": False, "error": str(e)}))
PYTHON;

            $result = Process::timeout(60)->run("{$pythonCmd} -c '{$script}' 2>&1");
            $output = json_decode($result->output(), true);

            if ($output && $output['success']) {
                session()->flash('success', 'Attendance logs cleared from device successfully!');
            } else {
                $error = $output['error'] ?? 'Unknown error';
                session()->flash('error', 'Failed to clear logs: '.$error);
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Error clearing logs: '.$e->getMessage());
        }
    }

    /**
     * Process sync data from Python script
     * - Create new fpusers if they don't exist
     * - Insert attendance records into employeeattendances
     */
    private function processSyncData($device, $data): array
    {
        $stats = [
            'total_users' => 0,
            'new_users' => 0,
            'new_attendance' => 0,
        ];

        DB::beginTransaction();

        try {
            // Process users from device
            $users = $data['users'] ?? [];
            $stats['total_users'] = count($users);

            foreach ($users as $user) {
                $userId = (string) $user['user_id'];
                $userName = $user['name'] ?? 'User '.$userId;

                // Check if fpuser exists
                $fpuser = fpusers::where('fpdevice_id', $userId)->first();

                if (! $fpuser) {
                    // Create new fpuser
                    fpusers::create([
                        'name' => $userName,
                        'fpdevice_id' => $userId,
                        'fpdevice_address' => $device->ip_address,
                        'added_by' => Auth::id(),
                    ]);
                    $stats['new_users']++;
                } else {
                    // Update device address if changed
                    if ($fpuser->fpdevice_address !== $device->ip_address) {
                        $fpuser->update(['fpdevice_address' => $device->ip_address]);
                    }
                }
            }

            // Process attendance records
            $attendances = $data['attendance'] ?? [];

            foreach ($attendances as $att) {
                $userId = (string) $att['user_id'];
                $timestamp = $att['timestamp'];

                // Parse the timestamp
                $punchTime = Carbon::parse($timestamp);
                $clockDate = $punchTime->format('Y-m-d');
                $clockTime = $punchTime->format('H:i:s');

                // Check if this attendance record already exists
                $exists = employeeattendances::where('fpuser_id', $userId)
                    ->where('clocktimestamp', $timestamp)
                    ->exists();

                if (! $exists) {
                    // Determine clock status (check_in or check_out based on time)
                    $hour = (int) $punchTime->format('H');
                    $clockStatus = $hour < 12 ? 'check_in' : 'check_out';

                    // Insert new attendance record
                    employeeattendances::create([
                        'fpuser_id' => $userId,
                        'clockdate' => $clockDate,
                        'device_id' => $device->id,
                        'clocktimestamp' => $timestamp,
                        'clocktime' => $clockTime,
                        'clock_status' => $clockStatus,
                        'status' => 'synced',
                        'clock_in' => $clockStatus === 'check_in' ? $clockTime : null,
                        'clock_out' => $clockStatus === 'check_out' ? $clockTime : null,
                    ]);
                    $stats['new_attendance']++;
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error processing sync data', ['error' => $e->getMessage()]);
            throw $e;
        }

        return $stats;
    }

    public function clearTestResult()
    {
        $this->testResult = null;
        $this->testStatus = null;
    }

    public function render()
    {
        $devicesQuery = FpDevice::query()
            ->with('creator');

        // Apply search filter
        if ($this->search) {
            $devicesQuery->where(function ($query) {
                $query->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('ip_address', 'like', '%'.$this->search.'%')
                    ->orWhere('location', 'like', '%'.$this->search.'%');
            });
        }

        // Apply status filter
        if ($this->statusFilter) {
            $devicesQuery->where('status', $this->statusFilter);
        }

        $devicesQuery->orderBy('created_at', 'desc');

        $devices = $devicesQuery->paginate($this->perPage);

        // Statistics
        $totalDevices = FpDevice::count();
        $activeDevices = FpDevice::where('status', 'active')->count();
        $offlineDevices = FpDevice::where('status', 'offline')->count();
        $totalSyncedLogs = FpDevice::sum('total_synced_logs');

        return view('livewire.hr.attendance.fpdevices', [
            'devices' => $devices,
            'totalDevices' => $totalDevices,
            'activeDevices' => $activeDevices,
            'offlineDevices' => $offlineDevices,
            'totalSyncedLogs' => $totalSyncedLogs,
        ]);
    }
}
