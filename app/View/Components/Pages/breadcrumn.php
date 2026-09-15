<?php

namespace App\View\Components\Pages;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class breadcrumn extends Component
{
    public string $title;
    public  $breadcrumbs=[];

    public function __construct(string $title, array $breadcrumbs = [])
    {
        $this->title = $title;
        $this->breadcrumbs = $breadcrumbs;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.pages.breadcrumn');
    }
}
