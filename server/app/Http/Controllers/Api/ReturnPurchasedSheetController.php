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

class ReturnPurchasedSheetController extends Controller
{
    public function createReturnPurchasedSheet(Request $request, $store_id, $branch_id)
    {
        /*
            return_purchased_sheet : {
                purchased_sheet_id : int
            },
            return_purchased_items : [
                {
                    purchased_item_id : int
                },....
            ]
        */
        $user = auth()->user();
        $data = $request->except('token');
        $rules = [
            'return_purchased_sheet'   => 'required',
            'return_purchased_items'   => 'required'
        ];
        $validator = Validator::make($data, $rules);

        $return_purchased_sheet =  $request->input('return_purchased_sheet');
        $return_purchased_items =  $request->input('return_purchased_items');

        $check_permission = FrequentQuery::checkPermission($user->id, $branch_id, ['purchasing']);
        $check_purchased_sheet_id = DB::table('purchased_sheets')->where('purchased_sheets.branch_id',$branch_id)->where('purchased_sheets.id',$return_purchased_sheet['purchased_sheet_id'])->exists();

        if($check_permission && $check_purchased_sheet_id && !$validator->fails()){
            $total_return_money = 0;

            if(count($return_purchased_items) > 0){
                //check purchased_item_id
                foreach($return_purchased_items as $return_purchased_item){
                    $purchased_item = DB::table('purchased_items')
                                        ->where('purchased_items.id', $return_purchased_item['purchased_item_id'])
                                        ->selectRaw('purchased_items.purchase_price, purchased_items.quantity')
                                        ->first();
                    if($purchased_item !== null){
                        $total_return_money += $purchased_item->purchase_price * $purchased_item->quantity;
                    } else {
                        $state ='false';
                        $errors = "purchased_item_id: ".$return_purchased_item['purchased_item_id']." doesnt exist";
                        return response()->json(compact('state', 'errors', 'data'));
                    }
                }
    
                DB::transaction(function () use ($branch_id, $user, $return_purchased_sheet, $return_purchased_items, $total_return_money){
                    //insert return_purchased_sheet
                    $return_purchased_sheet_id = DB::table('return_purchased_sheets')->insertGetId([
                        'branch_id'             => $branch_id,
                        'returner_id'           => $user->id,
                        'purchased_sheet_id'    => $return_purchased_sheet['purchased_sheet_id'],
                        'total_return_money'    => $total_return_money,
                    ]);
                    //update corresponding purchased_sheet total_purchase_price
                    $purchased_sheet = DB::table('purchased_sheets')->where('purchased_sheets.id', $return_purchased_sheet['purchased_sheet_id'])->select('total_purchase_price')->first();
                    DB::table('purchased_sheets')->where('purchased_sheets.id', $return_purchased_sheet['purchased_sheet_id'])
                        ->update([
                            'total_purchase_price'  => $purchased_sheet->total_purchase_price - $total_return_money
                        ]);
                    $purchased_sheet = DB::table('purchased_sheets')->where('purchased_sheets.id', $return_purchased_sheet['purchased_sheet_id'])->select('total_purchase_price')->first();
                    if($purchased_sheet->total_purchase_price == 0){
                        DB::table('purchased_sheets')->where('purchased_sheets.id', $return_purchased_sheet['purchased_sheet_id'])
                        ->update([
                            'discount'  => 0
                        ]);
                    }

                    foreach($return_purchased_items as $return_purchased_item){
                        $purchased_item = DB::table('purchased_items')
                                            ->where('purchased_items.id', $return_purchased_item['purchased_item_id'])
                                            ->selectRaw('purchased_items.purchase_price, purchased_items.quantity')
                                            ->first();
                        $purchased_price = $purchased_item->purchase_price;
                        $quantity = $purchased_item->quantity;
                        //insert return_purchased_item
                        DB::table('return_purchased_items')->insert([
                            'return_sheet_id'       => $return_purchased_sheet_id,
                            'purchased_item_id'     => $return_purchased_item['purchased_item_id'],
                            'old_purchased_price'   => $purchased_price,
                            'old_quantity'          => $quantity,
                        ]);
                        //update quant and purchase_price of corresponding purchased_items and item_quantities
                        DB::table('purchased_items')
                            ->leftJoin('item_quantities', 'item_quantities.item_id', '=','purchased_items.item_id')
                            ->where('purchased_items.id', $return_purchased_item['purchased_item_id'])
                            ->update([
                                'purchased_items.purchase_price'    => 0,
                                'purchased_items.quantity'          => 0,
                                'item_quantities.quantity'          => DB::raw("item_quantities.quantity - $quantity")
                            ]);
                    }
                });
            } else {
                $state ='fail';
                if(!$check_permission){
                    $errors = 'You dont have the permission to do this';
                } else if(!$check_purchased_sheet_id){
                    $errors = "purchased_sheet_id doesnt belong to branch";
                } else if($validator->fails()){
                    $errors = $validator->errors();
                }
                return response()->json(compact('state', 'errors', 'data'));
            }
            
            $state ='success';
            $errors = 'none';
            return response()->json(compact('state', 'errors', 'data'));
        } else {
            $state = 'fail';
            if(!$check_permission){
                $errors = 'You dont have the permission to do this';
            } else if (!$check_purchased_sheet_id){
                $errors = "purchased_sheet_id: ".$return_purchased_sheet['purchased_sheet_id']." doesnt belong to branch_id: $branch_id";
            } else {
                $errors = $validator->errors();
            }
            return response()->json(compact('state', 'errors', 'data'));
        }
    }

