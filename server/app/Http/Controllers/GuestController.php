<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\FrequentQuery;

class GuestController extends Controller
{
    public function itemList(Request $request, $branch_id, $category_id = null){
        $branch_info = DB::table('branches')->where('branches.id', $branch_id)->where('branches.status', 'enable')->selectRaw('branches.name, branches.address')->first();
        if($branch_info !== null){
            $item_list = FrequentQuery::getItemInfo($branch_id);
            $item_list = $item_list
                        ->selectRaw('items.name, items.image_url, item_prices.sell_price, item_quantities.quantity');
            $category_list = DB::table('item_categories')->where('item_categories.branch_id', $branch_id)->where('item_categories.deleted',0);
            if($request->category_id){
                $item_list = $item_list->where('item_categories.id', $request->category_id);
            }
            if($request->key_word){
                $item_list = $item_list->where('items.name', 'like', '%'.$request->key_word.'%');
            }
            
            $item_list = $item_list->paginate(12)->appends(request()->query());
            $category_list = $category_list->get();
            return view('guest.item_list', compact('item_list', 'category_list', 'branch_info' ));
        } else {
            //return 404
            abort(404);
        }
    }
    public function QRScanner(Request $request, $branch_id, $category_id = null){
        
        return view('guest.qr_scanner');
    }
}