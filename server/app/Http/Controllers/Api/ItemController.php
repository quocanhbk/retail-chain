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

class ItemController extends Controller
{
    public function createItem(Request $request, $store_id, $branch_id)
    {
        $user = auth()->user();
        $data = $request->except('token');
        $rules = [
            'category_id'       => ['required',
                                    Rule::exists('item_categories', 'id')
                                        ->where('branch_id', $branch_id)
                                        ->where('deleted',0)],
            'item_name'         => 'required',
            'bar_code'          => '',
            'image'             => 'file',
            'sell_price'        => 'required',
            'quantity'          => '',
            'purchase_price'    => '',
        ];
        $validator = Validator::make($data, $rules);

        $check_permission = FrequentQuery::checkPermission($user->id, $branch_id, ['selling', 'purchasing']);
        if($check_permission && !$validator->fails()){
            $img_error_flag = 0;
            $item_id = 0;
            if($request->input('bar_code')){
                $bar_code = $request->input('bar_code');
            } else {
                $count = 1;
                $bar_code = $store_id.$branch_id.$count;
                $check_barcode_exist = DB::table('items')->where('items.bar_code', $bar_code)->exists();
                while($check_barcode_exist){
                    $count++;
                    $bar_code = $store_id.$branch_id.$count;
                    $check_barcode_exist = DB::table('items')->where('items.bar_code', $bar_code)->exists();
                }
            }
            DB::transaction(function () use ($request, $store_id, $branch_id, &$img_error_flag, &$item_id, $bar_code, $user) {
                $item_id = DB::table('items')->insertGetId([
                    'category_id'   => $request->input('category_id'),
                    'name'          => $request->input('item_name'),
                    'bar_code'      => $bar_code,
                ]);
                $item_price_id = DB::table('item_prices')->insertGetId([
                    'item_id'       => $item_id,
                    'sell_price'    => $request->input('sell_price'),
                    'change_by'     => $user->id,
                ]);
                $item_quant_id = DB::table('item_quantities')->insertGetId([
                    'item_id'       => $item_id,
                    'branch_id'     => $branch_id,
                    'quantity'      => $request->input('quantity')
                ]);

                //create purchase price of item by create a new purchased_sheet and purchased_item with quantity = 0
                $purchase_price = $request->input('purchase_price')? $request->input('purchase_price') : 0;
                $purchased_sheet_id = DB::table('purchased_sheets')->insertGetId([
                    'branch_id'             => $branch_id,
                    'purchaser_id'          => $user->id,
                    'supplier_id'           => null,
                    'total_purchase_price'  => 0,
                    'discount'              => 0,
                    'deliver_name'          => null,
                ]);
                $purchased_item_id = DB::table('purchased_items')->insertGetId([
                    'purchased_sheet_id'    => $purchased_sheet_id,
                    'item_id'               => $item_id,
                    'purchase_price'        => $purchase_price,
                    'quantity'              => 0
                ]);
                //add image
                if ($item_id){
                    if ($request->hasFile('image')){
                        $image = $request->image;
                        $ext = $image->getClientOriginalExtension();
                        if ($ext == 'png' || $ext == 'jpg'){
                            $img_url = 'upload/store_id_'.$store_id.'/branch_id_'.$branch_id.'/';
                            $result = DB::table('items')->where('id',$item_id)
                                ->update([
                                    'image_url' => $img_url.$item_id.'.'.$ext
                                    ]);
                            if ($result){
                                $image->move('../public/'.$img_url,$item_id.'.'.$ext);
                            } else {
                                DB::table('items')->where('id',$item_id)
                                ->update(['image_url'=>'']);
                            }
                        } else {
                            $img_error_flag = 1;
                        }
                    }
                }
            });
            if($img_error_flag){
                $validator->errors()->add('image', 'Choose an image has extension .png or .jpg');
                $state = 'fail';
                $errors = $validator->errors();
                return response()->json(compact('state', 'errors', 'data'));
            } else {
                $purchase_price_info = FrequentQuery::getLatestPurchasedPrice();
                $item = FrequentQuery::getItemInfo($branch_id);
                $item = $item->leftJoinSub($purchase_price_info, 'purchase_price_info', function ($join){
                                $join->on('purchase_price_info.item_id', '=','items.id');
                            });
                $item = $item->where('items.id', $item_id);
                $item = $item->selectRaw('items.id AS item_id, items.name AS item_name, items.bar_code, items.image_url, items.created_datetime, item_categories.id AS category_id, item_categories.name as category_name, item_quantities.quantity,item_prices.id as price_id, item_prices.sell_price, COALESCE(purchase_price_info.purchase_price, 0) AS purchase_price, COALESCE(items.point_ratio, item_categories.point_ratio) AS point_ratio');
                $item = $item->first();

                $state = 'success';
                $errors = 'none';
                return response()->json(compact('state', 'errors', 'data','item'));
            }
        } else {
            $state = 'fail';
            $errors = !$check_permission? 'You dont have the permission to do this': $validator->errors();
            return response()->json(compact('state', 'errors', 'data'));
        }
    }

