<?php

namespace App\Http\Controllers\New;

use App\CommonApi;
use App\CommonQuery;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;

class Admin extends Controller
{

    public function register(Request $request){
        $data = $request->all();
        $rules = [
            'name'              => 'required',
            'email'             => 'required|unique:users|email',
            'password'          => 'required',
            'store_name'        => 'required',
        ];

        $validator = Validator::make($data, $rules);

        if($validator->fails()){
            $state = 'fail';
            $errors = $validator->errors();
            return response()->json(compact('state', 'errors'), 400);
        }

        $user_id = DB::transaction(function () use ($request) {

            // init new store
            $store_id = DB::table('stores')->insertGetId([
                'name'          => $request->input('store_name')
            ]);

            // init new user
            $user_id = DB::table('users')->insertGetId([
                'name'          => $request->input('name'),
                'password'      => Hash::make($request->input('password')),
                'email'         => $request->input('email'),
                'phone'         => $request->input('phone'),
                'store_id'      => $store_id,
                'is_owner'      => true
            ]);

            return $user_id;
        });

        $credentials = $request->only('email', 'password');
        $credentials['status'] = 'enabled';
        $token = null;

        try {
            $token = JWTAuth::attempt($credentials, ['status' => 'enabled']);

            if (!$token) {
                $state = 'fail';
                $errors = "Invalid username, password or account is disabled";
                return response()->json(compact('state', 'errors'), 401);
            }
        } catch (JWTAuth $e) {
            $state = 'fail';
            $errors = "Failed to create token";
            return response()->json(compact('state', 'errors'), 401);
        }

        $user = auth()->user();
        $info = compact('user');
        $state = 'success';
        $errors = 'none';
        return response()->json(compact('state', 'errors', 'info'), 200)->cookie("bkrm-token", $token);
    }

    public function login(Request $request) {
        return CommonApi::login($request, true);
    }


    public function logout(Request $request) {
        return CommonApi::logout($request);
    }

    public function refreshToken(Request $request) {
        return CommonApi::refreshToken($request);
    }

    public function getInfo(Request $request){

        $user = auth()->user();

        if (!$user) {
            $state = 'failed';
            $errors = 'User not found';
            return response()->json(compact('state', 'errors'), 404);
        }

        $info = compact('user');
        $state = 'success';
        return response()->json(compact('state', 'info'), 200);

    }

    public function createEmployee(Request $request) {
        $user = auth()->user();

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
            return response()->json(compact('state', 'errors'), 400);
        }

        $available_branches = DB::table('branches')->where('store_id', $user->store_id)->pluck('id')->toArray();
        if (!in_array($request->branch_id, $available_branches)) {
            $state = 'fail';
            $errors = 'Branch not found';
            return response()->json(compact('state', 'errors'), 404);
        }

        DB::transaction(function () use ($request, $user) {
            $user_id = DB::table('users')->insertGetId([
                'name'          => $request->input('name'),
                'password'      => Hash::make($request->input('password')),
                'email'         => $request->input('email'),
                'store_id'      => $user->store_id
            ]);
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

    public function updateEmployee(Request $request) {
        $data = $request->all();
        $rules = [
            'id'                => 'required',
            'name'              => 'nullable',
            'phone'             => 'nullable',
            'birthday'          => 'nullable|date_format:Y-m-d',
            'gender'            => 'nullable|in:male,female',
            'avatar'            => 'nullable|file',
        ];

        $validator = Validator::make($data, $rules);

        $user = $this->getUser($request->input('id'));

        if(!$validator->fails()){
            DB::transaction(function () use ($user, $request){
                $user_id = $user->id;
                DB::table('users')->where('users.id',$user_id)
                    ->update([
                        'name'          => $request->input('name'),
                        'phone'         => $request->input('phone'),
                        'birthday'      => $request->input('birthday'),
                        'gender'        => $request->input('gender'),
                    ]);

                //add avatar
                if ($request->hasFile('avatar')){
                    $image = $request->avatar;
                    $ext = $image->getClientOriginalExtension();
                    if ($ext == 'png' || $ext == 'jpg') {
                        $img_url = 'upload/avatar/';
                        $result = DB::table('users')->where('id',$user_id)
                            ->update([
                                'avatar_url' => $img_url.$user_id.'.'.$ext
                                ]);
                        if ($result){
                            $image->move('../public/'.$img_url,$user_id.'.'.$ext);
                        }
                    }
                }
            });

            $state ='success';
            $errors = 'none';
            return response()->json(compact('state', 'errors', 'data'));
        } else {
            $state = 'fail';
            $errors = $validator->errors();
            return response()->json(compact('state', 'errors', 'data'));
        }
    }

    public function getEmployees(Request $request, $branch_id) {
        $users = DB::table('users')->where('is_owner', false)->whereExists(function ($query) use ($branch_id) {
            $query->select("*")->from('employments')->where('branch_id', $branch_id)->whereColumn('user_id', 'users.id')->whereNull('to');
        })->get();

        $state = "success";
        $info = compact('users');
        return response()->json(compact('state', 'info' ), 200);
    }

    public function terminateEmployee(Request $request) {
        $owner = auth()->user();
        $data = $request->all();
        $rules = [
            'user_id'             => 'required|number'
        ];
        $validator = Validator::make($data, $rules);

        if ($validator->failed()) {
            $state = 'failed';
            $errors = $validator->errors();
            return response()->json(compact('state', 'errors'), 400);
        }

        $terminated_user = CommonQuery::getUser($request->input('user_id'));

        if ($owner->store_id != $terminated_user->store_id) {
            $state = 'failed';
            $errors = 'Permission denied';
            return response()->json(compact('state', 'errors'), 403);
        }

        DB::table('employments')->where('user_id', $terminated_user->user_id)->update(['to', date('Y-m-d')]);
    }
}
