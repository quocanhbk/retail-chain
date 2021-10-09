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

class InvoiceController extends Controller
{
    public function createInvoice(Request $request, $store_id, $branch_id)
    {
        /*
            invoice : {
                customer_id : int,
                customer_phone : optional, for offline mode,
                customer_point : int,
                status : reserve hoặc success,
                discount : int,
                created_datetime: Y-m-d H:i:s, optional
            },
            invoice_items : [
                {
                    price_id : int,
                    quantity : int,
                    point_ratio : float,
                },....
            ]
        */
        $user = auth()->user();
        $data = $request->except('token');
        $rules = [
            'invoice'       => 'required',
            'invoice_items'  => 'required'
        ];
        $validator = Validator::make($data, $rules);

        $check_permission = FrequentQuery::checkPermission($user->id, $branch_id, ['selling']);

        if($check_permission && !$validator->fails()){
            $invoice =   $request->input('invoice');
            $invoice_items =  $request->input('invoice_items');
            $total_sell_price = 0;
            $total_point_gain = 0;

            if(count($invoice_items) > 0){
                //check if quant is valid
                if($invoice['status'] == "success"){
                    foreach($invoice_items as  $invoice_item){
                        $item_info_table = FrequentQuery::getItemInfo($branch_id);
                        $item = $item_info_table->select('items.id', 'item_prices.id AS price_id', 'item_prices.sell_price', 'item_quantities.quantity')
                                                ->where('item_prices.id', $invoice_item['price_id'])
                                                ->first();
                        if($item !== null){
                            if($item->quantity === null){
                                $point_ratio = FrequentQuery::getPointRatio($item->id, $branch_id);
                                $total_sell_price += $item->sell_price*$invoice_item['quantity'];
                                $total_point_gain += $total_sell_price*$point_ratio;
                            } else if($item->quantity < $invoice_item['quantity']){
                                $state = 'fail';
                                $errors = 'Item quantity with price_id '.$item->price_id.' only has '.$item->quantity.' while you are asking for '.$invoice_item['quantity'];
                                return response()->json(compact('state', 'errors', 'data', 'item'));
                            } else {
                                $point_ratio = FrequentQuery::getPointRatio($item->id, $branch_id);
                                $total_sell_price += $item->sell_price*$invoice_item['quantity'];
                                $total_point_gain += $total_sell_price*$point_ratio;
                            }
                        } else {
                            $state = 'fail';
                            $errors = 'Item with price_id '.$invoice_item['price_id'].' doesnt belong to branch_id '.$branch_id;
                            return response()->json(compact('state', 'errors', 'data'));
                        }
                    }
                }
                //check if customer exist and add point to customer
                $customer_id = null;
                if(isset($invoice['customer_id']) || isset($invoice['customer_phone'])){
                    $customer = DB::table('customers')->where('branch_id', $branch_id)
                                ->where(function ($query) use($invoice){
                                    $query=isset($invoice['customer_id'])? $query->orWhere('id', $invoice['customer_id']) : $query;
                                    $query=isset($invoice['customer_phone'])? $query->orWhere('phone', $invoice['customer_phone']) : $query;
                                })
                                ->where('deleted',0);
                    if($customer->exists()){
                        $customer_data = $customer->selectRaw('customers.point, customers.id')->first();
                        $customer_point = $customer_data->point;
                        $customer_id = $customer_data->id;
                        if($customer_point >= $invoice['customer_point'] || $customer_point < 0){
                            DB::transaction(function () use ($invoice, $customer, $customer_point, $total_sell_price, $total_point_gain) {
                                $customer->update([
                                    'point' => $customer_point + round($total_point_gain) - $invoice['customer_point']
                                ]);
                            });
                        } else {
                            $state = 'fail';
                            $errors = 'Customer only has '.$customer_point.' point while invoice is asking for '.$invoice['customer_point'].' point';
                            return response()->json(compact('state', 'errors', 'data'));
                        }
                    }
                }

                $created_invoice_id = 0;
                DB::transaction(function () use ($invoice, $invoice_items, $branch_id, $user, $data, &$created_invoice_id, $total_sell_price,$customer_id) {
                    //start insert
                    if(isset($invoice['created_datetime'])){
                        $invoice_id = DB::table('invoices')->insertGetId([
                            'branch_id'             => $branch_id,
                            'seller_id'             => $user->id,
                            'customer_id'           => $customer_id,
                            'total_sell_price'      => $total_sell_price,
                            'discount'              => $invoice['discount'],
                            'status'                => $invoice['status'],
                            'created_datetime'      => $invoice['created_datetime']
                        ]);
                    } else {
                        $invoice_id = DB::table('invoices')->insertGetId([
                            'branch_id'             => $branch_id,
                            'seller_id'             => $user->id,
                            'customer_id'           => $customer_id,
                            'total_sell_price'      => $total_sell_price,
                            'discount'              => $invoice['discount'],
                            'status'                => $invoice['status'],
                        ]);
                    }
                    $created_invoice_id = $invoice_id;
                    //insert invoice_items
                    foreach($invoice_items as $invoice_item){
                        // $invoice_item = (object) $invoice_item;
                        $item_info_table = FrequentQuery::getItemInfo($branch_id);
                        $item_info = $item_info_table->select('item_prices.id', 'item_quantities.quantity');
    
                        $item = $item_info->where('item_prices.id', $invoice_item['price_id']);
                        $item = $item->first();
                        DB::table('invoice_items')->insert([
                            'invoice_id'    => $invoice_id,
                            'price_id'      => $invoice_item['price_id'],
                            'quantity'      => $invoice_item['quantity'],
                            'point_ratio'   => $invoice_item['point_ratio'],
                        ]);
                        if($item->quantity !== null && $invoice['status'] == "success"){
                            $item_info_table = FrequentQuery::getItemInfo($branch_id);
                            $item_info_table->where('item_prices.id', $invoice_item['price_id'])
                                            ->update([
                                                'item_quantities.quantity'   => $item->quantity - $invoice_item['quantity'],
                                            ]);
                        }
                    }
                });
                //get invoice data to print
                $created_invoice = DB::table('invoices')->where('invoices.branch_id', $branch_id)->where('invoices.id', $created_invoice_id)
                                        ->leftJoin('customers','customers.id','=','invoices.customer_id')
                                        ->leftJoin('users','users.id','=','invoices.seller_id')
                                        ->selectRaw('invoices.id AS invoice_id, invoices.total_sell_price, invoices.discount, invoices.created_datetime, invoices.status, invoices.customer_id, customers.name AS customer_name, invoices.seller_id, users.name AS seller_name')
                                        ->get();
                $created_invoice_items = FrequentQuery::getInvoiceItemInfo($branch_id, $created_invoice_id);
                $created_invoice_items = $created_invoice_items->selectRaw('items.name, item_prices.sell_price, invoice_items.quantity')->get();
            } else {
                $state ='fail';
                $errors = 'invoice_items cannot be an empty array';
                return response()->json(compact('state', 'errors', 'data'));
            }

            $state ='success';
            $errors = 'none';
            return response()->json(compact('state', 'errors', 'data', 'created_invoice', 'created_invoice_items'));
        } else {
            $state = 'fail';
            $errors = !$check_permission? 'You dont have the permission to do this': $validator->errors();
            return response()->json(compact('state', 'errors', 'data'));
        }
    }

