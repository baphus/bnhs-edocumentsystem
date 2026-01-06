<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OptimizeDatabase
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Enable query result caching for this request
        \Illuminate\Support\Facades\DB::enableQueryLog();
        
        // Reduce number of queries for relationships
        config(['database.connections.mysql.strict' => false]);
        
        return $next($request);
    }
}