    public function getItem(Request $request, $store_id, $branch_id)
    {
        $user = auth()->user();
        $data = $request->except('token');
        $rules = [
            'item_id'       => 'array',
            'category_id'   => ['integer',Rule::exists('item_categories','id')->where('branch_id',$branch_id)],
            'search_query'  => 'nullable',
            'order_by'      => 'required_with:order|in:item_name,sell_price,created_date,purchase_price',
            'order'         => 'required_with:order_by|in:asc,desc',
            'is_get_all'    => 'nullable|boolean'
        ];
        $validator = Validator::make($data, $rules);

        $check_permission = FrequentQuery::checkPermission($user->id, $branch_id, ['selling', 'purchasing']);
        $item_id_list = $request->input('item_id');

        if($check_permission && !$validator->fails()){
            $item = FrequentQuery::getItemInfo($branch_id);
            $purchase_price_info = FrequentQuery::getLatestPurchasedPrice();
            $item = $item->leftJoinSub($purchase_price_info, 'purchase_price_info', function ($join){
                            $join->on('purchase_price_info.item_id', '=','items.id');
                        });
            if($item_id_list){
                $item = $item->where(function ($query) use ($item_id_list){
                    foreach($item_id_list as $item_id){
                        $query = $query->orWhere('items.id',$item_id);
                    }
                });
            }
            $item = $request->input('category_id')? $item->where('item_categories.id', $request->input('category_id')) : $item;
            $item = $request->input('search_query')? $item->where(function ($query) use($request){
                $query->where('items.name', 'like' ,'%'.$request->input('search_query').'%');
                $query->orWhere('items.bar_code', 'like' ,'%'.$request->input('search_query').'%');
                // $query->orWhere('items.bar_code', $request->input('search_query'));
            }) : $item;


            $item = $item->selectRaw('items.id AS item_id, items.name AS item_name, items.bar_code, items.image_url, items.created_datetime, item_categories.id AS category_id, item_categories.name as category_name, item_quantities.quantity,item_prices.id as price_id, item_prices.sell_price, COALESCE(purchase_price_info.purchase_price, 0) AS purchase_price, COALESCE(items.point_ratio, item_categories.point_ratio) AS point_ratio');

            if($request->input('order_by') && $request->input('order')){
                switch ($request->input('order_by')) {
                    case "item_name":
                        $order_by = "items.name";
                        break;
                    case "sell_price":
                        $order_by = "item_prices.sell_price";
                        break;
                    case "created_date":
                        $order_by = "items.created_datetime";
                        break;
                    case "purchase_price":
                        $order_by = "purchase_price_info.purchase_price";
                        break;
                }
                $order = $request->input('order');
                $item = $item->orderBy($order_by, $order);
            } else {
                $item = $item->orderBy('items.category_id', 'asc');
                $item = $item->orderBy('items.id', 'asc');
            }

            if($request->input('item_id') || $request->input('is_get_all')){
                $item = $item->get();
            } else {
                $item = $item->paginate(10);
            }
            // $item = $request->input('item_id')? $item->get():$item->paginate(10);

            $state ='success';
            $errors = 'none';
            return response()->json(compact('state', 'errors', 'data', 'item'));
        } else {
            $state = 'fail';
            if(!$check_permission){
                $errors = 'You dont have the permission to do this';
            } else {
                $errors = $validator->errors();
            }
            // $errors = !$check_permission? 'You dont have the permission to do this': $validator->errors();
            return response()->json(compact('state', 'errors', 'data'));
        }
    }

