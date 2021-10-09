<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\FrequentQuery;

class ReturnPurchasedSheetController extends WebController
{
    private $sidebar_code = "sidebar_return_purchased_sheet";
    private $per_page = 10;

    public function listReturnPurchasedSheet(Request $request, $store_id, $branch_id){
        $items = DB::table('return_purchased_sheets')->where('return_purchased_sheets.branch_id',$branch_id)
                            ->leftJoin('purchased_sheets','purchased_sheets.id','=','return_purchased_sheets.purchased_sheet_id')
                            ->leftJoin('suppliers','suppliers.id', '=', 'purchased_sheets.supplier_id')
                            ->leftJoin('users','users.id','=','return_purchased_sheets.returner_id');

        $items = $request->input('search')? $items->where(function ($query) use($request){
                                                $query->orWhere('suppliers.name','like', '%'.$request->input('search').'%');
                                            }) : $items;

        $items = $items->selectRaw('return_purchased_sheets.id AS return_purchased_sheet_id, return_purchased_sheets.purchased_sheet_id, return_purchased_sheets.returner_id, users.name AS returner_name, purchased_sheets.supplier_id, suppliers.name AS supplier_name, return_purchased_sheets.total_return_money, return_purchased_sheets.created_datetime');

        if($request->input('order_by') && $request->input('order')){
            $items = $items->orderBy($request->input('order_by'), $request->input('order'));
        } else {
            $items = $items->orderByRaw('return_purchased_sheets.created_datetime DESC');
        }

        $per_page = $this->per_page;
        $items = $items->paginate($per_page);
        $sidebar_code = $this->sidebar_code;
        return view('inventory.return_purchased_sheet.list', compact('sidebar_code', 'items'));
    }

    public function detailReturnPurchasedSheet(Request $request, $store_id, $branch_id, $return_purchased_sheet_id){
        $item = DB::table('return_purchased_sheets')->where('return_purchased_sheets.branch_id',$branch_id)
                            ->leftJoin('purchased_sheets','purchased_sheets.id','=','return_purchased_sheets.purchased_sheet_id')
                            ->leftJoin('suppliers','suppliers.id', '=', 'purchased_sheets.supplier_id')
                            ->leftJoin('users','users.id','=','return_purchased_sheets.returner_id');

        $item = $item->where('return_purchased_sheets.id', $return_purchased_sheet_id);

        $item = $item->selectRaw('return_purchased_sheets.id AS return_purchased_sheet_id, return_purchased_sheets.purchased_sheet_id, return_purchased_sheets.returner_id, users.name AS returner_name, purchased_sheets.supplier_id, suppliers.name AS supplier_name, return_purchased_sheets.total_return_money, return_purchased_sheets.created_datetime');

        $list_item = DB::table('return_purchased_sheets')->where('return_purchased_sheets.branch_id',$branch_id)->where('return_purchased_sheets.id',$return_purchased_sheet_id)
                            ->leftJoin('return_purchased_items','return_purchased_items.return_sheet_id','=','return_purchased_sheets.id')
                            ->leftJoin('purchased_items','purchased_items.id','=','return_purchased_items.purchased_item_id')
                            ->leftJoin('items','items.id','=','purchased_items.item_id');

        $list_item = $list_item->selectRaw('return_purchased_sheets.id AS return_purchased_sheet_id, return_purchased_items.id AS return_purchased_item_id, purchased_items.item_id, items.name, items.image_url, return_purchased_items.old_purchased_price, return_purchased_items.old_quantity');

        $list_item = $list_item->get();
        $item = $item->first();
        $sidebar_code = $this->sidebar_code;
        return view('inventory.return_purchased_sheet.detail', compact('sidebar_code', 'item', 'list_item'));
    }
}