<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        '/login',  // Exclude the login route from CSRF verification
    ];

    /**
     * The method to check if CSRF should be skipped
     * for Dusk testing.
     *
     * @return bool
     */
    protected function inExceptArray($request): bool
    {
        if (app()->environment('dusk.testing')) {
            return true; // Skip CSRF checks for Dusk testing
        }

        return parent::inExceptArray($request);
    }
}
