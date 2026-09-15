<?php

namespace App\Livewire\Hr\Attendance;

use App\Models\employeeattendances;
use App\Models\fpusers;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;
use PhpOffice\PhpSpreadsheet\IOFactory;

class Checkindata extends Component
{
    use WithFileUploads;

    public $file;

    public bool $showPreview = false;

    /**
     * Normalized parsed rows kept across the Livewire round-trip for the
     * confirm step. Each entry:
     * ['fpuser_id','name','timestamp','clockdate','clocktime','known','error'].
     */
    public array $rows = [];

    public array $sample = [];

    public int $totalRows = 0;

    public int $validRows = 0;

    public int $invalidRows = 0;

    public int $unknownUsers = 0;

    public bool $isImporting = false;

    public int $importedCount = 0;

    protected array $rules = [
        'file' => 'required|file|mimes:xlsx,csv,txt|max:10240',
    ];

    protected array $messages = [
        'file.required' => 'Please choose a file to upload.',
        'file.mimes' => 'File must be a CSV or Excel (.xlsx) file. Export the device report as CSV first.',
        'file.max' => 'File size must not exceed 10MB.',
    ];

    /**
     * Parse the uploaded file and build a preview without writing anything.
     */
    public function parseFile(): void
    {
        $this->validate();

        $this->reset(['rows', 'sample', 'totalRows', 'validRows', 'invalidRows', 'unknownUsers', 'importedCount', 'showPreview']);

        try {
            $raw = $this->rowsToArray($this->file->getRealPath());

            if (empty($raw)) {
                $this->dispatch('toaster', [
                    'type' => 'error',
                    'message' => 'The file appears to be empty.',
                ]);

                return;
            }

            // Resolve the column positions for "No." and "Date/Time" from the
            // header row, falling back to the device export's default layout
            // (Name | No. | Date/Time) when no recognisable header is present.
            [$noIndex, $dateIndex, $nameIndex, $hasHeader] = $this->resolveColumns($raw[0]);

            $dataRows = $hasHeader ? array_slice($raw, 1) : $raw;

            $knownIds = fpusers::pluck('fpdevice_id')
                ->map(fn ($id) => (string) $id)
                ->flip();

            foreach ($dataRows as $row) {
                $no = isset($row[$noIndex]) ? trim((string) $row[$noIndex]) : '';
                $datetime = isset($row[$dateIndex]) ? trim((string) $row[$dateIndex]) : '';
                $name = $nameIndex !== null && isset($row[$nameIndex]) ? trim((string) $row[$nameIndex]) : '';

                // Skip completely empty rows.
                if ($no === '' && $datetime === '') {
                    continue;
                }

                $this->totalRows++;

                $entry = [
                    'fpuser_id' => $no,
                    'name' => $name,
                    'timestamp' => $datetime,
                    'clockdate' => null,
                    'clocktime' => null,
                    'known' => false,
                    'error' => null,
                ];

                if ($no === '') {
                    $entry['error'] = 'Missing user number (No.)';
                } elseif ($datetime === '') {
                    $entry['error'] = 'Missing date/time';
                } else {
                    try {
                        $punch = Carbon::parse($datetime);
                        $entry['clockdate'] = $punch->format('Y-m-d');
                        $entry['clocktime'] = $punch->format('H:i:s');
                        $entry['timestamp'] = $punch->format('Y-m-d H:i:s');
                    } catch (\Throwable) {
                        $entry['error'] = 'Unrecognised date/time: '.$datetime;
                    }
                }

                if ($entry['error'] === null) {
                    $entry['known'] = $knownIds->has($no);

                    if (! $entry['known']) {
                        $this->unknownUsers++;
                    }

                    $this->validRows++;
                } else {
                    $this->invalidRows++;
                }

                $this->rows[] = $entry;
            }

            if ($this->totalRows === 0) {
                $this->dispatch('toaster', [
                    'type' => 'error',
                    'message' => 'No data rows were found in the file.',
                ]);

                return;
            }

            $this->sample = array_slice($this->rows, 0, 20);
            $this->showPreview = true;
        } catch (\Throwable $e) {
            $this->dispatch('toaster', [
                'type' => 'error',
                'message' => 'Could not read the file: '.$e->getMessage(),
            ]);
        }
    }

