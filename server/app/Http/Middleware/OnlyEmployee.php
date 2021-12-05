<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class OnlyEmployee
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
        $user = auth()->user();

        if ($user->is_owner) {
            $state = 'fail';
            $errors = 'Permission denied';
            return response()->json(compact('state', 'errors'), 403);
        }

        return $next($request);
    }
}
