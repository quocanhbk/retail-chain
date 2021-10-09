<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\FrequentQuery;

class InventoryController extends WebController
{
    private $sidebar_code = "sidebar_inventory";
    private $per_page = 10;

    public function item(Request $request, $store_id, $branch_id){
        $items = FrequentQuery::getItemInfo($request->branch_id);
        $purchase_price_info = FrequentQuery::getLatestPurchasedPrice();
        $items = $items->leftJoinSub($purchase_price_info, 'purchase_price_info', function ($join){
                        $join->on('purchase_price_info.item_id', '=','items.id');
                    });
        if($request->input('search')){
            $items = $items->where(function ($query) use ($request) {
                            $query->where('items.name', 'like' ,'%'.$request->input('search').'%');
                            $query->orWhere('items.bar_code', 'like' ,'%'.$request->input('search').'%');
                        });
        }
        if($request->input('category_string')){
            $category_str_array = explode("_", $request->input('category_string'));
            if(!empty($category_str_array[0])){
                $items = $items->where(function ($query) use ($category_str_array) {
                    foreach($category_str_array as $category_id){
                        $query->orWhere('item_categories.id', $category_id);
                    }
                });
            }
        }
        $items = $items->selectRaw('item_categories.name AS category_name, items.id AS item_id, items.name, items.bar_code, items.image_url, item_prices.sell_price, COALESCE(purchase_price_info.purchase_price, 0) AS purchase_price, COALESCE(items.point_ratio, item_categories.point_ratio, 0) AS point_ratio, item_quantities.quantity, items.created_datetime');
        if($request->input('order_by') && $request->input('order')){
            $items = $items->orderBy($request->input('order_by'), $request->input('order'));
        } else {
            $items = $items->orderBy('items.category_id', 'asc');
            $items = $items->orderBy('items.id', 'asc');
        }

        $per_page = $this->per_page;
        $items = $items->paginate($per_page)->appends(request()->query());

        $category_list = DB::table('item_categories')->where('item_categories.branch_id', $branch_id)->where('item_categories.deleted',0)->get();
        $sidebar_code = $this->sidebar_code;
        return view('inventory.inventory.item', compact('sidebar_code', 'items', 'per_page', 'category_list'));
    }
    
