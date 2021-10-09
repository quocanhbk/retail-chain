<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Validation\Rule;
use App\FrequentQuery;
use DateTime;
use DatePeriod;
use DateInterval;

class RoleController extends Controller
{
    public function getRole(Request $request, $store_id, $branch_id)
    {
        $user = auth()->user();
        $data = $request->except('token');
        // $rules = [
        //     'branch_name'    => 'required',
        //     'branch_address' => 'required',
        // ];
        // $validator = Validator::make($data, $rules);

        // if(!$validator->fails()){
        //     DB::transaction(function () use($request, $branch_id){
        //         DB::table('branches')->where('branches.id', $branch_id)
        //             ->update([
        //                 'name'      => $request->input('branch_name'),
        //                 'address'   => $request->input('branch_address'),
        //             ]);
        //     });
            
        //     $state = 'success';
        //     $errors = 'none';
        //     return response()->json(compact('state', 'errors', 'data'));
        // } else {    
        //     $state = 'fail';
        //     $errors = $validator->errors();
        //     return response()->json(compact('state', 'errors', 'data'));
        // }
        
        $role_list = DB::table('roles')->get();
        
        $state = 'success';
        $errors = 'none';
        return response()->json(compact('state', 'errors', 'data', 'role_list'));
    }
    
}