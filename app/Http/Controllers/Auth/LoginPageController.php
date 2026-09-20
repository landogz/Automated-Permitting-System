<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class LoginPageController extends Controller
{
    /**
     * Show the Velzon sign-in page with optional local quick-login accounts.
     */
    public function __invoke(): View
    {
        $quickLoginEnabled = (bool) config('apics_demo_users.enabled');
        $quickUsers = [];
        if ($quickLoginEnabled) {
            foreach (config('apics_demo_users.users', []) as $demo) {
                if (! is_array($demo)) {
                    continue;
                }
                // Never expose demo passwords in the HTML DOM.
                $quickUsers[] = [
                    'label' => (string) ($demo['label'] ?? ''),
                    'email' => (string) ($demo['email'] ?? ''),
                    'redirect' => (string) ($demo['redirect'] ?? '/admin'),
                    'role' => (string) ($demo['role'] ?? ''),
                ];
            }
        }

        return view('auth.login', [
            'quickLoginEnabled' => $quickLoginEnabled,
            'quickUsers' => $quickUsers,
        ]);
    }
}