    public function itemCreate(Request $request, $store_id, $branch_id){
        if ($request->isMethod('post')) {
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
            DB::transaction(function () use ($request, $store_id, $branch_id, $bar_code) {
                $user = Auth::user();
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
                        $img_url = 'upload/store_id_'.$store_id.'/branch_id_'.$branch_id.'/';
                        $result = DB::table('items')->where('id',$item_id)
                            ->update([
                                'image_url' => $img_url.$item_id.'.'.$ext
                                ]);
                        if ($result){
                            $image->move('../public/'.$img_url,$item_id.'.'.$ext);
                        } else {
                            DB::table('items')->where('id',$item_id)
                            ->update(['image_url'=>null]);
                        }
                    }
                }
            });
            return redirect()->route('inventory.item', compact('store_id', 'branch_id'));
        }
        $category_list = DB::table('item_categories')->where('item_categories.branch_id', $branch_id)->where('item_categories.deleted',0)->get();
        $sidebar_code = $this->sidebar_code;
        return view('inventory.inventory.item_create', compact('sidebar_code', 'category_list'));
    }

    public function itemEdit(Request $request, $store_id, $branch_id, $item_id){
        if ($request->isMethod('post')) {
            DB::transaction(function () use ($request, $store_id, $branch_id, $item_id) {
                $user = Auth::user();
                $item = FrequentQuery::getItemInfo($branch_id);
                $item = $item->where('items.id',$item_id);
                $current_date =  date('Y-m-d');

                $item->update([
                    'items.category_id'         => $request->input('category_id'),
                    'items.name'                => $request->input('item_name'),
                    'items.bar_code'            => $request->input('bar_code'),
                    'items.category_id'         => $request->input('category_id'),
                    'items.point_ratio'         => $request->input('point_ratio'),
                    'item_quantities.quantity'   => $request->input('quantity'),
                ]);
                
                $item_price = FrequentQuery::getItemInfo($branch_id);
                $item_price = $item_price->where('items.id',$item_id)
                                ->select('item_prices.sell_price')
                                ->first();
                if($item_price->sell_price != $request->input('sell_price')){
                        $item->update([
                            'item_prices.end_date'  => $current_date
                        ]);
                        DB::table('item_prices')->insert([
                            'item_id'       => $item_id,
                            'sell_price'    => $request->input('sell_price'),
                            'change_by'     => $user->id,
                        ]);
                }
    
                if ($request->hasFile('image')){
                    $image = $request->image;
                    $ext = $image->getClientOriginalExtension();
                    $img_url = 'upload/store_id_'.$store_id.'/branch_id_'.$branch_id.'/';
                    $result = DB::table('items')->where('id',$item_id)->where('deleted', 0)
                        ->update([
                            'image_url' =>  $img_url.$item_id.'.'.$ext,
                        ]);

                    $image->move('../public/'.$img_url,$item_id.'.'.$ext);
                }
                //update pruchase_price
                $old_purchase_price = FrequentQuery::getItemInfo($branch_id);
                $purchase_price_info = FrequentQuery::getLatestPurchasedPrice();
                $old_purchase_price = $old_purchase_price->leftJoinSub($purchase_price_info, 'purchase_price_info', function ($join){
                                                            $join->on('purchase_price_info.item_id', '=','items.id');
                                                        });
                $old_purchase_price = $old_purchase_price->where('items.id', $item_id)
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
                        'item_id'               => $item_id,
                        'purchase_price'        => $new_purchase_price,
                        'quantity'              => 0
                    ]);
                }
            });
            
            return redirect()->route('inventory.itemEdit', compact('store_id', 'branch_id', 'item_id'));
        } else if ($request->isMethod('get')){
            $item = FrequentQuery::getItemInfo($request->branch_id);
            $purchase_price_info = FrequentQuery::getLatestPurchasedPrice();
            $item = $item->leftJoinSub($purchase_price_info, 'purchase_price_info', function ($join){
                            $join->on('purchase_price_info.item_id', '=','items.id');
                        });
            $item = $item->selectRaw('item_categories.id AS category_id, item_categories.name AS category_name, items.name, items.bar_code, items.image_url, item_prices.sell_price, COALESCE(purchase_price_info.purchase_price, 0) AS purchase_price, COALESCE(items.point_ratio, item_categories.point_ratio, 0) AS point_ratio, item_quantities.quantity, items.created_datetime');
            
            $item = $item->where('items.id', $item_id)->first();

            $category_list = DB::table('item_categories')->where('item_categories.branch_id', $branch_id)->where('item_categories.deleted',0)->get();
            $sidebar_code = $this->sidebar_code;
            return view('inventory.inventory.item_edit', compact('sidebar_code', 'category_list', 'item'));
        }
    }

    public function itemDetail(Request $request, $store_id, $branch_id, $item_id){
        if ($request->isMethod('get')){
            $item = FrequentQuery::getItemInfo($request->branch_id);
            $purchase_price_info = FrequentQuery::getLatestPurchasedPrice();
            $item = $item->leftJoinSub($purchase_price_info, 'purchase_price_info', function ($join){
                            $join->on('purchase_price_info.item_id', '=','items.id');
                        });
            $item = $item->selectRaw('item_categories.id AS category_id, item_categories.name AS category_name, items.name, items.bar_code, items.image_url, item_prices.sell_price, COALESCE(purchase_price_info.purchase_price, 0) AS purchase_price, COALESCE(items.point_ratio, item_categories.point_ratio, 0) AS point_ratio, item_quantities.quantity, items.created_datetime');
            
            $item = $item->where('items.id', $item_id)->first();

            $category_list = DB::table('item_categories')->where('item_categories.branch_id', $branch_id)->where('item_categories.deleted',0)->get();
            $sidebar_code = $this->sidebar_code;
            return view('inventory.inventory.item_detail', compact('sidebar_code', 'category_list', 'item'));
        }
    }

    public function itemDelete(Request $request, $store_id, $branch_id, $item_id){
        if ($request->isMethod('post')) {
            $item = FrequentQuery::getItemInfo($branch_id);
            $item = $item->where('items.id',$item_id);
            DB::transaction(function () use ($item) {
                $current_date =  date('Y-m-d');
                $item->update([
                    'items.deleted'         => 1,
                    'item_prices.end_date'  => $current_date
                ]);
            });
        }
    }
    
    public function itemChangeQuantity(Request $request, $store_id, $branch_id, $item_id){
        if ($request->isMethod('get')) {
            $item = FrequentQuery::getItemInfo($request->branch_id);
            $item = $item->selectRaw('item_quantities.quantity')->where('items.id', $item_id)->first();
            $sidebar_code = $this->sidebar_code;
            return view('inventory.inventory.item_change_quantity', compact('sidebar_code', 'item'));
        } else if ($request->isMethod('post')) {
            DB::transaction(function () use($request,  $branch_id, $item_id){
                $user = Auth::user();
                //create quantity_checking_sheets
                $quant_checking_sheet_id = DB::table('quantity_checking_sheets')->insertGetID([
                    'branch_id'     => $branch_id,
                    'checker_id'    => $user->id,
                    'reason'        => $request->reason,
                ]);
                //creat quantity_checking_items records
                $change_quant = abs($request->old_quant - $request->new_quant);
                $sign = $request->old_quant <= $request->old_quant? "+" : "-";
                //insert into quantity_checking_items
                DB::table('quantity_checking_items')->insert([
                    'quant_checking_sheet_id'   => $quant_checking_sheet_id,
                    'item_id'                   => $item_id,
                    'changes'                   => $sign . $change_quant,
                    'old_quant'                 => $request->old_quant,
                    'new_quant'                 => $request->new_quant,
                ]);
                //update curresponding item quantity
                $update_item_quant = FrequentQuery::getItemInfo($branch_id);
                $update_item_quant->where('items.id', $item_id)->update([
                    'item_quantities.quantity'  => $request->new_quant
                ]);
            });
            return redirect()->route('inventory.itemEdit', compact('store_id', 'branch_id', 'item_id'));
        }
    }

    public function itemPriceHistory(Request $request, $store_id, $branch_id, $item_id){
        $items = DB::table('items')
                            ->leftJoin('item_categories', 'item_categories.id', '=', 'items.category_id')
                            ->where('item_categories.branch_id', $branch_id)
                            ->leftJoin('item_prices', 'item_prices.item_id', '=', 'items.id')
                            ->leftJoin('users', 'users.id', '=', 'item_prices.change_by');
        $items = $items->where('items.id', $item_id);
        $items = $items->selectRaw("item_prices.item_id, items.name, item_prices.sell_price, COALESCE(users.name, 'Không có') AS change_by, item_prices.start_date, item_prices.end_date");
        $items = $items->orderByRaw('item_prices.item_id ASC, item_prices.start_date DESC'); 
        
        $per_page = $this->per_page;
        $items = $items->paginate($per_page);
        $sidebar_code = $this->sidebar_code;
        return view('inventory.inventory.item_price_history', compact('sidebar_code', 'items', 'per_page'));
    }
    
    public function itemQuantityHistory(Request $request, $store_id, $branch_id, $item_id){
        $items = DB::table('quantity_checking_sheets')
                            ->leftJoin('quantity_checking_items', 'quantity_checking_items.quant_checking_sheet_id', '=', 'quantity_checking_sheets.id')
                            ->leftJoin('users', 'users.id', '=','quantity_checking_sheets.checker_id');

        $items = $items->where('quantity_checking_items.item_id', $item_id);   
        
        $items = $items->selectRaw('quantity_checking_sheets.id AS quant_checking_sheet_id, quantity_checking_items.item_id, quantity_checking_items.changes, quantity_checking_items.old_quant, quantity_checking_items.new_quant, quantity_checking_sheets.checker_id, users.name AS checker_name, quantity_checking_sheets.reason, quantity_checking_sheets.created_datetime');
        $items = $items->orderByRaw('quantity_checking_sheets.created_datetime DESC');

        $per_page = $this->per_page;
        $items = $items->paginate($per_page);
        $sidebar_code = $this->sidebar_code;
        return view('inventory.inventory.item_quant_history', compact('sidebar_code', 'items', 'per_page'));
    }
}