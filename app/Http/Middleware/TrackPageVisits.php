<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\PageVisit;

class TrackPageVisits
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Inertia uses XHR but we still want to track page visits
        // So we only ignore if it's an API route or debugbar, but allow web GET requests
        if ($request->isMethod('GET')) {
            $url = $request->path();
            
            // Ignore asset requests, API routes, or debugbar
            if (!str_starts_with($url, 'api/') && !str_starts_with($url, '_debugbar') && !str_starts_with($url, 'build/')) {
                // If it's an Inertia partial reload, we shouldn't increment
                // Inertia sets X-Inertia-Partial-Data header for partials
                if (!$request->hasHeader('X-Inertia-Partial-Data')) {
                    $visit = PageVisit::firstOrCreate(
                        ['url' => $url],
                        ['visits' => 0]
                    );
                    
                    $visit->increment('visits');
                }
            }
        }

        return $next($request);
    }
}
