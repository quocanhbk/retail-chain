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

class PurchasedSheetController extends Controller
{
    public function createPurchasedSheet(Request $request, $store_id, $branch_id)
    {
        /*
            purchased_sheet : {
                supplier_id : int,
                discount : int,
                deliver_name : varchar
            },
            purchased_items : [
                {
                    item_id : int,
                    quantity : int,
                    purchase_price : int
                },....
            ]
        */
        $user = auth()->user();
        $data = $request->except('token');
        $rules = [
            'purchased_sheet'   => 'required',
            'purchased_items'   => 'required'
        ];
        $validator = Validator::make($data, $rules);

        $check_permission = FrequentQuery::checkPermission($user->id, $branch_id, ['purchasing']);

        if($check_permission && !$validator->fails()){
            $purchased_sheet =  $request->input('purchased_sheet');
            $purchased_items =  $request->input('purchased_items');
            $total_purchase_price = 0;

            foreach($purchased_items as  $purchased_item){
                $item_info_table = FrequentQuery::getItemInfo($branch_id);
                $item = $item_info_table->where('items.id', $purchased_item['item_id']);
                $item = $item->first();

                if($item === null){
                    $state ='fail';
                    $errors = 'Item with id '.$purchased_item['item_id'].' doesnt belong to branch_id: '.$branch_id;
                    return response()->json(compact('state', 'errors', 'data'));
                } else {
                    $total_purchase_price += $purchased_item['purchase_price']*$purchased_item['quantity'];
                }
            }
            $purchased_sheet_id = 0;
            DB::transaction(function () use ($purchased_sheet, $purchased_items, $branch_id, $user, &$item_flag, $total_purchase_price, &$purchased_sheet_id) {
                //insert purchased_sheet
                $purchased_sheet_id = DB::table('purchased_sheets')->insertGetId([
                    'branch_id'             => $branch_id,
                    'purchaser_id'          => $user->id,
                    'supplier_id'           => $purchased_sheet['supplier_id'],
                    'total_purchase_price'  => $total_purchase_price,
                    'discount'              => $purchased_sheet['discount'],
                    'deliver_name'          => $purchased_sheet['deliver_name'],
                ]);
                foreach($purchased_items as  $purchased_item){
                    $item_info_table = FrequentQuery::getItemInfo($branch_id);
                    $item = $item_info_table->where('items.id', $purchased_item['item_id']);
                    $item = $item->first();
    
                    if($item !== null){
                        //insert purchased_item
                        $purchased_item_id = DB::table('purchased_items')->insertGetId([
                            'purchased_sheet_id'    => $purchased_sheet_id,
                            'item_id'               => $purchased_item['item_id'],
                            'purchase_price'        => $purchased_item['purchase_price'],
                            'quantity'              => $purchased_item['quantity']
                        ]);
                        //update quantity of corresponding item
                        $item_info_table = FrequentQuery::getItemInfo($branch_id);
                        $item_info_table->where('items.id', $purchased_item['item_id'])
                                        ->update([
                                            'item_quantities.quantity'   => $item->quantity + $purchased_item['quantity'],
                                        ]);
                    }
                }
            });
            
            $purchased_sheet = DB::table('purchased_sheets')->where('purchased_sheets.branch_id', $branch_id)
                                ->leftJoin('users','users.id','=','purchased_sheets.purchaser_id')
                                ->leftJoin('suppliers','suppliers.id','=','purchased_sheets.supplier_id')
                                ->leftJoin('purchased_items', 'purchased_items.purchased_sheet_id', '=', 'purchased_sheets.id')
                                ->where('purchased_items.quantity', '>', 0);
            $purchased_sheet = $purchased_sheet->where('purchased_sheets.id', $purchased_sheet_id);
            $purchased_sheet = $purchased_sheet->selectRaw('purchased_sheets.id AS purchased_sheet_id, purchased_sheets.deliver_name, purchased_sheets.total_purchase_price, purchased_sheets.discount, purchased_sheets.delivery_datetime, purchased_sheets.supplier_id, suppliers.name AS supplier_name, suppliers.phone AS supplier_phone, purchased_sheets.purchaser_id, users.name AS purchaser_name');
            $purchased_sheet = $purchased_sheet->groupByRaw('purchased_sheets.id, purchased_sheets.deliver_name, purchased_sheets.total_purchase_price, purchased_sheets.discount, purchased_sheets.delivery_datetime, purchased_sheets.supplier_id, suppliers.name, suppliers.phone, purchased_sheets.purchaser_id, users.name');
            $purchased_sheet = $purchased_sheet->get();

            $purchased_sheet_items =DB::table('purchased_sheets')->where('purchased_sheets.branch_id', $branch_id)->where('purchased_sheets.id', $purchased_sheet_id)
                                        ->leftJoin('purchased_items', 'purchased_items.purchased_sheet_id','=','purchased_sheets.id')
                                        ->leftJoin('items','items.id','=','purchased_items.item_id');
            $purchased_sheet_items = $purchased_sheet_items->where('purchased_items.quantity', '>', 0); 
            $purchased_sheet_items = $purchased_sheet_items->selectRaw('purchased_sheets.id AS purchased_sheet_id, purchased_items.id AS purchased_item_id, purchased_items.item_id, items.name, purchased_items.purchase_price, purchased_items.quantity, items.image_url');
            $purchased_sheet_items = $purchased_sheet_items->get();


            $state ='success';
            $errors = 'none';
            return response()->json(compact('state', 'errors', 'data', 'purchased_sheet', 'purchased_sheet_items'));
        } else {
            $state = 'fail';
            $errors = !$check_permission? 'You dont have the permission to do this': $validator->errors();
            return response()->json(compact('state', 'errors', 'data'));
        }
    }