    public function getReturnPurchasedSheet(Request $request, $store_id, $branch_id)
    {
        $user = auth()->user();
        $data = $request->except('token');
        $rules = [
            'return_purchased_sheet_id' => ['integer'],
            'purchased_sheet_id '       => ['integer'],
            'returner_id  '              => ['integer'],
            'order_by'                  => 'required_with:order|in:total_return_money,created_datetime,returner_name',
            'order'                     => 'required_with:order_by|in:asc,desc',

            'keyword'                   => 'nullable',
            'total_return_money_from'   => 'nullable',
            'total_return_money_to'     => 'nullable',
            'created_datetime_from'     => 'nullable|date_format:Y-m-d',
            'created_datetime_to'       => 'nullable|date_format:Y-m-d',
        ];
        $validator = Validator::make($data, $rules);

        $check_permission = FrequentQuery::checkPermission($user->id, $branch_id, ['purchasing']);

        if($check_permission && !$validator->fails()){
            $return_purchased_sheet = DB::table('return_purchased_sheets')->where('return_purchased_sheets.branch_id',$branch_id)
                                ->leftJoin('purchased_sheets','purchased_sheets.id','=','return_purchased_sheets.purchased_sheet_id')
                                ->leftJoin('suppliers','suppliers.id', '=', 'purchased_sheets.supplier_id')
                                ->leftJoin('users','users.id','=','return_purchased_sheets.returner_id');

            $return_purchased_sheet = $request->input('return_purchased_sheet_id')? 
                                        $return_purchased_sheet->where('return_purchased_sheets.id',$request->input('return_purchased_sheet_id')) 
                                        : $return_purchased_sheet;
            $return_purchased_sheet = $request->input('purchased_sheet_id')? 
                                        $return_purchased_sheet->where('return_purchased_sheets.purchased_sheet_id',$request->input('purchased_sheet_id')) 
                                        : $return_purchased_sheet;
            $return_purchased_sheet = $request->input('returner_id')? 
                                        $return_purchased_sheet->where('return_purchased_sheets.returner_id',$request->input('returner_id')) 
                                        : $return_purchased_sheet;
            $return_purchased_sheet = $request->input('keyword')? $return_purchased_sheet->where(function ($query) use($request){
                                                    $query->orWhere('suppliers.name','like', '%'.$request->input('keyword').'%');
                                                    $query->orWhere('suppliers.phone','like', '%'.$request->input('keyword').'%');
                                                }) : $return_purchased_sheet;

            $return_purchased_sheet = $request->input('total_return_money_from')? 
                                $return_purchased_sheet->where('return_purchased_sheets.total_return_money', '>=',$request->input('total_return_money_from')) 
                                : $return_purchased_sheet;
            $return_purchased_sheet = $request->input('total_return_money_to')? 
                                $return_purchased_sheet->where('return_purchased_sheets.total_return_money', '<=',$request->input('total_return_money_to')) 
                                : $return_purchased_sheet;
            $return_purchased_sheet = $request->input('created_datetime_from')? 
                                $return_purchased_sheet->where('return_purchased_sheets.created_datetime', '>=',$request->input('created_datetime_from')." 00:00:00") 
                                : $return_purchased_sheet;
            $return_purchased_sheet = $request->input('created_datetime_to')? 
                                $return_purchased_sheet->where('return_purchased_sheets.created_datetime', '<=',$request->input('created_datetime_to')." 23:59:59") 
                                : $return_purchased_sheet;

            $return_purchased_sheet = $return_purchased_sheet->selectRaw('return_purchased_sheets.id AS return_purchased_sheet_id, return_purchased_sheets.purchased_sheet_id, return_purchased_sheets.returner_id, users.name AS returner_name, purchased_sheets.supplier_id, suppliers.name AS supplier_name, return_purchased_sheets.total_return_money, return_purchased_sheets.created_datetime');

            if($request->input('order_by') && $request->input('order')){
                switch ($request->input('order_by')) {
                    case "total_return_money":
                        $order_by = "return_purchased_sheets.total_return_money";
                        break;
                    case "created_datetime":
                        $order_by = "return_purchased_sheets.created_datetime";
                        break;
                    case "returner_name":
                        $order_by = "users.name";
                        break;
                }
                $order = $request->input('order');
                $return_purchased_sheet = $return_purchased_sheet->orderBy($order_by, $order);
            } else {
                $return_purchased_sheet = $return_purchased_sheet->orderByRaw('return_purchased_sheets.created_datetime DESC');
            }
            $return_purchased_sheet = $return_purchased_sheet->paginate(10);
            
            $state ='success';
            $errors = 'none';
            return response()->json(compact('state', 'errors', 'data', 'return_purchased_sheet'));
        } else {
            $state = 'fail';
            if(!$check_permission){
                $errors = 'You dont have the permission to do this';
            } else {
                $errors = $validator->errors();
            }
            return response()->json(compact('state', 'errors', 'data'));
        }
    }
    
