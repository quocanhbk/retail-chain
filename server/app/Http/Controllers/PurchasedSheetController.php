<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\FrequentQuery;

class PurchasedSheetController extends WebController
{
    private $sidebar_code = "sidebar_purchased_sheet";
    private $per_page = 10;

    public function listPurchasedSheet(Request $request, $store_id, $branch_id){
        $items = DB::table('purchased_sheets')->where('purchased_sheets.branch_id', $branch_id)
                            ->leftJoin('users','users.id','=','purchased_sheets.purchaser_id')
                            ->leftJoin('suppliers','suppliers.id','=','purchased_sheets.supplier_id')
                            ->leftJoin('purchased_items', 'purchased_items.purchased_sheet_id', '=', 'purchased_sheets.id')
                            ->where('purchased_items.quantity', '>', 0);

        $items = $request->input('search')? $items->where(function ($query) use($request){
                                                $query->where('suppliers.name','like', '%'.$request->input('search').'%');
                                            }) : $items;

        $items = $items->selectRaw('purchased_sheets.id AS purchased_sheet_id, purchased_sheets.deliver_name, purchased_sheets.total_purchase_price, purchased_sheets.discount, purchased_sheets.delivery_datetime, purchased_sheets.supplier_id, suppliers.name AS supplier_name, suppliers.phone AS supplier_phone, purchased_sheets.purchaser_id, users.name AS purchaser_name');
        $items = $items->groupByRaw('purchased_sheets.id, purchased_sheets.deliver_name, purchased_sheets.total_purchase_price, purchased_sheets.discount, purchased_sheets.delivery_datetime, purchased_sheets.supplier_id, suppliers.name, suppliers.phone, purchased_sheets.purchaser_id, users.name');

        if($request->input('order_by') && $request->input('order')){
            $items = $items->orderBy($request->input('order_by'), $request->input('order'));
        } else {
            $items = $items->orderByRaw('purchased_sheets.delivery_datetime DESC');
        }
        $per_page = $this->per_page;
        $items = $items->paginate($per_page);
        $sidebar_code = $this->sidebar_code;
        return view('inventory.purchased_sheet.list', compact('sidebar_code', 'items'));
    }

    public function detailPurchasedSheet(Request $request, $store_id, $branch_id, $purchased_sheet_id){
        $item = DB::table('purchased_sheets')->where('purchased_sheets.branch_id', $branch_id)
                            ->leftJoin('users','users.id','=','purchased_sheets.purchaser_id')
                            ->leftJoin('suppliers','suppliers.id','=','purchased_sheets.supplier_id')
                            ->leftJoin('purchased_items', 'purchased_items.purchased_sheet_id', '=', 'purchased_sheets.id')
                            ->where('purchased_items.quantity', '>', 0);

        $item = $item->where('purchased_sheets.id', $purchased_sheet_id);

        $item = $item->selectRaw('purchased_sheets.id AS purchased_sheet_id, purchased_sheets.deliver_name, purchased_sheets.total_purchase_price, purchased_sheets.discount, purchased_sheets.delivery_datetime, purchased_sheets.supplier_id, suppliers.name AS supplier_name, suppliers.phone AS supplier_phone, purchased_sheets.purchaser_id, users.name AS purchaser_name');
        $item = $item->groupByRaw('purchased_sheets.id, purchased_sheets.deliver_name, purchased_sheets.total_purchase_price, purchased_sheets.discount, purchased_sheets.delivery_datetime, purchased_sheets.supplier_id, suppliers.name, suppliers.phone, purchased_sheets.purchaser_id, users.name');

        $list_item = DB::table('purchased_sheets')->where('purchased_sheets.branch_id', $branch_id)->where('purchased_sheets.id', $purchased_sheet_id)
                ->leftJoin('purchased_items', 'purchased_items.purchased_sheet_id','=','purchased_sheets.id')
                ->leftJoin('items','items.id','=','purchased_items.item_id');

        $list_item = $list_item->where('purchased_items.quantity', '>', 0); 

        $list_item = $list_item->selectRaw('purchased_sheets.id AS purchased_sheet_id, purchased_items.id AS purchased_item_id, purchased_items.item_id, items.name, purchased_items.purchase_price, purchased_items.quantity, items.image_url');

        $list_item = $list_item->get();
        $item = $item->first();
        $sidebar_code = $this->sidebar_code;
        return view('inventory.purchased_sheet.detail', compact('sidebar_code', 'item', 'list_item'));
    }
}