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

class ReportController extends Controller
{
    public function getRevenue(Request $request, $store_id, $branch_id)
    {
        $user = auth()->user();
        $data = $request->except('token');
        $rules = [
            'from_date'     => 'required|date_format:Y-m-d',
            'to_date'       => 'required|date_format:Y-m-d',
            'unit'          => 'required|in:day,month,year',

            'revenue'       =>  'boolean',
            'profit'        =>  'boolean',
            'purchase'      =>  'boolean',
            'capital'       =>  'boolean',
        ];
        $validator = Validator::make($data, $rules);

        $check_permission = FrequentQuery::checkPermission($user->id, $branch_id, ['reporting']);

        if(!$validator->fails() && $check_permission){
            $date_list = $this->split_date($request->input('from_date'), $request->input('to_date'), $request->input('unit'));

            for($i = 0; $i < (count($date_list) - 1) ; $i++){
                $begin = $date_list[$i]." 00:00:00";
                $end = $date_list[$i+1]." 00:00:00";
                $between_date_list[] = $begin." - ".$end;
                
                if($request->input('revenue')){
                    $revenue[] = $this->revenue($branch_id, $begin, $end);
                }
                if($request->input('profit')){
                    $profit[] = $this->profit($branch_id, $begin, $end);
                }
                if($request->input('purchase')){
                    $purchase[] = $this->purchase($branch_id, $begin, $end);
                }
                if($request->input('capital')){
                    $capital[] = $this->capital($branch_id, $begin, $end);
                }
            }

            $return_str = ['state', 'errors', 'data'];
            $return_str[] = 'date_list';
            $return_str[] = 'between_date_list';
            if($request->input('revenue')){
                $return_str[] = 'revenue';
            }
            if($request->input('profit')){
                $return_str[] = 'profit';
            }
            if($request->input('purchase')){
                $return_str[] = 'purchase';
            }
            if($request->input('capital')){
                $return_str[] = 'capital';
            }
            $items_info = FrequentQuery::getItemInfo($branch_id);
            $purchase_price_info = FrequentQuery::getLatestPurchasedPrice();
            $no_purchased_price_items = $items_info->leftJoinSub($purchase_price_info, 'purchase_price_info', function ($join){
                                                        $join->on('purchase_price_info.item_id', '=', 'items.id');
                                                    })
                                                    ->where(function ($query){
                                                        $query->where('purchase_price_info.purchase_price', '<=', 0);
                                                        $query->orWhereNull('purchase_price_info.purchase_price');
                                                    })
                                                    ->selectRaw('items.id AS item_id, items.name AS item_name, items.bar_code, items.image_url, items.created_datetime, item_categories.id AS category_id, item_categories.name as category_name, item_quantities.quantity,item_prices.id as price_id, item_prices.sell_price, COALESCE(purchase_price_info.purchase_price, 0) AS purchase_price, COALESCE(items.point_ratio, item_categories.point_ratio) AS point_ratio')
                                                    ->get();
            $return_str[] = 'no_purchased_price_items';             
            $state ='success';
            $errors = 'none';
            return response()->json(compact($return_str));
        } else {
            $state = 'fail';
            $errors = !$check_permission? 'You dont have the permission to do this': $validator->errors();
            return response()->json(compact('state', 'errors', 'data'));
        }
    }

    public function getReportItems(Request $request, $store_id, $branch_id)
    {
        $user = auth()->user();
        $data = $request->except('token');
        $rules = [
            'from_date'     => 'nullable|date_format:Y-m-d',
            'to_date'       => 'nullable|date_format:Y-m-d',
            'category_id'   => 'required',
            'limit'         => 'required',
        ];
        $validator = Validator::make($data, $rules);

        $check_permission = FrequentQuery::checkPermission($user->id, $branch_id, ['reporting']);

        if(!$validator->fails() && $check_permission){
            $from_date = $request->input('from_date')? $request->input('from_date')." 00:00:00" : $request->input('from_date');
            $to_date = $request->input('to_date')? $request->input('to_date')." 23:59:59" : $request->input('to_date');
            if($request->input('to_date')){
                $date = strtotime("+1 day", strtotime($request->input('to_date')));
                $to_date = date("Y-m-d 00:00:00", $date);
            } else {
                $to_date = $request->input('to_date');
            }
            $category_id = $request->input('category_id');
            $limit = $request->input('limit');
            $top_total_sell_price_item = $this->getBestSellingItem($branch_id, $from_date, $to_date, "total_sell_price", $category_id, $limit, true);
            $top_sold_quantity_item = $this->getBestSellingItem($branch_id, $from_date, $to_date, "total_quantity", $category_id, $limit, true);
            $total_all = $this->getBestSellingItem($branch_id, $from_date, $to_date, "total_quantity", null, null, false);

            $return_str = ['state', 'errors', 'data', 'top_total_sell_price_item', 'top_sold_quantity_item', 'total_all', 'to_date'];

            $state ='success';
            $errors = 'none';
            return response()->json(compact($return_str));
        } else {
            $state = 'fail';
            $errors = !$check_permission? 'You dont have the permission to do this': $validator->errors();
            return response()->json(compact('state', 'errors', 'data'));
        }
    }

