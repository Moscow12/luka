<?php

namespace App\Http\Controllers;

use App\Services\Auth\AccountSetupService;
use Illuminate\Http\RedirectResponse;

/**
 * Entry point for setup links. Moves the token into the session and redirects to a
 * URL without it, so the token doesn't stay in the address bar, browser history or
 * Referer headers sent by the setup page.
 */
class AccountSetupLinkController extends Controller
{
    public function __invoke(string $token): RedirectResponse
    {
        if (AccountSetupService::isWellFormed($token)) {
            session([AccountSetupService::SESSION_KEY => $token]);
        } else {
            session()->forget(AccountSetupService::SESSION_KEY);
        }

        return redirect()->route('account.setup')->header('Referrer-Policy', 'no-referrer');
    }
}
