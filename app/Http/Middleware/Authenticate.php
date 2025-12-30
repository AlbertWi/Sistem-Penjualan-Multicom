<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    protected function redirectTo($request)
    {
        if (! $request->expectsJson()) {

            // ===== E-COMMERCE (CUSTOMER) =====
            if (
                $request->is('ecom*') ||
                $request->is('cart*') ||
                $request->is('profile*') ||
                $request->is('checkout*')
            ) {
                return route('ecom.login');
            }

            // ===== DEFAULT (PEGAWAI / OFFLINE) =====
            return route('login');
        }
    }
}
