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

class RefundSheetController extends Controller
{
    public function createRefundSheet(Request $request, $store_id, $branch_id)
    {
        /*
            refund_sheet : {
                invoice_id : int,
                reason : string
            },
            refund_items : [
                {
                    invoice_item_id : int,
                    quantity : int
                },....
            ]
        */
        $user = auth()->user();
        $data = $request->except('token');
        $rules = [
            'refund_sheet'   => 'required',
            'refund_items'   => 'required'
        ];
        $validator = Validator::make($data, $rules);

        $refund_sheet =  $request->input('refund_sheet');
        $refund_items =  $request->input('refund_items');

        $check_permission = FrequentQuery::checkPermission($user->id, $branch_id, ['selling']);
        $check_invoice_id = DB::table('invoices')->where('invoices.branch_id',$branch_id)->where('invoices.id',$refund_sheet['invoice_id'])->exists();

        if($check_permission && $check_invoice_id && !$validator->fails()){
            $total_refund_price = 0;
            $total_reduce_point = 0;

            $invoice = DB::table('invoices')->where('invoices.branch_id',$branch_id)->where('invoices.id',$refund_sheet['invoice_id'])
                            ->selectRaw('invoices.customer_id, invoices.status')
                            ->first();
            $customer_id = $invoice->customer_id;
            $invoice_status = $invoice->status;

            if(count($refund_items) > 0 && $invoice_status == "success"){
                //check invoice_item_id
                foreach($refund_items as $refund_item){
                    $invoice_item = FrequentQuery::getInvoiceItemInfo($branch_id, $refund_sheet['invoice_id']);
                    $invoice_item = $invoice_item->where('invoice_items.id', $refund_item['invoice_item_id'])
                                        ->selectRaw('invoice_items.quantity, item_prices.sell_price, invoice_items.point_ratio')
                                        ->first();
                    if($invoice_item !== null){
                        if($invoice_item->quantity >= $refund_item['quantity']){
                            $total_refund_price += $invoice_item->sell_price * $refund_item['quantity'];
                            $total_reduce_point += $total_refund_price * $invoice_item->point_ratio;
                        } else {
                            $state ='false';
                            $errors = "invoice_item_id: ".$refund_item['invoice_item_id']." only has ".$invoice_item->quantity." while you are asking for ".$refund_item['quantity'];
                            return response()->json(compact('state', 'errors', 'data'));
                        }
                    } else {
                        $state ='false';
                        $errors = "invoice_item_id: ".$refund_item['invoice_item_id']." doesnt exist";
                        return response()->json(compact('state', 'errors', 'data'));
                    }
                }
    
                DB::transaction(function () use ($branch_id, $user, $refund_sheet, $refund_items, $total_refund_price, $total_reduce_point,$customer_id){
                    //insert refund_sheet
                    $refund_sheet_id = DB::table('refund_sheets')->insertGetId([
                        'branch_id'             => $branch_id,
                        'refunder_id'           => $user->id,
                        'invoice_id'            => $refund_sheet['invoice_id'],
                        'reason'                => $refund_sheet['reason'],
                        'total_refund_price'    => $total_refund_price  
                    ]);
                    //update corresponding invoice total_sell_value
                    $invoice = DB::table('invoices')->where('invoices.id', $refund_sheet['invoice_id'])->select('total_sell_price')->first();
                    DB::table('invoices')->where('invoices.id', $refund_sheet['invoice_id'])
                        ->update([
                            'total_sell_price'  => $invoice->total_sell_price - $total_refund_price
                        ]);
                    
                    foreach($refund_items as $refund_item){
                        //insert refund_item
                        DB::table('refund_items')->insert([
                            'refund_sheet_id'   => $refund_sheet_id,
                            'invoice_item_id'   => $refund_item['invoice_item_id'],
                            'quantity'          => $refund_item['quantity'],
                        ]);
                        //update quant of corresponding item and invoice_item
                        $item = FrequentQuery::getInvoiceItemInfo($branch_id, $refund_sheet['invoice_id']);
                        $item = $item->where('invoice_items.id', $refund_item['invoice_item_id'])
                                    ->selectRaw('item_quantities.quantity, invoice_items.quantity AS invoice_quantity')
                                    ->first();
                        $update_item = FrequentQuery::getInvoiceItemInfo($branch_id, $refund_sheet['invoice_id']);
                        $update_item = $update_item->where('invoice_items.id', $refund_item['invoice_item_id']);
                        $update_item = $update_item->update([
                            'item_quantities.quantity'  => $item->quantity + $refund_item['quantity'],
                            'invoice_items.quantity'    => $item->invoice_quantity - $refund_item['quantity'],
                        ]);
                    }
                    //update customer point
                    if($customer_id){
                        $customer = DB::table('customers')->where('branch_id', $branch_id)
                                    ->where('id', $customer_id)
                                    ->where('deleted',0);
                        if($customer->exists()){
                            $customer_point = $customer->select('point')->first();
                            $customer_point = $customer_point->point;
                            $customer->update([
                                'point' => $customer_point - $total_reduce_point
                            ]);
                        }
                    }
                });
            } else {
                $state ='fail';
                if(!$check_permission){
                    $errors = 'You dont have the permission to do this';
                } else if(!$check_invoice_id){
                    $errors = "invoice_id doesnt belong to branch";
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
            } else if (!$check_invoice_id){
                $errors = "invoice_id: ".$refund_sheet['invoice_id']." doesnt belong to branch_id: $branch_id";
            } else {
                $errors = $validator->errors();
            }
            return response()->json(compact('state', 'errors', 'data'));
        }
    }

    public function getRefundSheet(Request $request, $store_id, $branch_id)
    {
        $user = auth()->user();
        $data = $request->except('token');
        $rules = [
            'refund_sheet_id'           => ['integer'],
            'invoice_id'                => ['integer'],
            'refunder_id '              => ['integer'],
            'order_by'                  => 'required_with:order|in:customer_name,total_refund_price,created_datetime,refunder_name',
            'order'                     => 'required_with:order_by|in:asc,desc',

            'keyword'                   => 'nullable',
            'total_refund_price_from'   => 'nullable',
            'total_refund_price_to'     => 'nullable',
            'created_datetime_from'     => 'nullable|date_format:Y-m-d',
            'created_datetime_to'       => 'nullable|date_format:Y-m-d',
        ];
        $validator = Validator::make($data, $rules);

        $check_permission = FrequentQuery::checkPermission($user->id, $branch_id, ['selling']);

        if($check_permission && !$validator->fails()){
            $refund_sheet = DB::table('refund_sheets')->where('refund_sheets.branch_id',$branch_id)
                                ->leftJoin('invoices','invoices.id','=','refund_sheets.invoice_id')
                                ->leftJoin('customers','customers.id','=','invoices.customer_id')
                                ->leftJoin('users','users.id','=','refund_sheets.refunder_id');

            $refund_sheet = $request->input('refund_sheet_id')? $refund_sheet->where('refund_sheets.id',$request->input('refund_sheet_id')) : $refund_sheet;
            $refund_sheet = $request->input('invoice_id')? $refund_sheet->where('refund_sheets.invoice_id',$request->input('invoice_id')) : $refund_sheet;
            $refund_sheet = $request->input('refunder_id')? $refund_sheet->where('refund_sheets.refunder_id',$request->input('refunder_id')) : $refund_sheet;
            $refund_sheet = $request->input('keyword')? $refund_sheet->where(function ($query) use($request){
                                                    $query->where('customers.name','like', '%'.$request->input('keyword').'%');
                                                    $query->orWhere('customers.phone','like', '%'.$request->input('keyword').'%');
                                                }) : $refund_sheet;

            $refund_sheet = $request->input('total_refund_price_from')? 
                                $refund_sheet->where('refund_sheets.total_refund_price', '>=',$request->input('total_refund_price_from')) 
                                : $refund_sheet;
            $refund_sheet = $request->input('total_refund_price_to')? 
                                $refund_sheet->where('refund_sheets.total_refund_price', '<=',$request->input('total_refund_price_to')) 
                                : $refund_sheet;
            $refund_sheet = $request->input('created_datetime_from')? 
                                $refund_sheet->where('refund_sheets.created_datetime', '>=',$request->input('created_datetime_from')." 00:00:00") 
                                : $refund_sheet;
            $refund_sheet = $request->input('created_datetime_to')? 
                                $refund_sheet->where('refund_sheets.created_datetime', '<=',$request->input('created_datetime_to')." 23:59:59") 
                                : $refund_sheet;

            $refund_sheet = $refund_sheet->selectRaw('refund_sheets.id AS refund_sheet_id, refund_sheets.invoice_id, refund_sheets.refunder_id, users.name AS refunder_name, invoices.customer_id, customers.name AS customer_name, customers.phone AS customers_phone, refund_sheets.total_refund_price, refund_sheets.reason, refund_sheets.created_datetime');

            if($request->input('order_by') && $request->input('order')){
                switch ($request->input('order_by')) {
                    case "customer_name":
                        $order_by = "customers.name";
                        break;
                    case "total_refund_price":
                        $order_by = "refund_sheets.total_refund_price";
                        break;
                    case "created_datetime":
                        $order_by = "refund_sheets.created_datetime";
                        break;
                    case "refunder_name":
                        $order_by = "users.name";
                        break;
                }
                $order = $request->input('order');
                $refund_sheet = $refund_sheet->orderBy($order_by, $order);
            } else {
                $refund_sheet = $refund_sheet->orderByRaw('refund_sheets.created_datetime DESC');
            }
            $refund_sheet = $refund_sheet->paginate(10);
            
            $state ='success';
            $errors = 'none';
            return response()->json(compact('state', 'errors', 'data', 'refund_sheet'));
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
    
    public function getRefundSheetDetail(Request $request, $store_id, $branch_id)
    {
        $user = auth()->user();
        $data = $request->except('token');
        $rules = [
            'refund_sheet_id'    => ['required','integer',Rule::exists('refund_sheets','id')->where('branch_id', $branch_id)],
        ];
        $validator = Validator::make($data, $rules);

        $check_permission = FrequentQuery::checkPermission($user->id, $branch_id, ['selling']);

        if($check_permission && !$validator->fails()){
            $refund_sheet = DB::table('refund_sheets')->where('refund_sheets.branch_id',$branch_id)->where('refund_sheets.id',$request->input('refund_sheet_id'))
                                ->leftJoin('refund_items','refund_items.refund_sheet_id','=','refund_sheets.id')
                                ->leftJoin('invoice_items','invoice_items.id','=','refund_items.invoice_item_id')
                                ->leftJoin('item_prices','item_prices.id','=','invoice_items.price_id')
                                ->leftJoin('items','items.id','=','item_prices.item_id');

            $refund_sheet = $refund_sheet->selectRaw('refund_sheets.id AS refund_sheet_id, refund_items.id AS refund_item_id, item_prices.item_id, items.name, item_prices.sell_price, refund_items.quantity, items.image_url');
            $refund_sheet = $refund_sheet->get();
            
            $state ='success';
            $errors = 'none';
            return response()->json(compact('state', 'errors', 'data', 'refund_sheet'));
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