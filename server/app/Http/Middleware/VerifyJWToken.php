<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class VerifyJWToken
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
        if(!auth()->check()){
            $state = "jwt_error";
            $token_error = "JWT invalid or missing, please try reset or login again";
            return response()->json(compact('state', 'token_error'));
        }

    // return response()->json(compact('user'));
        return $next($request);
    }
}
