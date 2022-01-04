<?php

namespace App;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class CommonApi {

    public static function login(Request $request, $admin){
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

        $info = $admin ? compact('user') : CommonQuery::getInfo($user->id);

        if (!$info) {
            $state = 'failed';
            $errors = 'Employee account is terminated';
            return response()->json(compact('state', 'errors'), 404);
        }

        $state = 'success';
        $errors = 'none';
        return response()->json(compact('state', 'errors', 'info'))->cookie("bkrm-token", $token);
    }

    public static function logout(Request $request) {
        $user = auth()->user();
        if($user !== null){
            TokenController::invalidateByID($user->id);
            auth()->logout();
        }
        $state ='success';
        $errors = 'none';
        return response()->json(compact('state', 'errors'))->withoutCookie('bkrm-token');
    }

    public static function refreshToken(Request $request) {
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
}
