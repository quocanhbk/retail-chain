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

class ItemPriceController extends Controller
{
    public function getItemPriceHistory(Request $request, $store_id, $branch_id)
    {
        $user = auth()->user();
        $data = $request->except('token');
        $rules = [
            'item_id'   => 'nullable|exists:item_prices'
        ];
        $validator = Validator::make($data, $rules);

        $check_permission = FrequentQuery::checkPermission($user->id, $branch_id, ['purchasing']);

        if(!$validator->fails() && $check_permission){
            $price_history = DB::table('items')
                                ->leftJoin('item_categories', 'item_categories.id', '=', 'items.category_id')
                                ->where('item_categories.branch_id', $branch_id)
                                ->leftJoin('item_prices', 'item_prices.item_id', '=', 'items.id')
                                ->leftJoin('users', 'users.id', '=', 'item_prices.change_by');
            $price_history = $request->input('item_id')? $price_history->where('items.id', $request->input('item_id')) : $price_history;
            $price_history = $price_history->selectRaw('item_prices.item_id, items.name, item_prices.sell_price, users.name AS change_by, item_prices.start_date, item_prices.end_date');
            $price_history = $price_history->orderByRaw('item_prices.item_id ASC, item_prices.start_date DESC');      
            $price_history = $price_history->get();      
            $state ='success';
            $errors = 'none';
            return response()->json(compact('state', 'errors', 'data', 'price_history'));
        } else {
            $state = 'fail';
            $errors = !$check_permission? 'You dont have the permission to do this': $validator->errors();
            return response()->json(compact('state', 'errors', 'data'));
        }
    }
}