<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use App\FrequentQuery;
use App\TokenController;

class Employee extends Controller
{
    // only owner can create new employee
    public function create(Request $request) {
        $user = auth()->user();
        error_log($user->id);
        $roles = FrequentQuery::getRoles($user->id);
        $is_owner = $roles->contains(function ($value, $key) {
            return $value == 'owner';
        });

        if (!$is_owner) {
            $state = 'fail';
            $errors = 'Permission denied';
            return response()->json(compact('state', 'errors'), 403);
        }

        $store_id = DB::table('stores')->where('id', $user->store_id)->pluck('id')->first();

        $data = $request->all();
        $rules = [
            'name'              => 'required',
            'email'             => 'unique:users|email',
            'password'          => 'required',
            'branch_id'         => 'required',
            'roles'             => 'array'
        ];
        $validator = Validator::make($data, $rules);

        if($validator->fails()){
            $state = 'fail';
            $errors = $validator->errors();
            return response()->json(compact('state', 'errors'));
        }

        DB::transaction(function () use ($request, $user) {
            $user_id = DB::table('users')->insertGetId([
                'name'          => $request->input('name'),
                'password'      => Hash::make($request->input('password')),
                'email'         => $request->input('email'),
                'store_id'      => $user->store_id
            ]);
            error_log("Go here");
            $employment_id = DB::table('employments')->insertGetId([
                'user_id'       => $user_id,
                'branch_id'     => $request->input('branch_id')
            ]);

            $roles = $request->input('roles');
            foreach($roles as $role){
                DB::table('employment_roles')->insert([
                    'employment_id' => $employment_id,
                    'role'          => $role
                ]);
            }
        });

        $state = 'success';
        $errors = 'none';
        return response()->json(compact('state', 'errors'));
    }
}
