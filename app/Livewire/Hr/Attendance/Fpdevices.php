<?php

namespace App\Livewire\Hr\Attendance;

use App\Models\employeeattendances;
use App\Models\FpDevice;
use App\Models\fpusers;
use App\Services\Attendance\AttendanceBridgeClient;
use App\Services\Attendance\AttendanceBridgeException;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

class Fpdevices extends Component
{
    use WithPagination;

    public const MANAGE_PERMISSION = 'manage-fp-devices';

    // Search and Filters
    public $search = '';

    public $statusFilter = '';

    public $perPage = 10;

    // Modal States
    public $showModal = false;

    #[Locked]
    public $modalMode = 'create';

    #[Locked]
    public $editingDeviceId = null;

    /** Whether the device being edited already has a stored password (the password itself is never sent to the browser). */
    #[Locked]
    public $hasStoredPassword = false;

    // Device Form
    public $deviceForm = [
        'name' => '',
        'device_type' => 'zkteco',
        'ip_address' => '',
        'port' => 4370,
        'username' => '',
        'password' => '',
        'location' => '',
        'description' => '',
        'is_active' => true,
    ];

    // Connection Test
    public $testResult = null;

    public $testStatus = null;

    /**
     * Runs on every Livewire request (initial render and every action call),
     * so no public method is reachable without the permission.
     */
    public function boot(): void
    {
        abort_unless(Auth::user()?->can(self::MANAGE_PERMISSION), 403);
    }

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
        $this->hasStoredPassword = false;
        $this->deviceForm = [
            'name' => '',
            'device_type' => 'zkteco',
            'ip_address' => '',
            'port' => 4370,
            'username' => '',
            'password' => '',
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

        $device = FpDevice::findOrFail($deviceId);

        $this->modalMode = 'edit';
        $this->editingDeviceId = $device->id;
        $this->hasStoredPassword = filled($device->password);
        $this->deviceForm = [
            'name' => $device->name,
            'device_type' => $device->device_type ?? 'zkteco',
            'ip_address' => $device->ip_address,
            'port' => $device->port ?? 4370,
            'username' => $device->username ?? '',
            'password' => '', // write-only: leave blank to keep the stored password
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
            'deviceForm.device_type' => 'required|in:zkteco,anviz',
            'deviceForm.ip_address' => 'required|ip',
            'deviceForm.port' => 'nullable|integer|min:1|max:65535|required_if:deviceForm.device_type,zkteco',
            'deviceForm.username' => 'nullable|string|max:255',
            'deviceForm.password' => 'nullable|string|max:255',
            'deviceForm.location' => 'nullable|string|max:255',
            'deviceForm.description' => 'nullable|string',
            'deviceForm.is_active' => 'boolean',
        ];

        $messages = [
            'deviceForm.name.required' => 'Device name is required.',
            'deviceForm.device_type.required' => 'Device type is required.',
            'deviceForm.device_type.in' => 'Invalid device type selected.',
            'deviceForm.ip_address.required' => 'IP address is required.',
            'deviceForm.ip_address.ip' => 'Please enter a valid IP address.',
            'deviceForm.port.required_if' => 'Port number is required for ZKTeco devices.',
            'deviceForm.port.integer' => 'Port must be a number.',
        ];

        $this->validate($rules, $messages);

        try {
            DB::beginTransaction();

            $data = [
                'name' => $this->deviceForm['name'],
                'device_type' => $this->deviceForm['device_type'],
                'ip_address' => $this->deviceForm['ip_address'],
                'port' => $this->deviceForm['device_type'] === 'zkteco' ? $this->deviceForm['port'] : null,
                'username' => $this->deviceForm['username'],
                'location' => $this->deviceForm['location'],
                'description' => $this->deviceForm['description'],
                'is_active' => $this->deviceForm['is_active'],
            ];

            // Blank password on edit keeps the stored one
            if (filled($this->deviceForm['password'])) {
                $data['password'] = $this->deviceForm['password'];
            }

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
            Log::error('FP device save failed', ['error' => $e->getMessage()]);
            session()->flash('error', 'An error occurred while saving the device.');
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->editingDeviceId = null;
        $this->hasStoredPassword = false;
        $this->deviceForm['password'] = '';
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
                // For Anviz devices, ping is sufficient since they don't use port connection
                if ($device->device_type === 'anviz') {
                    $device->update([
                        'status' => 'active',
                        'last_connected_at' => now(),
                    ]);

                    $this->testStatus = 'success';
                    $this->testResult = 'Connection successful! Anviz device is reachable at '.$device->ip_address;
                    session()->flash('success', 'Device connection test passed!');
                } else {
                    // For ZKTeco devices, try to connect to the port
                    $socketResult = $this->testSocketConnection($device->ip_address, $device->port);

                    if ($socketResult) {
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

    private function pingDevice(?string $ip): bool
    {
        if (! filter_var($ip, FILTER_VALIDATE_IP)) {
            return false;
        }

        // Array form: executed directly, never through a shell
        $result = Process::timeout(5)->run(['ping', '-c', '1', '-W', '2', $ip]);

        return $result->successful();
    }

    private function testSocketConnection($ip, $port): bool
    {
        if (! filter_var($ip, FILTER_VALIDATE_IP) || ! $port) {
            return false;
        }

        $connection = @fsockopen($ip, (int) $port, $errno, $errstr, 5);

        if ($connection) {
            fclose($connection);

            return true;
        }

        return false;
    }

    // Sync Attendance (via the external attendance service API)
    public function syncAttendance($deviceId)
    {
        $bridge = app(AttendanceBridgeClient::class);

        try {
            $device = FpDevice::findOrFail($deviceId);

            $data = $bridge->sync($device, daysBack: 1);
            $syncStats = $this->processSyncData($device, $data);

            $device->update([
                'status' => 'active',
                'last_sync_at' => now(),
                'total_synced_logs' => $device->total_synced_logs + $syncStats['new_attendance'],
            ]);

            session()->flash('success', "Synced {$syncStats['new_attendance']} attendance records from {$syncStats['total_users']} users!");
        } catch (AttendanceBridgeException $e) {
            session()->flash('error', 'Sync failed: '.$e->getMessage());
        } catch (\Exception $e) {
            Log::error('FP device sync exception', ['device_id' => $deviceId, 'error' => $e->getMessage()]);
            session()->flash('error', 'Sync error: '.$e->getMessage());
        }
    }

    // Clear attendance logs from device (via the external attendance service API)
    public function clearDeviceLogs($deviceId)
    {
        $bridge = app(AttendanceBridgeClient::class);

        try {
            $device = FpDevice::findOrFail($deviceId);

            $bridge->clearLogs($device);

            Log::info('FP device logs cleared', ['device_id' => $device->id, 'user_id' => Auth::id()]);
            session()->flash('success', 'Attendance logs cleared from device successfully!');
        } catch (AttendanceBridgeException $e) {
            session()->flash('error', 'Failed to clear logs: '.$e->getMessage());
        } catch (\Exception $e) {
            session()->flash('error', 'Error clearing logs: '.$e->getMessage());
        }
    }

    /**
     * Process sync data returned by the attendance service
     * - Create new fpusers if they don't exist
     * - Insert attendance records into employeeattendances
     */
    private function processSyncData($device, array $data): array
    {
        $stats = [
            'total_users' => 0,
            'new_users' => 0,
            'new_attendance' => 0,
        ];

        DB::beginTransaction();

        try {
            // Process users from device
            $users = array_filter($data['users'] ?? [], fn ($u) => is_array($u) && is_scalar($u['user_id'] ?? null));
            $stats['total_users'] = count($users);

            foreach ($users as $user) {
                $userId = (string) $user['user_id'];
                $userName = is_string($user['name'] ?? null) && $user['name'] !== '' ? mb_substr($user['name'], 0, 255) : 'User '.$userId;

                $fpuser = fpusers::where('fpdevice_id', $userId)->first();

                if (! $fpuser) {
                    fpusers::create([
                        'name' => $userName,
                        'fpdevice_id' => $userId,
                        'fpdevice_address' => $device->ip_address,
                        'added_by' => Auth::id(),
                    ]);
                    $stats['new_users']++;
                } elseif ($fpuser->fpdevice_address !== $device->ip_address) {
                    $fpuser->update(['fpdevice_address' => $device->ip_address]);
                }
            }

            // Process attendance records
            foreach ($data['attendance'] ?? [] as $att) {
                if (! is_array($att) || ! is_scalar($att['user_id'] ?? null) || ! is_string($att['timestamp'] ?? null)) {
                    continue;
                }

                try {
                    $punchTime = Carbon::createFromFormat('Y-m-d H:i:s', $att['timestamp']);
                } catch (\Throwable) {
                    continue;
                }

                $userId = (string) $att['user_id'];
                $timestamp = $punchTime->format('Y-m-d H:i:s');
                $clockDate = $punchTime->format('Y-m-d');
                $clockTime = $punchTime->format('H:i:s');

                $exists = employeeattendances::where('fpuser_id', $userId)
                    ->where('clocktimestamp', $timestamp)
                    ->exists();

                if (! $exists) {
                    // Determine clock status (check_in or check_out based on time)
                    $clockStatus = (int) $punchTime->format('H') < 12 ? 'check_in' : 'check_out';

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

    public function diagnoseAnvizDevice($deviceId)
    {
        $bridge = app(AttendanceBridgeClient::class);

        try {
            $device = FpDevice::findOrFail($deviceId);

            if ($device->device_type !== 'anviz') {
                session()->flash('error', 'Diagnostic is only for Anviz devices!');

                return;
            }

            $report = $bridge->diagnose($device);

            Log::info('Anviz diagnostic output', ['device' => $device->name, 'output' => $report]);

            session()->flash('success', 'Diagnostic completed! Check logs for details or contact support with this info.');
            $this->testResult = "Diagnostic scan completed. Output logged for review.\n\n".mb_substr($report, 0, 500);
            $this->testStatus = 'success';
        } catch (AttendanceBridgeException $e) {
            session()->flash('error', 'Diagnostic failed: '.$e->getMessage());
        } catch (\Exception $e) {
            session()->flash('error', 'Diagnostic failed: '.$e->getMessage());
        }
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
