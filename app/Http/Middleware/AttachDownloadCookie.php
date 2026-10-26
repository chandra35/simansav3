<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;

class AttachDownloadCookie
{
    /**
     * Handle an incoming request.
     * Attach a readable client-side cookie when the response is a file download,
     * allowing JavaScript to automatically dismiss loading overlays.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $disposition = $response->headers->get('Content-Disposition');
        if ($disposition && (str_contains($disposition, 'attachment') || str_contains($disposition, 'filename='))) {
            $token = (string) $request->input('simansa_download_token', $request->header('X-Download-Token', time()));
            if ($token === '') {
                $token = (string) time();
            }

            // Set cookie readable by client JavaScript (httpOnly = false)
            $response->headers->setCookie(
                new Cookie(
                    'simansa_download_token',
                    $token,
                    time() + 120,
                    '/',
                    null,
                    false, // secure (works on both http local dev and https production)
                    false, // httpOnly: false so document.cookie can read it
                    false, // raw
                    'Lax'  // sameSite
                )
            );
        }

        return $response;
    }
}
