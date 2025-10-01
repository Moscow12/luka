<?php

declare(strict_types=1);

namespace App\Livewire\Auth;

use Devrabiul\ToastMagic\Facades\ToastMagic;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class Logout extends Component
{
    public function logoutUser(): mixed
    {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();

        ToastMagic::success('Login out', 'Successfully logged out');

        return redirect()->route('login');
    }

    public function render(): View
    {
        return view('livewire.auth.logout');
    }
}
