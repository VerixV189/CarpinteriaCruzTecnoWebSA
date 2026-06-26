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
        if ($request->isMethod('GET') && !$request->ajax()) {
            $url = $request->path();
            
            // Ignorar peticiones a assets o API si las hay
            if (!str_starts_with($url, 'api/') && !str_starts_with($url, '_debugbar')) {
                $visit = PageVisit::firstOrCreate(
                    ['url' => $url],
                    ['visits' => 0]
                );
                
                $visit->increment('visits');
            }
        }

        return $next($request);
    }
}