    public function getPurchasedSheet(Request $request, $store_id, $branch_id)
    {
        $user = auth()->user();
        $data = $request->except('token');
        $rules = [
            'purchased_sheet_id'        => ['integer'],
            'purchaser_id'              => ['integer'],
            'supplier_id'               => ['integer'],
            'order_by'                  => 'required_with:order|in:supplier_name,total_purchase_price,created_datetime,purchaser_name,discount',
            'order'                     => 'required_with:order_by|in:asc,desc',

            'keyword'                   => 'nullable',
            'total_purchase_price_from' => 'nullable',
            'total_purchase_price_to'   => 'nullable',
            'created_datetime_from'     => 'nullable|date_format:Y-m-d',
            'created_datetime_to'       => 'nullable|date_format:Y-m-d',
        ];
        $validator = Validator::make($data, $rules);

        $check_permission = FrequentQuery::checkPermission($user->id, $branch_id, ['purchasing']);

        if($check_permission && !$validator->fails()){
            $purchased_sheet = DB::table('purchased_sheets')->where('purchased_sheets.branch_id', $branch_id)
                                ->leftJoin('users','users.id','=','purchased_sheets.purchaser_id')
                                ->leftJoin('suppliers','suppliers.id','=','purchased_sheets.supplier_id')
                                ->leftJoin('purchased_items', 'purchased_items.purchased_sheet_id', '=', 'purchased_sheets.id')
                                ->where('purchased_items.quantity', '>', 0);

            $purchased_sheet = $request->input('purchased_sheet_id')? $purchased_sheet->where('purchased_sheets.id', $request->input('purchased_sheet_id')) : $purchased_sheet;
            $purchased_sheet = $request->input('purchaser_id')? $purchased_sheet->where('purchased_sheets.purchaser_id', $request->input('purchaser_id')) : $purchased_sheet;
            $purchased_sheet = $request->input('supplier_id')? $purchased_sheet->where('purchased_sheets.supplier_id', $request->input('supplier_id')) : $purchased_sheet;
            $purchased_sheet = $request->input('keyword')? $purchased_sheet->where(function ($query) use($request){
                                                    $query->where('suppliers.name','like', '%'.$request->input('keyword').'%');
                                                }) : $purchased_sheet;

            $purchased_sheet = $request->input('total_purchase_price_from')? 
                                    $purchased_sheet->where('purchased_sheets.total_purchase_price', '>=',$request->input('total_purchase_price_from')) 
                                    : $purchased_sheet;  
            $purchased_sheet = $request->input('total_purchase_price_to')? 
                                    $purchased_sheet->where('purchased_sheets.total_purchase_price', '<=',$request->input('total_purchase_price_to')) 
                                    : $purchased_sheet;  
            $purchased_sheet = $request->input('created_datetime_from')? 
                                    $purchased_sheet->where('purchased_sheets.delivery_datetime', '>=',$request->input('created_datetime_from')." 00:00:00") 
                                    : $purchased_sheet;  
            $purchased_sheet = $request->input('created_datetime_to')? 
                                    $purchased_sheet->where('purchased_sheets.delivery_datetime', '<=',$request->input('created_datetime_to')." 23:59:59") 
                                    : $purchased_sheet;  


            $purchased_sheet = $purchased_sheet->selectRaw('purchased_sheets.id AS purchased_sheet_id, purchased_sheets.deliver_name, purchased_sheets.total_purchase_price, purchased_sheets.discount, purchased_sheets.delivery_datetime, purchased_sheets.supplier_id, suppliers.name AS supplier_name, suppliers.phone AS supplier_phone, purchased_sheets.purchaser_id, users.name AS purchaser_name');
            $purchased_sheet = $purchased_sheet->groupByRaw('purchased_sheets.id, purchased_sheets.deliver_name, purchased_sheets.total_purchase_price, purchased_sheets.discount, purchased_sheets.delivery_datetime, purchased_sheets.supplier_id, suppliers.name, suppliers.phone, purchased_sheets.purchaser_id, users.name');

            if($request->input('order_by') && $request->input('order')){
                switch ($request->input('order_by')) {
                    case "supplier_name":
                        $order_by = "suppliers.name";
                        break;
                    case "total_purchase_price":
                        $order_by = "purchased_sheets.total_purchase_price";
                        break;
                    case "created_datetime":
                        $order_by = "purchased_sheets.delivery_datetime";
                        break;
                    case "purchaser_name":
                        $order_by = "users.name";
                        break;
                    case "discount":
                        $order_by = "purchased_sheets.discount";
                        break;
                }
                $order = $request->input('order');
                $purchased_sheet = $purchased_sheet->orderBy($order_by, $order);
            } else {
                $purchased_sheet = $purchased_sheet->orderByRaw('purchased_sheets.delivery_datetime DESC');
            }
            $purchased_sheet = $purchased_sheet->paginate(10);
            
            $state ='success';
            $errors = 'none';
            return response()->json(compact('state', 'errors', 'data', 'purchased_sheet'));
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
    
    public function getPurchasedSheetDetail(Request $request, $store_id, $branch_id)
    {
        $user = auth()->user();
        $data = $request->except('token');
        $rules = [
            'purchased_sheet_id'    => ['required','integer',Rule::exists('purchased_sheets','id')->where('branch_id', $branch_id)],
        ];
        $validator = Validator::make($data, $rules);

        $check_permission = FrequentQuery::checkPermission($user->id, $branch_id, ['purchasing']);

        if($check_permission && !$validator->fails()){
            $purchased_sheet =DB::table('purchased_sheets')->where('purchased_sheets.branch_id', $branch_id)->where('purchased_sheets.id', $request->input('purchased_sheet_id'))
                                ->leftJoin('purchased_items', 'purchased_items.purchased_sheet_id','=','purchased_sheets.id')
                                ->leftJoin('items','items.id','=','purchased_items.item_id');
            
            $purchased_sheet = $purchased_sheet->where('purchased_items.quantity', '>', 0); 

            $purchased_sheet = $purchased_sheet->selectRaw('purchased_sheets.id AS purchased_sheet_id, purchased_items.id AS purchased_item_id, purchased_items.item_id, items.name, purchased_items.purchase_price, purchased_items.quantity, items.image_url');
            $purchased_sheet = $purchased_sheet->get();
            
            $state ='success';
            $errors = 'none';
            return response()->json(compact('state', 'errors', 'data', 'purchased_sheet'));
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