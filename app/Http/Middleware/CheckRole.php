<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    public function handle(Request $request, Closure $next, string $role)
    {
        if (! auth()->check() || auth()->user()->role !== $role) {
            abort(403, 'Kamu tidak punya akses ke halaman ini.');
        }

        return $next($request);
    }
}

// Daftarkan di bootstrap/app.php (Laravel 11) atau app/Http/Kernel.php (Laravel 10):
// 'role' => \App\Http\Middleware\CheckRole::class,
