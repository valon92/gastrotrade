<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ndalon cache-in e faqes HTML në shfletues dhe proxy,
 * që pas deploy ndryshimet (p.sh. navbar/shporta) të shfaqen menjëherë në arontrade.net.
 */
class NoCacheForSPA
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        return $response;
    }
}
