<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ContentSecurityPolicy
{
    /**
     * Define o header Content-Security-Policy para restringir origens de <iframe> (frame-src).
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $allowedFrameSources = [
            "'self'",
            'https://www.youtube.com',
            'https://youtube.com',
            'https://www.youtube-nocookie.com',
            'https://youtube-nocookie.com',
            'https://www.instagram.com',
            'https://instagram.com',
            'https://www.tiktok.com',
            'https://tiktok.com',
        ];

        $cspHeader = 'frame-src ' . implode(' ', $allowedFrameSources) . ';';

        $response->headers->set('Content-Security-Policy', $cspHeader);

        return $response;
    }
}