    public function getReturnPurchasedSheetDetail(Request $request, $store_id, $branch_id)
    {
        $user = auth()->user();
        $data = $request->except('token');
        $rules = [
            'return_purchased_sheet_id'    => ['required','integer',Rule::exists('return_purchased_sheets','id')->where('branch_id', $branch_id)],
        ];
        $validator = Validator::make($data, $rules);

        $check_permission = FrequentQuery::checkPermission($user->id, $branch_id, ['purchasing']);

        if($check_permission && !$validator->fails()){
            $return_purchased_sheet = DB::table('return_purchased_sheets')->where('return_purchased_sheets.branch_id',$branch_id)->where('return_purchased_sheets.id',$request->input('return_purchased_sheet_id'))
                                ->leftJoin('return_purchased_items','return_purchased_items.return_sheet_id','=','return_purchased_sheets.id')
                                ->leftJoin('purchased_items','purchased_items.id','=','return_purchased_items.purchased_item_id')
                                ->leftJoin('items','items.id','=','purchased_items.item_id');

            $return_purchased_sheet = $return_purchased_sheet->selectRaw('return_purchased_sheets.id AS return_purchased_sheet_id, return_purchased_items.id AS return_purchased_item_id, purchased_items.item_id, items.name, items.image_url, return_purchased_items.old_purchased_price, return_purchased_items.old_quantity');
            $return_purchased_sheet = $return_purchased_sheet->get();
            
            $state ='success';
            $errors = 'none';
            return response()->json(compact('state', 'errors', 'data', 'return_purchased_sheet'));
        } else {
            $state = 'fail';
            if(!$check_permission){
                $errors = 'You dont have the permission to do this';
            } else {
                $errors = $validator->errors();
            }
            return response()->json(compact('state', 'errors', 'data'));
        }
    }
}