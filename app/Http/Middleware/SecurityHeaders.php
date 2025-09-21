<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Add security headers
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');
        
        // Content Security Policy (environment-specific)
        $isDev = app()->environment('local', 'development');
        
        $csp = [
            "default-src 'self'",
            $isDev ? "script-src 'self' 'unsafe-inline' 'unsafe-eval' cdn.jsdelivr.net" : "script-src 'self' cdn.jsdelivr.net",
            $isDev ? "style-src 'self' 'unsafe-inline' fonts.googleapis.com cdn.jsdelivr.net" : "style-src 'self' fonts.googleapis.com cdn.jsdelivr.net",
            "font-src 'self' fonts.gstatic.com",
            "img-src 'self' data: blob:",
            "connect-src 'self'",
            "frame-ancestors 'none'",
        ];
        
        $response->headers->set('Content-Security-Policy', implode('; ', $csp));
        
        // HSTS for HTTPS (only in production)
        if (app()->environment('production') && $request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        }

        return $response;
    }
}