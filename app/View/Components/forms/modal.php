<?php

namespace App\View\Components\forms;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class modal extends Component
{
    public $id;
    public $title;
    public $size;
    public $centered;
    public $footer;
    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        $this->id = 'add-edit-event-modal';
        $this->title = 'Add Event';
        $this->size = 'modal-lg';
        $this->centered = true;
        $this->footer = '<button type="button" class="btn btn-secondary" wire:click="$set(\'showModal\', false)">close</button>
        <button type="submit" class="btn btn-primary">
            {{ $modalMode === \'edit\' ? \'Update\' : \'Save\' }}
        </button>';
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.forms.modal');
    }
}
