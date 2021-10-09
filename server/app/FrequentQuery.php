<?php

namespace App;

use Illuminate\Support\Facades\DB;

class FrequentQuery 
{
    public static function getWorkingBranchTable()
    {
        // $store_info = DB::table('users')
        $branch = DB::table('users')->where('users.status', 'enable')
                    ->leftJoin('works','works.user_id','=','users.id')
                    ->selectRaw('works.user_id, works.branch_id')
                    ->groupByRaw('works.user_id, works.branch_id');
        return $branch;
    }

    public static function checkPermission($user_id,$branch_id, $role_list)
    {
        $conf_role_list = ['selling','purchasing', 'managing', 'reporting'];
        $check_correct_role = !array_diff($role_list, $conf_role_list);
        if($check_correct_role){
            $check_role = DB::table('works')->where('works.user_id', $user_id)->where('works.branch_id', $branch_id)
                                    ->leftJoin('roles', 'roles.id','=','works.role_id');

                $check_role = $check_role->where(function ($query) use ($role_list){
                    foreach($role_list as $role){
                        $query = $query->orWhere('roles.name',$role);
                    } 
                });
            $check_role = $check_role->exists();
            return $check_role;
        } else {
            return false;
        }
    }

    // public static function checkItemInStore($item_id, $store_id){
    //     $check_item_in_store = DB::table('items')->where('items.deleted',0)->where('items.id', $item_id)
    //                             ->leftJoin('item_categories','item_categories.id', 'items.category_id')->where('item_categories.deleted', 0)
    //                             ->where('item_categories.store_id', $store_id);
    //     $check_item_in_store = $check_item_in_store->exists();
    //     return $check_item_in_store;
    // }

    public static function getItemInfo($branch_id){
        $item_info = DB::table('items')->where('items.deleted',0)
                        ->leftJoin('item_quantities','item_quantities.item_id','=','items.id')->where('item_quantities.branch_id',$branch_id)
                        ->leftJoin('item_prices','item_prices.item_id','=','items.id')->whereNull('item_prices.end_date')
                        ->leftJoin('item_categories','item_categories.id','=','items.category_id')->where('item_categories.branch_id', $branch_id)->where('item_categories.deleted',0);
        return $item_info;
    }

    public static function getRevenueDate($branch_id,$date){
        $revenue = DB::table('invoices')->where('invoices.branch_id', $branch_id)
                    ->whereRaw("DATE(invoices.created_datetime) = '".$date."'")
                    ->selectRaw('COALESCE(SUM(total_sell_price),0) as total_sell_price, COALESCE(SUM(discount),0) as discount')
                    ->first();
        return $revenue;
    }

    public static function getRevenueMonth($branch_id, $year, $month){
        $revenue = DB::table('invoices')->where('invoices.branch_id', $branch_id)
                    ->whereRaw("year(invoices.created_datetime) = '".$year."'")
                    ->whereRaw("month(invoices.created_datetime) = '".$month."'")
                    ->selectRaw('COALESCE(SUM(total_sell_price),0) as total_sell_price, COALESCE(SUM(discount),0) as discount')
                    ->first();
        return $revenue;
    }

    public static function getInvoiceItemInfo($branch_id, $invoice_id){
        $table = DB::table('invoices')->where('invoices.branch_id', $branch_id)->where('invoices.id', $invoice_id)
                    ->leftJoin('invoice_items', 'invoice_items.invoice_id','=', 'invoices.id')
                    ->leftJoin('item_prices','item_prices.id','=','invoice_items.price_id')
                    ->leftJoin('items','items.id','=','item_prices.item_id')
                    ->leftJoin('item_quantities','item_quantities.item_id','=','items.id');
        return $table;
    }

    public static function getEmployeeList($branch_id){
        $employee_list = DB::table('users')
                            ->leftJoin('works','works.user_id', '=', 'users.id')->where('works.branch_id', $branch_id)
                            ->selectRaw('works.branch_id, works.user_id, users.name, users.email, users.phone, users.date_of_birth, users.gender, users.status')
                            ->groupByRaw('works.branch_id, works.user_id, users.name, users.email, users.phone, users.date_of_birth, users.gender, users.status');
        return $employee_list;
    }

    public static function getEmployeeListExcludeOwner($branch_id, $is_owner){
        $employee_list = DB::table('users')
                            ->leftJoin('works','works.user_id', '=', 'users.id')->where('works.branch_id', $branch_id)
                            ->leftJoin('stores','stores.owner_id', '=', 'users.id')
                            ->whereNull('stores.id');
        if($is_owner){
            $employee_list = $employee_list
                                ->selectRaw('works.branch_id, works.user_id, users.name, users.email, users.username, users.phone, users.date_of_birth, users.gender, users.status')
                                ->groupByRaw('works.branch_id, works.user_id, users.name, users.email, users.username, users.phone, users.date_of_birth, users.gender, users.status');
        } else {
            $employee_list = $employee_list
                                ->selectRaw('works.branch_id, works.user_id, users.name, users.email, users.phone, users.date_of_birth, users.gender, users.status')
                                ->groupByRaw('works.branch_id, works.user_id, users.name, users.email, users.phone, users.date_of_birth, users.gender, users.status');
        }
        return $employee_list;
    }

    public static function isEmployee($user_id, $branch_id, $store_id){
        $is_employee = DB::table('branches')->where('branches.id', $branch_id)
                            ->join('stores','stores.id','=','branches.store_id')->where('stores.id', $store_id)
                            ->where('stores.owner_id','!=',$user_id)
                            ->exists();
        return $is_employee;
    }
    
    public static function getPointRatio($item_id, $branch_id){
        $point_ratio = DB::table('items')->where('items.deleted', 0)->where('items.id', $item_id)
                            ->leftJoin('item_categories','item_categories.id','=','items.category_id')->where('item_categories.branch_id', $branch_id)->where('item_categories.deleted', 0)
                            ->selectRaw('COALESCE(items.point_ratio, item_categories.point_ratio, 0) AS point_ratio')
                            ->first();
            
        return $point_ratio->point_ratio;
    }

    public static function getLatestPurchasedPrice(){
        $latest_purchased_price = DB::table('purchased_items')
                                    ->leftJoin('purchased_sheets', 'purchased_sheets.id', '=', 'purchased_items.purchased_sheet_id')
                                    ->selectRaw("purchased_items.item_id, substring_index(group_concat(purchased_items.purchase_price order by purchased_sheets.delivery_datetime desc), ',', 1) as purchase_price")
                                    ->groupByRaw('purchased_items.item_id');
        return $latest_purchased_price;
    }
}