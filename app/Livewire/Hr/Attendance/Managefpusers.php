<?php

namespace App\Livewire\Hr\Attendance;

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

    public function render()
    {
        $users = fpusers::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('fpdevice_id', 'like', '%'.$this->search.'%')
                    ->orWhere('fpdevice_address', 'like', '%'.$this->search.'%');
            })
            ->with('added_by')
            ->latest()
            ->paginate(10);

        return view('livewire.hr.attendance.managefpusers', [
            'users' => $users,
        ]);
    }
}