    public function editItem(Request $request, $store_id, $branch_id)
    {
        $user = auth()->user();
        $data = $request->except('token');
        $rules = [
            'category_id'   => ['required',
                                Rule::exists('item_categories', 'id')
                                    ->where('branch_id', $branch_id)
                                    ->where('deleted',0)],
            'item_id'       => 'required',
            'item_name'     => 'required',
            'image'         => 'file',
            'bar_code'      => '',
            'quantity'      => '',
            'sell_price'    => 'required',
            'purchase_price'=> '',
            'point_ratio'   => '',
        ];
        $validator = Validator::make($data, $rules);
        $check_permission = FrequentQuery::checkPermission($user->id, $branch_id, ['selling', 'purchasing']);
        $current_date =  date('Y-m-d');

        if($check_permission && !$validator->fails()){
            $item = FrequentQuery::getItemInfo($branch_id);
            $item = $item->where('items.id',$request->input('item_id'));

            $img_error_flag = 0;
            DB::transaction(function () use ($request, $item, $current_date, &$img_error_flag, $branch_id, $store_id, $user) {
                $item->update([
                    'items.category_id'         => $request->input('category_id'),
                    'items.name'                => $request->input('item_name'),
                    'items.bar_code'            => $request->input('bar_code'),
                    'items.category_id'         => $request->input('category_id'),
                    'items.point_ratio'         => $request->input('point_ratio'),
                    'item_quantities.quantity'   => $request->input('quantity'),
                ]);

                $item_price = FrequentQuery::getItemInfo($branch_id);
                $item_price = $item_price->where('items.id',$request->input('item_id'))
                                ->select('item_prices.sell_price')
                                ->first();
                if($item_price->sell_price != $request->input('sell_price')){
                        $item->update([
                            'item_prices.end_date'  => $current_date
                        ]);
                        DB::table('item_prices')->insert([
                            'item_id'       => $request->input('item_id'),
                            'sell_price'    => $request->input('sell_price'),
                            'change_by'     => $user->id,
                        ]);
                }

                if ($request->hasFile('image')){
                    $image = $request->image;
                    $ext = $image->getClientOriginalExtension();
                    if ($ext == 'png' || $ext == 'jpg'){
                        $item_id = $request->input('item_id');
                        $img_url = 'upload/store_id_'.$store_id.'/branch_id_'.$branch_id.'/';
                        $result = DB::table('items')->where('id',$item_id)->where('deleted', 0)
                            ->update([
                                'image_url' =>  $img_url.$item_id.'.'.$ext,
                            ]);

                        $image->move('../public/'.$img_url,$item_id.'.'.$ext);
                    } else {
                        $img_error_flag = 1;
                    }
                }
                //update pruchase_price
                $old_purchase_price = FrequentQuery::getItemInfo($branch_id);
                $purchase_price_info = FrequentQuery::getLatestPurchasedPrice();
                $old_purchase_price = $old_purchase_price->leftJoinSub($purchase_price_info, 'purchase_price_info', function ($join){
                                                            $join->on('purchase_price_info.item_id', '=','items.id');
                                                        });
                $old_purchase_price = $old_purchase_price->where('items.id', $request->input('item_id'))
                                                        ->selectRaw('purchase_price_info.purchase_price')
                                                        ->first();
                $old_purchase_price = $old_purchase_price->purchase_price;
                $new_purchase_price = $request->input('purchase_price');
                if(($old_purchase_price === null || $old_purchase_price != $new_purchase_price) && $new_purchase_price !== null){
                    //update purchase price of item by create a new purchased_sheet and purchased_item with quantity = 0
                    $purchased_sheet_id = DB::table('purchased_sheets')->insertGetId([
                        'branch_id'             => $branch_id,
                        'purchaser_id'          => $user->id,
                        'supplier_id'           => null,
                        'total_purchase_price'  => 0,
                        'discount'              => 0,
                        'deliver_name'          => null,
                    ]);
                    $purchased_item_id = DB::table('purchased_items')->insertGetId([
                        'purchased_sheet_id'    => $purchased_sheet_id,
                        'item_id'               => $request->input('item_id'),
                        'purchase_price'        => $new_purchase_price,
                        'quantity'              => 0
                    ]);
                }
            });
            if($img_error_flag){
                $validator->errors()->add('image', 'Choose an image has extension .png or .jpg');
                $state = 'fail';
                $errors = $validator->errors();
                return response()->json(compact('state', 'errors', 'data'));
            } else {
                $state = 'success';
                $errors = 'none';
                return response()->json(compact('state', 'errors', 'data'));
            }
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

    public function deleteItem(Request $request, $store_id, $branch_id) {
        $user = auth()->user();
        $data = $request->except('token');
        $rules = [
            'item_id'       => ['required',Rule::exists('items', 'id')->where('deleted',0)],
        ];
        $validator = Validator::make($data, $rules);
        $check_permission = FrequentQuery::checkPermission($user->id, $branch_id, ['purchasing']);
        $current_date =  date('Y-m-d');


        if($check_permission && !$validator->fails()){
            $item = FrequentQuery::getItemInfo($branch_id);
            $item = $item->where('items.id',$request->input('item_id'));
            DB::transaction(function () use ($item, $current_date) {
                $item->update([
                    'items.deleted'         => 1,
                    'item_prices.end_date'  => $current_date
                ]);
            });

            $state = 'success';
            $errors = 'none';
            return response()->json(compact('state', 'errors', 'data'));
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

    public function searchItemByBarcode(Request $request, $store_id, $branch_id){
        $user = auth()->user();
        $data = $request->except('token');
        $rules = [
            'bar_code'  => 'required',
        ];
        $validator = Validator::make($data, $rules);
        $check_permission = FrequentQuery::checkPermission($user->id, $branch_id, ['purchasing', 'selling']);

        if($check_permission && !$validator->fails()){
            $item = FrequentQuery::getItemInfo($branch_id);
            $item = $item->where('items.bar_code','=',$request->input('bar_code'));
            $item = $item->selectRaw('items.id AS item_id, items.name AS item_name, items.bar_code, items.image_url, items.created_datetime, item_categories.id AS category_id, item_categories.name as category_name, item_quantities.quantity,item_prices.id as price_id, item_prices.sell_price');
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

    public function checkItemWithZeroOrNullPurchasePrice(Request $request, $store_id, $branch_id){
        $user = auth()->user();
        $data = $request->except('token');
        // $rules = [
        //     'bar_code'  => 'required',
        // ];
        // $validator = Validator::make($data, $rules);
        $check_permission = FrequentQuery::checkPermission($user->id, $branch_id, ['purchasing']);

        if($check_permission){
            $purchase_price_info = FrequentQuery::getLatestPurchasedPrice();
            $item = FrequentQuery::getItemInfo($branch_id);
            $item = $item->leftJoinSub($purchase_price_info, 'purchase_price_info', function ($join){
                            $join->on('purchase_price_info.item_id', '=','items.id');
                        });
            $item = $item->where(function($query){
                                    $query->where('purchase_price_info.purchase_price', '<=', 0);
                                    $query->orWhereNull('purchase_price_info.purchase_price');
                                });
            $item = $item->selectRaw('items.id AS item_id, items.name AS item_name, items.bar_code, items.image_url, items.created_datetime, item_categories.id AS category_id, item_categories.name as category_name, item_quantities.quantity,item_prices.id as price_id, item_prices.sell_price, COALESCE(purchase_price_info.purchase_price, 0) AS purchase_price, COALESCE(items.point_ratio, item_categories.point_ratio) AS point_ratio')
                        ->get();
            $state = 'success';
            $errors = 'none';
            return response()->json(compact('state', 'errors', 'data', 'item'));
        } else {
            $state = 'fail';
            if(!$check_permission){
                $errors = 'You dont have the permission to do this';
            }
            // $errors = !$check_permission? 'You dont have the permission to do this':$validator->errors();
            return response()->json(compact('state', 'errors', 'data'));
        }
    }
}
