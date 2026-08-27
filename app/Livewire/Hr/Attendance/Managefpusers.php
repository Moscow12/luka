<?php

namespace App\Livewire\Hr\Attendance;

use App\Models\Employee;
use App\Models\fpusers;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use PhpOffice\PhpSpreadsheet\IOFactory;

class Managefpusers extends Component
{
    use WithFileUploads, WithPagination;

    public $excelFile;

    public $search = '';

    public $uploading = false;

    // Linking state
    public ?string $linkingUserId = null;

    public ?string $linkingUserName = null;

    public ?string $linkingDeviceId = null;

    public array $candidates = [];

    public bool $showLinkModal = false;

    // Search inside the link modal (searches all employees, not just matches).
    public string $modalSearch = '';

    // Add user modal state
    public bool $showAddModal = false;

    public string $newName = '';

    public string $newFpdeviceId = '';

    public string $newFpdeviceAddress = '';

    protected $paginationTheme = 'bootstrap';

    protected $rules = [
        'excelFile' => 'required|file|mimes:xlsx,xls,csv|max:10240',
    ];

    protected $messages = [
        'excelFile.required' => 'Please select an Excel file to upload.',
        'excelFile.mimes' => 'File must be in Excel format (xlsx, xls, csv).',
        'excelFile.max' => 'File size must not exceed 10MB.',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function uploadExcel()
    {
        $this->validate();

        try {
            $this->uploading = true;

            $path = $this->excelFile->getRealPath();
            $spreadsheet = IOFactory::load($path);
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            $imported = 0;
            $skipped = 0;

            // Skip header row (assuming first row is header)
            foreach (array_slice($rows, 1) as $row) {
                // Assuming columns: Name | FP Device ID | FP Device Address
                if (! empty($row[0]) && ! empty($row[1])) {
                    fpusers::create([
                        'name' => $row[0],
                        'fpdevice_id' => $row[1],
                        'fpdevice_address' => $row[2] ?? null,
                        'added_by' => Auth::id(),
                    ]);
                    $imported++;
                } else {
                    $skipped++;
                }
            }

            $this->dispatch('toaster', [
                'type' => 'success',
                'message' => "Successfully imported {$imported} users".($skipped > 0 ? " ({$skipped} skipped)" : ''),
            ]);

            $this->reset(['excelFile', 'uploading']);
        } catch (\Exception $e) {
            $this->dispatch('toaster', [
                'type' => 'error',
                'message' => 'Error importing file: '.$e->getMessage(),
            ]);
        } finally {
            $this->uploading = false;
        }
    }

    public function deleteUser($id)
    {
        try {
            fpusers::findOrFail($id)->delete();
            $this->dispatch('toaster', [
                'type' => 'success',
                'message' => 'User deleted successfully',
            ]);
        } catch (\Exception) {
            $this->dispatch('toaster', [
                'type' => 'error',
                'message' => 'Error deleting user',
            ]);
        }
    }

    public function openAddModal()
    {
        $this->reset(['newName', 'newFpdeviceId', 'newFpdeviceAddress']);
        $this->resetValidation();
        $this->showAddModal = true;
    }

    public function closeAddModal()
    {
        $this->reset(['newName', 'newFpdeviceId', 'newFpdeviceAddress', 'showAddModal']);
        $this->resetValidation();
    }

    public function addUser()
    {
        $validated = $this->validate([
            'newName' => 'required|string|max:255',
            'newFpdeviceId' => 'required|string|max:255|unique:fpusers,fpdevice_id',
            'newFpdeviceAddress' => 'nullable|string|max:255',
        ], [
            'newName.required' => 'Please enter the user\'s name.',
            'newFpdeviceId.required' => 'Please enter the fingerprint device ID.',
            'newFpdeviceId.unique' => 'This fingerprint device ID is already registered.',
        ]);

        fpusers::create([
            'name' => $validated['newName'],
            'fpdevice_id' => $validated['newFpdeviceId'],
            'fpdevice_address' => $validated['newFpdeviceAddress'] ?: null,
            'added_by' => Auth::id(),
        ]);

        $this->dispatch('toaster', [
            'type' => 'success',
            'message' => 'Fingerprint user added successfully',
        ]);

        $this->closeAddModal();
        $this->resetPage();
    }

    /**
     * Open the link modal for a fingerprint user and list employees whose name
     * relates to the device user's name, ranked by match strength.
     */
    public function openLink($id)
    {
        $fpuser = fpusers::findOrFail($id);

        $this->linkingUserId = $fpuser->id;
        $this->linkingUserName = $fpuser->name;
        $this->linkingDeviceId = $fpuser->fpdevice_id;
        $this->modalSearch = '';
        $this->loadCandidates();
        $this->showLinkModal = true;
    }

    public function closeLink()
    {
        $this->reset(['linkingUserId', 'linkingUserName', 'linkingDeviceId', 'candidates', 'showLinkModal', 'modalSearch']);
    }

    /**
     * Re-run the employee lookup whenever the modal search box changes.
     */
    public function updatedModalSearch()
    {
        $this->loadCandidates();
    }

    /**
     * Populate $candidates for the open fpuser: when the modal search box has a
     * term, search ALL employees by name/number; otherwise fall back to the
     * automatic name-match candidates.
     */
    protected function loadCandidates(): void
    {
        if (! $this->linkingDeviceId) {
            $this->candidates = [];

            return;
        }

        $term = trim($this->modalSearch);

        $this->candidates = $term !== ''
            ? $this->searchEmployees($term, $this->linkingDeviceId)
            : $this->findCandidates($this->linkingUserName ?? '', $this->linkingDeviceId);
    }

    /**
     * Free-text search across all employees (name, employee number) so the user
     * can link a device user even when the names don't auto-match.
     */
    protected function searchEmployees(string $term, ?string $deviceId): array
    {
        $like = '%'.$term.'%';

        $employees = Employee::query()
            ->select('id', 'first_name', 'middle_name', 'last_name', 'employee_no', 'fpid')
            ->where(function ($q) use ($like) {
                $q->where('first_name', 'like', $like)
                    ->orWhere('middle_name', 'like', $like)
                    ->orWhere('last_name', 'like', $like)
                    ->orWhere('employee_no', 'like', $like);
            })
            ->limit(15)
            ->get();

        return $employees->map(fn ($employee) => [
            'id' => $employee->id,
            'name' => $employee->getFullName(),
            'employee_no' => $employee->employee_no,
            'current_fpid' => $employee->fpid,
            'already_linked' => $employee->fpid === $deviceId,
            'shared' => 0,
            'score' => null,
        ])->all();
    }

    /**
     * Set the chosen employee's fpid to this device user's fpdevice_id.
     */
    public function updateEmployeeFpid($employeeId)
    {
        if (! $this->linkingDeviceId) {
            return;
        }

        try {
            $employee = Employee::findOrFail($employeeId);

            // Prevent the same device id being assigned to two employees.
            $taken = Employee::where('fpid', $this->linkingDeviceId)
                ->where('id', '!=', $employee->id)
                ->first();

            if ($taken) {
                $this->dispatch('toaster', [
                    'type' => 'error',
                    'message' => "Device ID {$this->linkingDeviceId} is already linked to {$taken->getFullName()}.",
                ]);

                return;
            }

            $employee->update(['fpid' => $this->linkingDeviceId]);

            $this->dispatch('toaster', [
                'type' => 'success',
                'message' => "Linked {$employee->getFullName()} to device ID {$this->linkingDeviceId}.",
            ]);

            // Keep the modal open and refresh so the row now shows as linked
            // (with an Unlink option) without re-opening.
            $this->loadCandidates();
        } catch (\Exception $e) {
            $this->dispatch('toaster', [
                'type' => 'error',
                'message' => 'Error linking employee: '.$e->getMessage(),
            ]);
        }
    }

    /**
     * Remove the link: clear the employee's fpid so the device user can be
     * linked to a different employee.
     */
    public function unlinkEmployee($employeeId)
    {
        try {
            $employee = Employee::findOrFail($employeeId);
            $name = $employee->getFullName();

            $employee->update(['fpid' => null]);

            $this->dispatch('toaster', [
                'type' => 'success',
                'message' => "Unlinked {$name} from the fingerprint device.",
            ]);

            $this->loadCandidates();
        } catch (\Exception $e) {
            $this->dispatch('toaster', [
                'type' => 'error',
                'message' => 'Error unlinking employee: '.$e->getMessage(),
            ]);
        }
    }

    /**
     * Find employees whose name relates to the device user's name.
     *
     * Names from devices are noisy ("PENDO_SHIRIMA", "AGATHON A KIMARIO"), so
     * both sides are normalised to lowercase space-separated tokens and scored
     * by how many tokens they share.
     */
    protected function findCandidates(string $deviceName, ?string $deviceId): array
    {
        $deviceTokens = $this->nameTokens($deviceName);

        if (empty($deviceTokens)) {
            return [];
        }

        $employees = Employee::query()
            ->select('id', 'first_name', 'middle_name', 'last_name', 'employee_no', 'fpid')
            ->get();

        $scored = [];

        foreach ($employees as $employee) {
            $empTokens = $this->nameTokens($employee->getFullName());

            if (empty($empTokens)) {
                continue;
            }

            $shared = count(array_intersect($deviceTokens, $empTokens));

            if ($shared === 0) {
                continue;
            }

            // Score: shared tokens, normalised by how complete the match is.
            $score = $shared / max(count($deviceTokens), count($empTokens));

            $scored[] = [
                'id' => $employee->id,
                'name' => $employee->getFullName(),
                'employee_no' => $employee->employee_no,
                'current_fpid' => $employee->fpid,
                'already_linked' => $employee->fpid === $deviceId,
                'shared' => $shared,
                'score' => round($score * 100),
            ];
        }

        // Best matches first.
        usort($scored, fn ($a, $b) => $b['score'] <=> $a['score']);

        return array_slice($scored, 0, 10);
    }

    /**
     * Normalise a name into comparable lowercase tokens, dropping single-letter
     * initials and short noise so only meaningful name parts are compared.
     */
    protected function nameTokens(?string $name): array
    {
        $name = strtolower((string) $name);
        $name = str_replace(['_', '.', '-', ','], ' ', $name);
        $name = preg_replace('/[^a-z\s]/', '', $name);

        $tokens = array_filter(
            preg_split('/\s+/', trim($name)),
            fn ($t) => strlen($t) > 1
        );

        return array_values(array_unique($tokens));
    }

    public function render()
    {
        $users = fpusers::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('fpdevice_id', 'like', '%'.$this->search.'%')
                    ->orWhere('fpdevice_address', 'like', '%'.$this->search.'%');
            })
            ->with('addedBy')
            ->latest()
            ->paginate(10);

        // Map of fpdevice_id => linked employee name, for the rows on this page.
        $deviceIds = $users->pluck('fpdevice_id')->filter()->all();
        $linked = Employee::whereIn('fpid', $deviceIds)
            ->get(['id', 'first_name', 'middle_name', 'last_name', 'fpid'])
            ->keyBy('fpid')
            ->map(fn ($e) => $e->getFullName());

        return view('livewire.hr.attendance.managefpusers', [
            'users' => $users,
            'linked' => $linked,
        ]);
    }
}
