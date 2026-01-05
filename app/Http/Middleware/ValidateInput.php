<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateInput
{
    /**
     * Sanitize and validate input data.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Trim and clean input
        if ($request->isJson()) {
            $data = $request->json()->all();
            $request->merge(array_map([$this, 'cleanInput'], $data));
        } else {
            $request->merge(array_map([$this, 'cleanInput'], $request->all()));
        }

        return $next($request);
    }

    /**
     * Clean input values - remove dangerous characters, trim whitespace.
     */
    protected function cleanInput($value)
    {
        if (is_array($value)) {
            return array_map([$this, 'cleanInput'], $value);
        }

        if (is_string($value)) {
            // Remove null bytes
            $value = str_replace("\0", '', $value);
            // Trim whitespace
            $value = trim($value);
        }

        return $value;
    }
}
