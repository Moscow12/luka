<?php

namespace App\View\Components\pages;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class profileHeader extends Component
{
    public ?string $name;
    public ?string $avatar;
    public ?string $cover;
    public ?string $email;
    public ?string $gender;
    public ?string $age;
    public ?string $editUrl;
    public bool $editable;
    public $employee;
    /**
     * Create a new component instance.
     */
      public function __construct(
        string $name = null,
        string $avatar = null,
        string $cover = null,
        string $email = null,
        string $gender = null,
        string $age = null,
        string $editUrl = null,
        bool $editable = false,
        $employee = null
    ) {
        $this->name = $name;
        $this->avatar = $avatar;
        $this->cover = $cover;
        $this->email = $email;
        $this->gender = $gender;
        $this->age = $age;
        $this->editUrl = $editUrl;
        $this->editable = $editable;
        $this->employee = $employee;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.pages.profile-header');
    }
}
