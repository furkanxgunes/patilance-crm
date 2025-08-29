<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetActivityCauser
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check()) {
            activity()->causedBy(auth()->user());
        }
        return $next($request);
    }
}