    /**
     * Persist the previewed punches into employeeattendances, skipping rows
     * with errors and timestamps that already exist for the same user.
     */
    public function import(): void
    {
        if (! $this->showPreview || empty($this->rows)) {
            return;
        }

        $this->isImporting = true;

        try {
            // Valid rows only.
            $valid = array_values(array_filter($this->rows, fn ($r) => $r['error'] === null));

            // Build a set of existing (fpuser_id|clocktimestamp) pairs across the
            // dates present in this file so we can dedupe in memory rather than
            // querying per row.
            $userIds = array_values(array_unique(array_map(fn ($r) => $r['fpuser_id'], $valid)));
            $dates = array_values(array_unique(array_map(fn ($r) => $r['clockdate'], $valid)));

            $existing = employeeattendances::whereIn('fpuser_id', $userIds)
                ->whereIn('clockdate', $dates)
                ->get(['fpuser_id', 'clocktimestamp'])
                ->map(fn ($r) => $r->fpuser_id.'|'.$r->clocktimestamp)
                ->flip();

            $seen = [];
            $insertRows = [];
            $duplicates = 0;
            $now = now();

            foreach ($valid as $r) {
                $key = $r['fpuser_id'].'|'.$r['timestamp'];

                // Skip rows already in the DB or duplicated within this file.
                if ($existing->has($key) || isset($seen[$key])) {
                    $duplicates++;

                    continue;
                }

                $seen[$key] = true;

                $insertRows[] = [
                    'id' => (string) Str::uuid(),
                    'fpuser_id' => $r['fpuser_id'],
                    'clocktimestamp' => $r['timestamp'],
                    'clockdate' => $r['clockdate'],
                    'clocktime' => $r['clocktime'],
                    'status' => 'imported',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            DB::transaction(function () use ($insertRows) {
                foreach (array_chunk($insertRows, 500) as $chunk) {
                    employeeattendances::insert($chunk);
                }
            });

            $this->importedCount = count($insertRows);
            $unknown = $this->unknownUsers;

            $message = "Imported {$this->importedCount} attendance record(s)";
            if ($duplicates > 0) {
                $message .= ", skipped {$duplicates} duplicate(s)";
            }
            if ($unknown > 0) {
                $message .= ". {$unknown} record(s) belong to unknown device users";
            }

            $this->reset(['file', 'rows', 'sample', 'showPreview', 'totalRows', 'validRows', 'invalidRows', 'unknownUsers']);

            $this->dispatch('toaster', [
                'type' => 'success',
                'message' => $message,
            ]);
        } catch (\Throwable $e) {
            $this->dispatch('toaster', [
                'type' => 'error',
                'message' => 'Import failed: '.$e->getMessage(),
            ]);
        } finally {
            $this->isImporting = false;
        }
    }

    public function cancel(): void
    {
        $this->reset(['file', 'rows', 'sample', 'showPreview', 'totalRows', 'validRows', 'invalidRows', 'unknownUsers', 'importedCount']);
    }

    /**
     * Determine the column indexes for No. and Date/Time. Returns
     * [noIndex, dateIndex, nameIndex|null, hasHeader].
     */
    protected function resolveColumns(array $firstRow): array
    {
        $lower = array_map(fn ($c) => strtolower(trim((string) $c)), $firstRow);

        $noIndex = null;
        $dateIndex = null;
        $nameIndex = null;

        foreach ($lower as $i => $value) {
            if ($value === 'no.' || $value === 'no' || str_contains($value, 'user')) {
                $noIndex ??= $i;
            } elseif (str_contains($value, 'date') || str_contains($value, 'time')) {
                $dateIndex ??= $i;
            } elseif (str_contains($value, 'name')) {
                $nameIndex ??= $i;
            }
        }

        $hasHeader = $noIndex !== null && $dateIndex !== null;

        // Fall back to the device export layout: Name | No. | Date/Time.
        if (! $hasHeader) {
            return [1, 2, 0, false];
        }

        return [$noIndex, $dateIndex, $nameIndex, true];
    }

    /**
     * Read a spreadsheet/CSV into a plain array of rows.
     *
     * Only formats PhpSpreadsheet can read natively (.xlsx, .csv) are
     * supported. Legacy fingerprint-device .xls exports are non-standard BIFF
     * streams that PhpSpreadsheet cannot open — users must export those as CSV
     * (or "Save As" .xlsx) before uploading.
     */
    protected function rowsToArray(string $path): array
    {
        try {
            return IOFactory::load($path)->getActiveSheet()->toArray();
        } catch (\Throwable) {
            throw new \RuntimeException(
                'This file could not be read. Please upload a .csv or .xlsx file '
                .'(open the device report and export/save it as CSV first).'
            );
        }
    }

    public function render()
    {
        return view('livewire.hr.attendance.checkindata');
    }
}