    public function getReportCategories(Request $request, $store_id, $branch_id)
    {
        $user = auth()->user();
        $data = $request->except('token');
        $rules = [
            'from_date'     => 'nullable|date_format:Y-m-d',
            'to_date'       => 'nullable|date_format:Y-m-d',
        ];
        $validator = Validator::make($data, $rules);

        $check_permission = FrequentQuery::checkPermission($user->id, $branch_id, ['reporting']);

        if(!$validator->fails() && $check_permission){
            $from_date = $request->input('from_date')? $request->input('from_date')." 00:00:00" : $request->input('from_date');
            $to_date = $request->input('to_date')? $request->input('to_date')." 23:59:59" : $request->input('to_date');

            $category_report_info = $this->getBestSellingCategory($branch_id, $from_date, $to_date, "total_sell_price");

            $return_str = ['state', 'errors', 'data','category_report_info'];

            $state ='success';
            $errors = 'none';
            return response()->json(compact($return_str));
        } else {
            $state = 'fail';
            $errors = !$check_permission? 'You dont have the permission to do this': $validator->errors();
            return response()->json(compact('state', 'errors', 'data'));
        }
    }

    public function getReportCustomer(Request $request, $store_id, $branch_id)
    {
        $user = auth()->user();
        $data = $request->except('token');
        $rules = [
            'from_date'     => 'nullable|date_format:Y-m-d',
            'to_date'       => 'nullable|date_format:Y-m-d',
        ];
        $validator = Validator::make($data, $rules);

        $check_permission = FrequentQuery::checkPermission($user->id, $branch_id, ['reporting']);

        if(!$validator->fails() && $check_permission){
            $from_date = $request->input('from_date')? $request->input('from_date')." 00:00:00" : $request->input('from_date');
            $to_date = $request->input('to_date')? $request->input('to_date')." 23:59:59" : $request->input('to_date');

            $top_customer = $this->getTopCustomer($branch_id, $from_date, $to_date, "total_buy_price");

            $return_str = ['state', 'errors', 'data', 'top_customer'];

            $state ='success';
            $errors = 'none';
            return response()->json(compact($return_str));
        } else {
            $state = 'fail';
            $errors = !$check_permission? 'You dont have the permission to do this': $validator->errors();
            return response()->json(compact('state', 'errors', 'data'));
        }
    }

    public function getReportSupplier(Request $request, $store_id, $branch_id)
    {
        $user = auth()->user();
        $data = $request->except('token');
        $rules = [
            'from_date'     => 'nullable|date_format:Y-m-d',
            'to_date'       => 'nullable|date_format:Y-m-d',
        ];
        $validator = Validator::make($data, $rules);

        $check_permission = FrequentQuery::checkPermission($user->id, $branch_id, ['reporting']);

        if(!$validator->fails() && $check_permission){
            $from_date = $request->input('from_date')? $request->input('from_date')." 00:00:00" : $request->input('from_date');
            $to_date = $request->input('to_date')? $request->input('to_date')." 23:59:59" : $request->input('to_date');

            $top_supplier = $this->getTopSupplier($branch_id, $from_date, $to_date, "total_purchase_price");

            $return_str = ['state', 'errors', 'data', 'top_supplier'];

            $state ='success';
            $errors = 'none';
            return response()->json(compact($return_str));
        } else {
            $state = 'fail';
            $errors = !$check_permission? 'You dont have the permission to do this': $validator->errors();
            return response()->json(compact('state', 'errors', 'data'));
        }
    }

