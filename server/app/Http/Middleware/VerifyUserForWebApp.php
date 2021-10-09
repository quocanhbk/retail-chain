<?php

namespace App\Http\Middleware;

use Closure;
use Auth;
use View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VerifyUserForWebApp
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
        $user = Auth::user();
        $store_id = $request->route()->parameter('store_id');
        $branch_id = $request->route()->parameter('branch_id');
        $is_owner = DB::table('stores')
                    ->where('owner_id', $user->id)
                    ->where('id', $store_id)
                    ->exists();
        $is_branch_belong_store = DB::table('stores')->where('stores.id', $store_id)
                                    ->leftJoin('branches', 'branches.store_id', '=', 'stores.id')
                                    ->where('branches.id', $branch_id)
                                    ->exists();
        if($is_owner && $is_branch_belong_store){
            $branch_name = DB::table('branches')
                        ->selectRaw('branches.name AS branch_name')
                        ->groupByRaw('branches.name')
                        ->first();
            $branch_name = $branch_name->branch_name;
            View::share('branch_name', $branch_name);
            return $next($request);
        } else {
            $store_info = DB::table('stores')
                            ->leftJoin('branches', 'branches.store_id', '=', 'stores.id')
                            ->where('stores.owner_id', $user->id)
                            ->selectRaw('stores.id AS store_id, branches.id AS branch_id, branches.name AS branch_name')
                            ->first();
            $store_id = $store_info->store_id;
            $branch_id = $store_info->branch_id;
            $branch_name = $store_info->branch_name;
            View::share('branch_name', $branch_name);
            $route_name = $request->route()->getName(); 
            return redirect()->route($route_name, compact('store_id', 'branch_id'));
        }
    }
}
