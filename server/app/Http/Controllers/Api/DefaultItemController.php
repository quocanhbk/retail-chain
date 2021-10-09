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

class DefaultItemController extends Controller
{
    public function searchItemByBarcode(Request $request, $store_id, $branch_id){
        $user = auth()->user();
        $data = $request->except('token');
        $rules = [
            'bar_code'  => 'required',
        ];
        $validator = Validator::make($data, $rules);
        $check_permission = FrequentQuery::checkPermission($user->id, $branch_id, ['purchasing', 'selling']);

        if($check_permission && !$validator->fails()){
            // $item = DB::table('default_items')->where('default_items.deleted',0)
            //             ->leftJoin('default_categories','default_categories.id','=','default_items.category_id');
            // $item = $item->where('default_items.bar_code','=',$request->input('bar_code'));
            // $item = $item->selectRaw('default_items.*, default_categories.name AS category_name');
            // $item = $item->get();
            
            $item = DB::connection('mysql_merged_db')->table('barcode_data')
                        ->leftJoin('categories','categories.id','=','barcode_data.category_id');
            $item = $item->where('barcode_data.bar_code','=',$request->input('bar_code'));
            $item = $item->selectRaw('barcode_data.category_id, barcode_data.product_name AS name, barcode_data.bar_code, barcode_data.image_url, categories.name AS category_name');
            $item = $item->get();

            $state = 'success';
            $errors = 'none';
            return response()->json(compact('state', 'errors', 'data', 'item'));
        } else {
            $state = 'fail';
            if(!$check_permission){
                $errors = 'You dont have the permission to do this';
            } else {
                $errors = $validator->errors();
            }
            // $errors = !$check_permission? 'You dont have the permission to do this':$validator->errors();
            return response()->json(compact('state', 'errors', 'data'));
        }
    }
}