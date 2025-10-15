<?php

namespace App\View\Components\forms;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Input extends Component
{
    public $name;
    public $type;
    public $label;
    public $id;
    public $value;
    public $required;
    public $placeholder;
    public $class;
    public $readonly;
    public $disabled;
    public $autofocus;
    public $autocomplete;
    public $list;
    public $max;
    public $min;
    public $step;
    public $maxlength;
    public $pattern;
    public $form;
    public $size;
    public $width;
    public $height;
    public $tabindex;
    public $accept;
    public $multiple;
    public $capture;
    public $autosave;
    public $enctype;
    public $formaction;

    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        $this->type = 'text';
        $this->label = 'Name';
        $this->id = null;
        $this->value = old($this->name);
        $this->required = false;
        $this->placeholder = null;
        $this->class = null;
        $this->readonly = false;
        $this->disabled = false;
        $this->autofocus = false;
        $this->autocomplete = null;
        $this->list = null;
        $this->max = null;
        $this->min = null;
        $this->step = null;
        $this->maxlength = null;
        $this->pattern = null;
        $this->form = null;
        $this->size = null;
        $this->width = null;
        $this->height = null;
        $this->tabindex = null;
        $this->accept = null;
        $this->multiple = false;
        $this->capture = false;
        $this->autosave = null;
        $this->enctype = null;
        $this->formaction = null;


    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.forms.input');
    }
}