    public function getInvoice(Request $request, $store_id, $branch_id)
    {
        $user = auth()->user();
        $data = $request->except('token');
        $rules = [
            'invoice_id'            => ['integer'],
            'customer_id'           => ['integer'],
            'seller_id'             => ['integer'],
            'order_by'              => 'required_with:order|in:customer_name,total_sell_price,created_datetime,seller_name,discount',
            'order'                 => 'required_with:order_by|in:asc,desc',

            'keyword'               => 'nullable',
            'total_sell_price_from' => 'nullable',
            'total_sell_price_to'   => 'nullable',
            'created_datetime_from' => 'nullable|date_format:Y-m-d',
            'created_datetime_to'   => 'nullable|date_format:Y-m-d',
        ];
        $validator = Validator::make($data, $rules);

        $check_permission = FrequentQuery::checkPermission($user->id, $branch_id, ['selling']);

        if($check_permission && !$validator->fails()){
            $invoice = DB::table('invoices')->where('invoices.branch_id', $branch_id)
                            ->leftJoin('customers','customers.id','=','invoices.customer_id')
                            ->leftJoin('users','users.id','=','invoices.seller_id')
                            ->where('invoices.total_sell_price','>',0);

            $invoice = $request->input('invoice_id')? $invoice->where('invoices.id', $request->input('invoice_id')) : $invoice;
            $invoice = $request->input('customer_id')? $invoice->where('invoices.customer_id', $request->input('customer_id')) : $invoice;
            $invoice = $request->input('seller_id')? $invoice->where('invoices.seller_id', $request->input('seller_id')) : $invoice;
            $invoice = $request->input('keyword')? $invoice->where(function ($query) use($request){
                                                    $query->where('customers.name','like', '%'.$request->input('keyword').'%');
                                                    $query->orWhere('customers.phone','like', '%'.$request->input('keyword').'%');
                                                }) : $invoice;
                                                
            $invoice = $request->input('total_sell_price_from')? 
                            $invoice->where('invoices.total_sell_price', '>=',$request->input('total_sell_price_from')) 
                            : $invoice;                                                
            $invoice = $request->input('total_sell_price_to')? 
                            $invoice->where('invoices.total_sell_price', '<=',$request->input('total_sell_price_to')) 
                            : $invoice;                                                
            $invoice = $request->input('created_datetime_from')? 
                            $invoice->where('invoices.created_datetime', '>=',$request->input('created_datetime_from')." 00:00:00") 
                            : $invoice;                                                
            $invoice = $request->input('created_datetime_to')? 
                            $invoice->where('invoices.created_datetime', '<=',$request->input('created_datetime_to')." 23:59:59") 
                            : $invoice;

            $invoice = $invoice->selectRaw('invoices.id AS invoice_id, invoices.total_sell_price, invoices.discount, invoices.created_datetime, invoices.status, invoices.customer_id, customers.name AS customer_name, customers.phone AS customer_phone, invoices.seller_id, users.name AS seller_name');

            if($request->input('order_by') && $request->input('order')){
                switch ($request->input('order_by')) {
                    case "customer_name":
                        $order_by = "customers.name";
                        break;
                    case "total_sell_price":
                        $order_by = "invoices.total_sell_price";
                        break;
                    case "created_datetime":
                        $order_by = "invoices.created_datetime";
                        break;
                    case "seller_name":
                        $order_by = "users.name";
                        break;
                    case "discount":
                        $order_by = "invoices.discount";
                        break;
                }
                $order = $request->input('order');
                $invoice = $invoice->orderBy($order_by, $order);
            } else {
                $invoice = $invoice->orderByRaw('invoices.created_datetime DESC');
            }
            $invoice = $invoice->paginate(10);

            $state ='success';
            $errors = 'none';
            return response()->json(compact('state', 'errors', 'data', 'invoice'));
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
    
    public function getInvoiceDetail(Request $request, $store_id, $branch_id)
    {
        $user = auth()->user();
        $data = $request->except('token');
        $rules = [
            'invoice_id'    => ['required','integer',Rule::exists('invoices','id')->where('branch_id', $branch_id)],
        ];
        $validator = Validator::make($data, $rules);

        $check_permission = FrequentQuery::checkPermission($user->id, $branch_id, ['selling']);

        if($check_permission && !$validator->fails()){
            $invoice = FrequentQuery::getInvoiceItemInfo($branch_id, $request->input('invoice_id'));
            $invoice = $invoice->where('invoice_items.quantity','>',0);

            $invoice = $invoice->selectRaw('invoices.id AS invoice_id, invoice_items.id as invoice_item_id, item_prices.item_id,items.name, item_prices.sell_price, invoice_items.quantity, items.image_url, invoice_items.point_ratio');
            $invoice = $invoice->get();

            $state ='success';
            $errors = 'none';
            return response()->json(compact('state', 'errors', 'data', 'invoice'));
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