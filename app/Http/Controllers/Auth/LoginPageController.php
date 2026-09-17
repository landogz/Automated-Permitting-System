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
        $quickUsers = $quickLoginEnabled
            ? array_values(config('apics_demo_users.users', []))
            : [];

        return view('auth.login', [
            'quickLoginEnabled' => $quickLoginEnabled,
            'quickUsers' => $quickUsers,
        ]);
    }
}
