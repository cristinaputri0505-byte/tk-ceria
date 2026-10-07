<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/** Header keamanan dasar untuk semua halaman. */
class HeaderKeamanan
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');          // cegah clickjacking
        $response->headers->set('X-Content-Type-Options', 'nosniff');      // cegah file dibaca sebagai tipe lain
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        return $response;
    }
}