    private function split_date($start_date, $end_date, $split_by){
        switch ($split_by){
            case 'day':
                $from_date = new DateTime($start_date);
                $interval = DateInterval::createFromDateString('1 day');
                break;
            case 'month':
                $from_date = new DateTime($start_date);
                $from_date->setDate($from_date->format('Y'), $from_date->format('m'), 1);
                $interval = DateInterval::createFromDateString('1 month');
                break;
            // case 'quarter':
            //     $interval = DateInterval::createFromDateString('4 month');
            //     break;
            case 'year':
                $from_date = new DateTime($start_date);
                $from_date->setDate($from_date->format('Y'), 1, 1);
                $interval = DateInterval::createFromDateString('1 year');
                break;
        }

        $to_date = new DateTime($end_date);
        $inc_date = $split_by == 'day'? '+2 '.$split_by:'+0 '.$split_by;
        $to_date = $to_date->modify( $inc_date );

        $period = new DatePeriod($from_date, $interval, $to_date);
        $date_list = [];
        $first_date_flag = true;
        foreach($period as $date){
            $date = $date->format("Y-m-d");
            if($first_date_flag){
                $date = $start_date;
                $first_date_flag = false;
            }
            
            $date_list[] = $date;
        }
        switch ($split_by){
            case 'day':
                // $interval = DateInterval::createFromDateString('1 day');
                break;
            case 'month':
                $month_date_list = date("m",strtotime(end($date_list)));
                $day_end_date = date("d",strtotime($end_date));
                if($day_end_date == "01"){
                    $date_list[] = $end_date;
                } 
                $date = strtotime("+1 day", strtotime($end_date));
                $date_list[] = date("Y-m-d", $date);
                break;
            // case 'quarter':
            //     $interval = DateInterval::createFromDateString('4 month');
            //     break;
            case 'year':
                $day_end_date = date("d",strtotime($end_date));
                $month_end_date = date("m",strtotime($end_date));
                if($day_end_date == "01" && $month_end_date == "01"){
                    $date_list[] = $end_date;
                } 
                $date = strtotime("+1 day", strtotime($end_date));
                $date_list[] = date("Y-m-d", $date);
                break;
        }

        return $date_list;
    }

    private function revenue($branch_id, $begin, $end){
        $invoice = DB::table('invoices')
                    ->where('invoices.branch_id', $branch_id)
                    ->where('invoices.created_datetime', '>=',$begin)
                    ->where('invoices.created_datetime', '<',$end)
                    ->selectRaw('COALESCE(SUM(invoices.total_sell_price - invoices.discount),0) AS revenue')
                    ->first();
        return $invoice->revenue;
    }
    
    private function profit($branch_id, $begin, $end){
        $invoice = DB::table('invoices')
                    ->where('invoices.branch_id', $branch_id)
                    ->leftJoin('invoice_items', 'invoice_items.invoice_id', '=', 'invoices.id')
                    ->leftJoin('item_prices', 'item_prices.id', '=', 'invoice_items.price_id');
        $latest_purchased_price = FrequentQuery::getLatestPurchasedPrice();

        $invoice = $invoice->leftJoinSub($latest_purchased_price, 'latest_purchased_price',function ($join){
                        $join->on('latest_purchased_price.item_id','=','item_prices.item_id');
                    })
                    ->where('invoices.created_datetime', '>=',$begin)
                    ->where('invoices.created_datetime', '<',$end)
                    ->selectRaw('COALESCE(SUM(invoice_items.quantity*(item_prices.sell_price - COALESCE(latest_purchased_price.purchase_price, 0))),0) AS profit')
                    ->first();
                    
        $discount = DB::table('invoices')
                    ->where('invoices.branch_id', $branch_id)
                    ->where('invoices.created_datetime', '>=',$begin)
                    ->where('invoices.created_datetime', '<',$end)
                    ->selectRaw('COALESCE(SUM(invoices.discount),0) AS discount')
                    ->first();
        return $invoice->profit - $discount->discount;
    }

    private function capital($branch_id, $begin, $end){
        $invoice = DB::table('invoices')
                    ->where('invoices.branch_id', $branch_id)
                    ->leftJoin('invoice_items', 'invoice_items.invoice_id', '=', 'invoices.id')
                    ->leftJoin('item_prices', 'item_prices.id', '=', 'invoice_items.price_id');
        $latest_purchased_price = FrequentQuery::getLatestPurchasedPrice();

        $invoice = $invoice->leftJoinSub($latest_purchased_price, 'latest_purchased_price',function ($join){
                        $join->on('latest_purchased_price.item_id','=','item_prices.item_id');
                    })
                    ->where('invoices.created_datetime', '>=',$begin)
                    ->where('invoices.created_datetime', '<',$end)
                    ->selectRaw('COALESCE(SUM(invoice_items.quantity * COALESCE(latest_purchased_price.purchase_price, 0)),0) AS capital')
                    ->first();
        return $invoice->capital;
    }
    
