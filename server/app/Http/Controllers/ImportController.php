<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\FrequentQuery;

class ImportController extends WebController
{
    private $sidebar_code = "sidebar_import";
    private $per_page = 10;

    public function index(Request $request, $store_id, $branch_id){
        if($request->ajax()){
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
            $items = $items->selectRaw('items.id AS item_id, items.name, items.bar_code, items.image_url, COALESCE(purchase_price_info.purchase_price, 0) AS purchase_price');
            $items = $items->orderBy('items.category_id', 'asc');
            $items = $items->orderBy('items.id', 'asc');
    
            $per_page = $this->per_page;
            $items = $items->paginate($per_page);
            $search = $request->input('search');
            return view('inventory.import.add_item_modal', compact('items', 'search'));
        }
        $sidebar_code = $this->sidebar_code;
        return view('inventory.import.index', compact('sidebar_code'));
    }

    public function createPurchasedSheet(Request $request, $store_id, $branch_id){
        $user = Auth::user();
        $purchased_items =  $request->input('purchased_items');
        Log::info(print_r($request->all(), true));
        $total_purchase_price = 0;

        foreach($purchased_items as  $purchased_item){
            $total_purchase_price += $purchased_item['purchase_price']*$purchased_item['quantity'];
        }
        $purchased_sheet_id = 0;
        DB::transaction(function () use ($request, $purchased_items, $branch_id, $user, &$item_flag, $total_purchase_price, &$purchased_sheet_id) {
            //insert purchased_sheet
            $purchased_sheet_id = DB::table('purchased_sheets')->insertGetId([
                'branch_id'             => $branch_id,
                'purchaser_id'          => $user->id,
                'supplier_id'           => $request->input('supplier_id'),
                'total_purchase_price'  => $total_purchase_price,
                'discount'              => $request->input('discount'),
                'deliver_name'          => $request->input('deliver_name'),
            ]);
            foreach($purchased_items as  $purchased_item){
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
                                    'item_quantities.quantity'   => DB::raw("item_quantities.quantity + ".$purchased_item['quantity']) ,
                                ]);
            }
        });
        return redirect()->route('purchased_sheet.detail', compact('store_id','branch_id', 'purchased_sheet_id'));
    }

    public function supplierList(Request $request, $store_id, $branch_id){
        $items = DB::table('suppliers')
                    ->where('branch_id', $branch_id)
                    ->where('deleted',0);
        $items = $request->input('search')? $items->where(function ($query) use($request){
                        $query->orWhere('suppliers.name','like', '%'.$request->input('search').'%');
                        $query->orWhere('suppliers.phone','like', '%'.$request->input('search').'%');
                    }) : $items;
        $items = $items->orderBy('id','desc');
        
        $per_page = $this->per_page;
        $items = $items->paginate($per_page);
        $search = $request->input('search');
        return view('inventory.import.supplier_modal', compact('items', 'search'));
    }
}