<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cookie;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use App\FrequentQuery;
use App\TokenController;


class UserController extends Controller
{
    public function createUser(Request $request){
        // $keys = ['name', 'email', 'password', 'gender', 'date_of_birth'];
        // $data = $request->only($keys);
        $data = $request->all();
        $rules = [
            'name'              => 'required',
            'username'          => 'required|unique:users',
            'password'          => 'required',
            'email'             => 'required|unique:users|email',
            'phone'             => 'required',
            'date_of_birth'     => 'required|date_format:Y-m-d',
            'gender'            => 'required|in:male,female',
            'store_name'        => 'required',
            'branch_name'       => 'required',
            'branch_address'    => 'required',
            'avatar'            => 'nullable|file'
        ];
        $validator = Validator::make($data, $rules);

        if($validator->fails()){
            $state = 'fail';
            $errors = $validator->errors();
            return response()->json(compact('state', 'errors', 'data'));
        } else {
            $img_error_flag = 0;
            DB::transaction(function () use ($request, &$img_error_flag) {
                $user_id = DB::table('users')->insertGetId([
                    'name'          => $request->input('name'),
                    'username'      => $request->input('username'),
                    'password'      => Hash::make($request->input('password')),
                    'email'         => $request->input('email'),
                    'phone'         => $request->input('phone'),
                    'date_of_birth' => $request->input('date_of_birth'),
                    'gender'        => $request->input('gender'),
                ]);
                $store_id = DB::table('stores')->insertGetId([
                    'owner_id'      => $user_id,
                    'name'          => $request->input('store_name')
                ]);
                $branch_id = DB::table('branches')->insertGetId([
                    'store_id'      => $store_id,
                    'name'          => $request->input('branch_name'),
                    'address'       => $request->input('branch_address')
                ]);
                $default_category_list = DB::connection('mysql_merged_db')->table('categories')->get();
                foreach($default_category_list as $default_category){
                    DB::table('item_categories')->insert([
                        'branch_id' => $branch_id,
                        'name'      => $default_category->name
                    ]);
                }
                $roles = DB::table('roles')->get();
                foreach($roles as $role){
                    DB::table('works')->insert([
                        'branch_id' => $branch_id,
                        'user_id'   => $user_id,
                        'role_id'   => $role->id,
                    ]);
                }

                //add avatar
                if ($user_id){
                    if ($request->hasFile('avatar')){
                        $image = $request->avatar;
                        $ext = $image->getClientOriginalExtension();
                        if ($ext == 'png' || $ext == 'jpg'){
                            $img_url = 'upload/avatar/';
                            $result = DB::table('users')->where('id',$user_id)
                                ->update([
                                    'avatar_url' => $img_url.$user_id.'.'.$ext
                                    ]);
                            if ($result){
                                $image->move('../public/'.$img_url,$user_id.'.'.$ext);
                            } else {
                                DB::table('users')->where('id',$user_id)
                                ->update(['avatar_url'=>$img_url.'default_user.png']);
                            }
                        } else {
                            DB::table('users')->where('id',$user_id)
                                ->update(['avatar_url'=>$img_url.'default_user.png']);
                            $img_error_flag = 1;
                        }
                    }
                }
            });
            if($img_error_flag){
                $validator->errors()->add('image', 'Choose an image has extension .png or .jpg');
                $state = 'success';
                $errors = $validator->errors();
            }

            $state = 'success';
            $errors = 'none';
            return response()->json(compact('state', 'errors', 'data'));
        }
    }

    public function login(Request $request){
        // $data = $request->only($keys);
        // get everything from request except token
        $data = $request->except('token');
        $rules = [
            'username'  => 'required',
            'password'  => 'required'
        ];
        // validate data if satisfy the rule
        $validator = Validator::make($data, $rules);

        if($validator->fails()){
            $state = 'fail';
            $errors = $validator->errors();
            return response()->json(compact('state', 'errors', 'data'));
        } else {
            $credentials = $request->only('username', 'password');
            $credentials['status'] = 'enable';
            $token = null;

            try {
                $token = JWTAuth::attempt($credentials, ['status' => 'enable']);

                if (!$token) {
                    $state = 'fail';
                    $errors = "Invalid username, password or account is disable";
                    return response()->json(compact('state', 'errors', 'data'));
                }
            } catch (JWTAuth $e) {
                $state = 'fail';
                $errors = "Failed to create token";
                return response()->json(compact('state', 'errors', 'data'));
            }
            if($token){
                $user = auth()->user();
                $user_work_in_branch = FrequentQuery::getWorkingBranchTable();
                $user_info = DB::table('users')->where('users.status', 'enable')->where('users.id', '=', $user->id)
                            ->joinSub($user_work_in_branch, 'work_in', function ($join) {
                                $join->on('users.id', '=', 'work_in.user_id');
                            })
                            ->leftJoin('branches','branches.id','=','work_in.branch_id')->where('branches.status', 'enable')
                            ->leftJoin('stores','stores.id','=','branches.store_id')
                            ->selectRaw('users.id AS user_id, users.name, users.username, users.avatar_url,users.email, users.phone, users.gender, users.date_of_birth, stores.id AS store_id, stores.name AS store_name,stores.owner_id AS store_owner_id, branches.id AS branch_id, branches.name AS branch_name, branches.address AS branches_address')
                            ->get();
                for($i = 0; $i < count($user_info); $i++){
                    $roles =  DB::table('works')->where('works.user_id', $user->id)->where('works.branch_id', $user_info[$i]->branch_id)
                                ->leftJoin('roles', 'roles.id','=','works.role_id')
                                ->selectRaw('roles.name')
                                ->get();
                    $role_array = [];
                    foreach($roles as $role){
                        $role_array[] = $role->name;
                    }
                    $user_info[$i]->roles = $role_array;
                }
                //add invalidate all old token and store new token to table
                TokenController::invalidateByID($user->id);
                // store jwt in database => why ? I don't know
                TokenController::create($user->id, $token);
            }
            $state = 'success';
            $errors = 'none';
            $user_info = $user_info[0];
            return response()->json(compact('state', 'errors', 'token', 'user_info'))->cookie("bkrm-token", $token);
        }
    }

