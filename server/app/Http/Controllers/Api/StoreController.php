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

class StoreController extends Controller
{
    public function editStore(Request $request, $store_id, $branch_id)
    {
        $user = auth()->user();
        $data = $request->except('token');
        $rules = [
            'store_name'    => 'required',
        ];
        $validator = Validator::make($data, $rules);

        if(!$validator->fails()){
            DB::transaction(function () use($request, $store_id){
                DB::table('stores')->where('stores.id', $store_id)
                    ->update([
                        'name' => $request->input('store_name'),
                    ]);
            });
            
            $state = 'success';
            $errors = 'none';
            return response()->json(compact('state', 'errors', 'data'));
        } else {    
            $state = 'fail';
            $errors = $validator->errors();
            return response()->json(compact('state', 'errors', 'data'));
        }
    }
    
}