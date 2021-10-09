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

class DashboardController extends Controller
{
    public function getDashboard(Request $request, $store_id, $branch_id)
    {
        $user = auth()->user();
        $data = $request->except('token');

        $no_employee = DB::table('works')
                            ->leftJoin('users','users.id', '=','works.user_id')->where('users.status', 'enable')
                            ->where('works.branch_id', $branch_id)
                            ->selectRaw('works.branch_id, works.user_id')
                            ->groupByRaw('works.branch_id, works.user_id')
                            ->get()
                            ->count();
        $no_item = FrequentQuery::getItemInfo($branch_id)->sum('item_quantities.quantity');

        $revenue_month = [];
        $revenue_year = [];
        for($i = 0; $i < 30; $i++){
            $minus_day = strtotime("-$i day");
            $date = date('Y-m-d', $minus_day);

            $revenue = FrequentQuery::getRevenueDate($branch_id, $date);
            $buffer['revenue'] = $revenue;
            $buffer['date'] = $date;
            $revenue_month[] = $buffer;
            unset($buffer);
        }
        for($i = 0; $i < 12 ; $i++){
            $minus_month = strtotime("-$i month");
            $year = date("Y",$minus_month);
            $month = date("m", $minus_month);
            
            $revenue = FrequentQuery::getRevenueMonth($branch_id, $year, $month);
            $buffer['revenue'] = $revenue;
            $buffer['year-month'] = $year."-".$month;
            $revenue_year[] = $buffer;
            unset($buffer);
        }
        $minus_week = strtotime("-7 day");
        $current_date = date('Y-m-d');
        $last_week_date = date('Y-m-d', $minus_day);
        $import_price = DB::table('purchased_sheets')->where('purchased_sheets.branch_id', $branch_id)
                            ->whereRaw("DATE(purchased_sheets.delivery_datetime) <= '".$current_date."' AND DATE(purchased_sheets.delivery_datetime) >= '".$last_week_date."'")
                            ->selectRaw('COALESCE(SUM(total_purchase_price),0) as total_purchase_price, COALESCE(SUM(discount),0) as discount')
                            ->first();
        return response()->json(compact('revenue_month', 'revenue_year', 'no_employee', 'no_item', 'import_price'));
    }
}