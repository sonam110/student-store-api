<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (\Auth::user()->user_type_id == '1')
        {
            return $next($request);
        }
        return response(prepareResult(true, [], 'You are not authorized to access this page.'), config('http_response.unauthorized'));
    }
}
