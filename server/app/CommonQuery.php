<?php

namespace App;

use Illuminate\Support\Facades\DB;

class CommonQuery {

    public static function getUser($userId) {
        $user = DB::table('users')->where('id', $userId)->first();
        return $user;
    }

    public static function getInfo($userId) {
        $user = CommonQuery::getUser($userId);

        if (!$user) return null;

        $store = DB::table('stores')->where('id', '=', $user->store_id)->first();
        $employment = DB::table('employments')->where('user_id','=', $user->id)->whereNull('to')->first();
        $branch = DB::table('branches')->where('id', $employment->branch_id)->first();
        $roles = DB::table('employment_roles')->where('employment_id', $employment->id)->pluck('role');
        $user_info = compact('user', 'store', 'branch', 'roles');

        return $user_info;
    }

}
