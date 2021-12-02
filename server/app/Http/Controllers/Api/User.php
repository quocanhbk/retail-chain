<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\TokenController;

class User extends Controller
{
    private function getInfo($userId, $admin) {
        $user = DB::table('users')->where('id', $userId)->first();
        $store = DB::table('stores')->where('id', '=', $user->store_id)->first();
        $employment = !$admin ? DB::table('employments')->where('user_id','=', $user->id)->whereNull('to')->first(): null;
        $branch = !$admin ? DB::table('branches')->where('id', $employment->branch_id)->first() : null;
        $roles = !$admin ? DB::table('employment_roles')->where('employment_id', $employment->id)->pluck('role'): null;
        $user_info = compact('user', 'store', 'branch', 'roles');

        return $user_info;
    }

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

        $info = $this->getInfo($user_id, true);
        $state = 'success';
        $errors = 'none';
        return response()->json(compact('state', 'errors', 'info'), 200)->cookie("bkrm-token", $token);
    }

    public function loginStaff(Request $request) {
        return $this->login($request, false);
    }

    public function loginAdmin(Request $request) {
        return $this->login($request, true);
    }

    private function login(Request $request, $admin){
        $data = $request->all();
        $rules = [
            'email'  => 'required',
            'password'  => 'required'
        ];
        // validate data if satisfy the rule
        $validator = Validator::make($data, $rules);

        if($validator->fails()){
            $state = 'fail';
            $errors = $validator->errors();
            return response()->json(compact('state', 'errors'));
        }

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

        if (($admin && !$user->is_owner) || (!$admin && $user->is_owner)) {
            $state = 'fail';
            $errors = "Permission denied!";
            return response()->json(compact('state', 'errors'), 403);
        }

        //add invalidate all old token and store new token to table
        TokenController::invalidateByID($user->id);
        // store jwt in database => why ? I don't know
        TokenController::create($user->id, $token);

        $info = $this->getInfo($user->id, $admin);
        $state = 'success';
        $errors = 'none';
        return response()->json(compact('state', 'errors', 'info'))->cookie("bkrm-token", $token);
    }

    public function logout(Request $request) {
        $user = auth()->user();
        if($user !== null){
            TokenController::invalidateByID($user->id);
            auth()->logout();
        }
        $state ='success';
        $errors = 'none';
        return response()->json(compact('state', 'errors'))->withoutCookie('bkrm-token');
    }

    public function refreshToken(Request $request) {
        $old_token = $request->input('token');
        $validate_token = DB::table('jwt_info')->where('jwt_info.token', $old_token)->where('jwt_info.is_invalidated', 0)->exists();
        if($validate_token){
            // $token = auth()->refresh();
            $token = JWTAuth::refresh();
            $user = JWTAuth::setToken($token)->user();
            TokenController::invalidateByID($user->id);
            TokenController::create($user->id, $token);
            $state ='success';
            $errors = 'none';
            return response()->json(compact('state', 'errors'))->cookie("bkrm-token", $token);;
        } else {
            $state = "jwt_error";
            $token_error = "JWT invalid or missing, please try reset or login again";
            return response()->json(compact('state', 'token_error'));
        }
    }

    public function getAdminInfo(Request $request)
    {
        $user = auth()->user();
        if (!$user->is_owner) {
            $state = 'failed';
            $errors = "Invalid request";
            return response()->json(compact('state', 'errors'), 401);
        }
        $info = $this->getInfo($user->id, true);
        $state ='success';
        $errors = 'none';

        return response()->json(compact('state', 'errors','info'));
    }

    public function getStaffInfo(Request $request)
    {
        $user = auth()->user();
        if ($user->is_owner) {
            $state = 'failed';
            $errors = "Invalid request";
            return response()->json(compact('state', 'errors'), 401);
        }
        $info = $this->getInfo($user->id, false);
        $state ='success';
        $errors = 'none';

        return response()->json(compact('state', 'errors','info'));
    }

    // public function updateCurrentUserInfo(Request $request) {
    //     $user = auth()->user();
    //     $data = $request->all();
    //     $rules = [
    //         'name'              => 'nullable',
    //         'phone'             => 'nullable',
    //         'birthday'          => 'nullable|date_format:Y-m-d',
    //         'gender'            => 'nullable|in:male,female',
    //         'avatar'            => 'nullable|file',
    //     ];

    //     $validator = Validator::make($data, $rules);

    //     if(!$validator->fails()){
    //         DB::transaction(function () use ($user, $request){
    //             $user_id = $user->id;
    //             DB::table('users')->where('users.id',$user_id)
    //                 ->update([
    //                     'name'          => $request->input('name'),
    //                     'phone'         => $request->input('phone'),
    //                     'birthday'      => $request->input('birthday'),
    //                     'gender'        => $request->input('gender'),
    //                 ]);

    //             //add avatar
    //             if ($request->hasFile('avatar')){
    //                 $image = $request->avatar;
    //                 $ext = $image->getClientOriginalExtension();
    //                 if ($ext == 'png' || $ext == 'jpg') {
    //                     $img_url = 'upload/avatar/';
    //                     $result = DB::table('users')->where('id',$user_id)
    //                         ->update([
    //                             'avatar_url' => $img_url.$user_id.'.'.$ext
    //                             ]);
    //                     if ($result){
    //                         $image->move('../public/'.$img_url,$user_id.'.'.$ext);
    //                     }
    //                 }
    //             }
    //         });

    //         $state ='success';
    //         $errors = 'none';
    //         return response()->json(compact('state', 'errors', 'data'));
    //     } else {
    //         $state = 'fail';
    //         $errors = $validator->errors();
    //         return response()->json(compact('state', 'errors', 'data'));
    //     }
    // }

    public function changePassword(Request $request, $store_id, $branch_id)
    {
        $user = auth()->user();
        $data = $request->except('token');
        $rules = [
            'old_password'  => 'required',
            'new_password'  => 'required'
        ];
        $validator = Validator::make($data, $rules);

        if(!$validator->fails()){
            $credentials['id'] = $user->id;
            $credentials['password'] = $request->input('old_password');
            $credentials['status'] = 'enable';

            if(auth()->validate($credentials)){
                DB::transaction(function () use ($request, $user) {
                    DB::table('users')->where('users.id', $user->id)
                        ->update([
                        'password'      => Hash::make($request->input('new_password'))
                    ]);
                });

                TokenController::invalidateAllByIDExceptToken($user->id, $request->token);
                $state ='success';
                $errors = 'none';
                return response()->json(compact('state', 'errors', 'data'));
            } else {
                $state = 'fail';
                $errors = 'Wrong password';
                return response()->json(compact('state', 'errors', 'data'));
            }
        } else {
            $state = 'fail';
            $errors = $validator->errors();
            return response()->json(compact('state', 'errors', 'data'));
        }
    }
}