    public function logout(Request $request) {
        $user = auth()->user();
        if($user !== null){
            TokenController::invalidateByID($user->id);
            auth()->logout();
        }
        $state ='success';
        $errors = 'none';
        return response()->json(compact('state', 'errors'));
    }

    public function refreshToken(Request $request)
    {
        $old_token = $request->input('token');
        $validate_token = DB::table('jwt_info')->where('jwt_info.token', $old_token)->where('jwt_info.is_invalidated', 0)->exists();
        if($validate_token){
            $token = JWTAuth::refresh();
            $user = JWTAuth::setToken($token)->user();
            TokenController::invalidateByID($user->id);
            TokenController::create($user->id, $token);
            $state ='success';
            $errors = 'none';
            return response()->json(compact('state', 'errors', 'token'));
        } else {
            $state = "jwt_error";
            $token_error = "JWT invalid or missing, please try reset or login again";
            return response()->json(compact('state', 'token_error'));
        }
    }

    public function getCurrentUserInfo(Request $request)
    {
        $user = auth()->user();
        $data = $request->except('token');

        $user_work_in_branch = FrequentQuery::getWorkingBranchTable();
        $user_info = DB::table('users')->where('users.status', 'enable')->where('users.id', '=', $user->id)
                    ->joinSub($user_work_in_branch, 'work_in', function ($join) {
                        $join->on('users.id', '=', 'work_in.user_id');
                    })
                    ->leftJoin('branches','branches.id','=','work_in.branch_id')->where('branches.status', 'enable')
                    ->leftJoin('stores','stores.id','=','branches.store_id')
                    ->selectRaw('users.id AS user_id, users.name, users.username, users.avatar_url,users.email, users.phone, users.gender, users.date_of_birth, stores.id AS store_id, stores.name AS store_name,stores.owner_id AS store_owner_id, branches.id AS branch_id, branches.name AS branch_name, branches.address AS branches_address');

        $user_info = $user_info->get();
        for($i = 0; $i < count($user_info); $i++){
            $roles =  DB::table('works')->where('works.user_id', $user->id)->where('works.branch_id', $user_info[$i]->branch_id)
                        ->leftJoin('roles', 'roles.id','=','works.role_id')
                        ->selectRaw('roles.name')
                        ->get();
            $role_array = [];
            foreach($roles as $role){
                $role_array[] = $role->name;
            }
            $user_info[$i]->roles = $role_array;
        }

        $state ='success';
        $errors = 'none';
        return response()->json(compact('state', 'errors', 'data', 'user_info'));
    }

    public function updateCurrentUserInfo(Request $request, $store_id, $branch_id)
    {
        $user = auth()->user();
        $data = $request->except('token');
        $rules = [
            'name'              => 'required',
            'email'             => ['nullable','email',
                                    Rule::unique('users','email')
                                        ->where('status', 'enable')
                                        ->ignore($user->id)
                                    ],
            'phone'             => 'nullable',
            'date_of_birth'     => 'nullable|date_format:Y-m-d',
            'gender'            => 'nullable|in:male,female',
            'avatar'            => 'nullable|file',
        ];
        $validator = Validator::make($data, $rules);

        if(!$validator->fails()){
            DB::transaction(function () use ($user, $request){
                $user_id = $user->id;
                DB::table('users')->where('users.id',$user_id)
                    ->update([
                        'name'          => $request->input('name'),
                        'email'         => $request->input('email'),
                        'phone'         => $request->input('phone'),
                        'birthday'      => $request->input('birthday'),
                        'gender'        => $request->input('gender'),
                    ]);

                //add avatar
                if ($user_id){
                    if ($request->hasFile('avatar')){
                        $image = $request->avatar;
                        $ext = $image->getClientOriginalExtension();
                        if ($ext == 'png' || $ext == 'jpg'){
                            $img_url = 'upload/avatar/';
                            $result = DB::table('users')->where('id',$user_id)
                                ->update([
                                    'avatar_url' => $img_url.$user_id.'.'.$ext
                                    ]);
                            if ($result){
                                $image->move('../public/'.$img_url,$user_id.'.'.$ext);
                            } else {
                                DB::table('users')->where('id',$user_id)
                                ->update(['avatar_url'=>$img_url.'default_user.png']);
                            }
                        } else {
                            DB::table('users')->where('id',$user_id)
                                ->update(['avatar_url'=>$img_url.'default_user.png']);
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
                    $user_id = DB::table('users')->where('users.id', $user->id)
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