    private function purchase($branch_id, $begin, $end){
        $purchased_sheet = DB::table('purchased_sheets')
                                ->where('purchased_sheets.branch_id', $branch_id)
                                ->where('purchased_sheets.delivery_datetime', '>=',$begin)
                                ->where('purchased_sheets.delivery_datetime', '<',$end)
                                ->selectRaw('COALESCE(SUM(purchased_sheets.total_purchase_price - purchased_sheets.discount),0) AS purchase')
                                ->first();
        return $purchased_sheet->purchase;
    }
    
    private function getBestSellingItem($branch_id, $begin, $end, $order_by, $category_id, $limit, $is_group_by = true){
        $item_list = DB::table('invoices')->where('invoices.branch_id', $branch_id)
                        ->join('invoice_items', 'invoice_items.invoice_id', '=', 'invoices.id')
                        ->join('item_prices', 'item_prices.id', '=', 'invoice_items.price_id')
                        ->join('items', 'items.id', '=', 'item_prices.item_id')
                        ->leftJoin('item_categories','item_categories.id', '=', 'items.category_id')
                        ->orderByRaw("$order_by DESC");
        if($is_group_by){
        $item_list = $item_list->selectRaw('items.id, items.name, items.image_url, item_categories.id AS category_id, item_categories.name AS category_name, SUM(item_prices.sell_price*invoice_items.quantity) AS total_sell_price, SUM(invoice_items.quantity) AS total_quantity')
                        ->groupByRaw('items.id, items.name, items.image_url, item_categories.name, item_categories.id')
                        ->limit($limit);
        } else {
            $item_list = $item_list->selectRaw('SUM(item_prices.sell_price*invoice_items.quantity) AS total_sell_price, SUM(invoice_items.quantity) AS total_quantity');
        }
        if($begin){
            $item_list = $item_list->where('invoices.created_datetime', '>=', $begin);
        }
        if($end){
            $item_list = $item_list->where('invoices.created_datetime', '<', $end);
        }
        if($category_id){
            $item_list = $item_list->where('items.category_id', $category_id);
        }
        $item_list = $item_list->having('total_quantity', '>', 0);

        return $item_list->get();
    }

    private function getBestSellingCategory($branch_id, $begin, $end, $order_by){
        $item_list = DB::table('invoices')->where('invoices.branch_id', $branch_id)
                        ->join('invoice_items', 'invoice_items.invoice_id', '=', 'invoices.id')
                        ->join('item_prices', 'item_prices.id', '=', 'invoice_items.price_id')
                        ->join('items', 'items.id', '=', 'item_prices.item_id')
                        ->join('item_categories', 'item_categories.id', '=', 'items.category_id')->where('item_categories.branch_id', $branch_id)
                        ->selectRaw('item_categories.id, item_categories.name, SUM(item_prices.sell_price*invoice_items.quantity) AS total_sell_price, SUM(invoice_items.quantity) AS total_quantity')
                        ->groupByRaw('item_categories.id, item_categories.name')
                        ->orderByRaw("item_categories.id DESC");
        
        if($begin){
            $item_list = $item_list->where('invoices.created_datetime', '>=', $begin);
        }
        if($end){
            $item_list = $item_list->where('invoices.created_datetime', '<', $end);
        }
        return $item_list->get();
    }

    private function getTopCustomer($branch_id, $begin, $end, $order_by){
        $customer_list = DB::table('invoices')->where('invoices.branch_id', $branch_id)
                        ->join('customers', 'customers.id', '=', 'invoices.customer_id')
                        ->selectRaw('customers.name, customers.phone, SUM(total_sell_price - discount) AS total_buy_price')
                        ->groupByRaw('customers.name, customers.phone')
                        ->orderByRaw("$order_by DESC");
        
        if($begin){
            $customer_list = $customer_list->where('invoices.created_datetime', '>=', $begin);
        }
        if($end){
            $customer_list = $customer_list->where('invoices.created_datetime', '<', $end);
        }
        return $customer_list->get();
    }

    private function getTopSupplier($branch_id, $begin, $end, $order_by){
        $supplier_list = DB::table('purchased_sheets')->where('purchased_sheets.branch_id', $branch_id)
                        ->join('suppliers', 'suppliers.id', '=', 'purchased_sheets.supplier_id')
                        ->selectRaw('suppliers.name, SUM(purchased_sheets.total_purchase_price) AS total_purchase_price')
                        ->groupByRaw('suppliers.name')
                        ->orderByRaw("$order_by DESC");
        
        if($begin){
            $supplier_list = $supplier_list->where('purchased_sheets.delivery_datetime', '>=', $begin);
        }
        if($end){
            $supplier_list = $supplier_list->where('purchased_sheets.delivery_datetime', '<', $end);
        }
        return $supplier_list->get();
    }
}