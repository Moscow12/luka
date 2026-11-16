<?php

namespace App\Livewire\Concerns;

use App\Support\RecordUsage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;

/**
 * Master records already referenced elsewhere are deactivated, never deleted.
 * Used with the <x-record-status-actions> buttons and the HasActiveStatus model trait.
 */
trait ManagesRecordStatus
{
    /** Deletes only when nothing references the record; returns whether it was deleted. */
    protected function deleteUnlessInUse(Model $record, string $label): bool
    {
        if (RecordUsage::isInUse($record)) {
            session()->flash('error', "{$label} is already used in other records and cannot be deleted. Deactivate it instead.");

            return false;
        }

        try {
            $record->delete();
        } catch (QueryException) {
            session()->flash('error', "{$label} is still in use and cannot be deleted. Deactivate it instead.");

            return false;
        }

        session()->flash('success', "{$label} deleted.");

        return true;
    }

    /** Activates an inactive record or deactivates an active one. */
    protected function toggleRecordStatus(Model $record, string $label): void
    {
        $record->toggleStatus();

        session()->flash('success', $record->isActive() ? "{$label} activated." : "{$label} deactivated. It no longer appears in lists for new records.");
    }
}
