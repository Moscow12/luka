<?php

namespace App\Livewire\Setup\Location;

use App\Services\TanzaniaGeodataSync;
use Livewire\Component;
use Throwable;

class Index extends Component
{
    public bool $syncing = false;

    /**
     * Import / refresh the Tanzania geo-location hierarchy from the bundled
     * NBS dataset. Idempotent: existing locations are updated, missing ones
     * are created, nothing is duplicated.
     */
    public function syncGeodata(TanzaniaGeodataSync $sync)
    {
        $this->syncing = true;

        // The dataset is large (~16k streets); give the request room on
        // servers with conservative PHP-FPM limits.
        @set_time_limit(300);
        @ini_set('memory_limit', '512M');

        try {
            $stats = $sync->sync();
        } catch (Throwable $e) {
            $this->syncing = false;
            session()->flash('error', 'Geodata sync failed: '.$e->getMessage());

            return;
        }

        $this->syncing = false;

        $created = $stats['regions_created'] + $stats['districts_created']
            + $stats['wards_created'] + $stats['streets_created'];
        $updated = $stats['regions_matched'] + $stats['districts_matched']
            + $stats['wards_matched'] + $stats['streets_matched'];

        // Refresh the child location tabs so newly imported rows appear.
        $this->dispatch('geodata-synced');

        session()->flash('success', sprintf(
            'Geodata synced: %d new (%dR / %dD / %dW / %dS), %d existing refreshed.',
            $created,
            $stats['regions_created'],
            $stats['districts_created'],
            $stats['wards_created'],
            $stats['streets_created'],
            $updated,
        ));
    }

    public function render()
    {
        return view('livewire.setup.location.index');
    }
}
