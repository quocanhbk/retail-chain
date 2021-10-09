<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\DB;
use App\FrequentQuery;

class VerifyWork
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
        $branch_id = $request->branch_id;
        $is_work_in_branch = FrequentQuery::getEmployeeList($branch_id);
        $is_work_in_branch = $is_work_in_branch->where('works.user_id', $user->id)->exists();
        if($is_work_in_branch){
            return $next($request);
        } else {
            $state = "fail";
            $errors = "user_id: ".$user->id." is not belonged to branch_id: ".$branch_id;
            return response()->json(compact('state','errors'));
        }
    }
}
