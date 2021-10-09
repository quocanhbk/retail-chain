<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\FrequentQuery;

class AuthenticationController extends WebController
{
    public function login(Request $request){
        $credentials = $request->only('username', 'password');

        if(Auth::attempt($credentials)){
            $user = Auth::user();
            $is_owner = DB::table('stores')
                        ->where('stores.owner_id', $user->id)
                        ->exists();
            if($is_owner){
                $store_info = DB::table('stores')
                                ->leftJoin('branches', 'branches.store_id', '=', 'stores.id')
                                ->leftJoin('works', 'works.branch_id', '=', 'branches.id')
                                ->where('works.user_id', $user->id)
                                ->selectRaw('stores.id AS store_id, branches.id AS branch_id')
                                ->groupByRaw('stores.id, branches.id')
                                ->first();
                $store_id = $store_info->store_id;
                $branch_id = $store_info->branch_id;
                return redirect()->route('inventory.item', compact('store_id', 'branch_id'));
            } else {
                Auth::logout();
                $error_mess = "Tài khoản không phải chủ cửa hàng";
                return view('login', compact('error_mess'));
            }
        }
        $error_mess = "Tên đăng nhập hoặc mật khẩu không chính xác";
        return view('login', compact('error_mess'));
    }
    public function logout(Request $request){
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}