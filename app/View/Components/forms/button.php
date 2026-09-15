<?php

namespace App\View\Components\forms;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class button extends Component
{
    public $type;
    public $size;
    public $color;
    public $block;
    public $rounded;
    public $outline;
    public $disabled;   
    public $icon;
    public $iconRight;
    public $iconLeft;
    public $loading;
    public $href;
    public $target;
    public $rel;
    public $method;
    public $form;
    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        $this->type = 'button';
        $this->size = 'md';
        $this->color = 'primary';
        $this->block = false;
        $this->rounded = false;
        $this->outline = false;
        $this->disabled = false;
        $this->icon = false;
        $this->iconRight = false;
        $this->iconLeft = false;
        $this->loading = false;
        $this->href = false;
        $this->target = '_self';
        $this->rel = 'noopener noreferrer';
        $this->method = 'get';
        $this->form = false;    
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.forms.button');
    }
}
