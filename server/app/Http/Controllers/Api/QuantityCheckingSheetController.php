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

class QuantityCheckingSheetController extends Controller
{
    public function createQuantCheckingSheet(Request $request, $store_id, $branch_id)
    {
        /*
            'reason': string,
            'item_list': [
                {
                    item_id: int
                    quantity: int
                    adjustment: increase hoặc decrease
                }
            ]
        */
        $user = auth()->user();
        $data = $request->except('token');
        $rules = [
            'reason'    => 'nullable',
            'item_list' => 'required', 
        ];
        $validator = Validator::make($data, $rules);

        $check_permission = FrequentQuery::checkPermission($user->id, $branch_id, ['purchasing']);

        if(!$validator->fails() && $check_permission){
            $item_list = $request->input('item_list');
            //check if quant and item_idis valid
            foreach($item_list as  $item){
                $item_info_table = FrequentQuery::getItemInfo($branch_id);
                $item_info = $item_info_table->select('items.id', 'item_quantities.quantity')
                                        ->where('items.id', $item['item_id'])
                                        ->first();
                if($item_info !== null){
                    if($item_info->quantity === null){
                        continue;
                    } else if($item_info->quantity < $item['quantity'] && $item['adjustment'] == 'decrease'){
                        $state = 'fail';
                        $errors = 'Item with id '.$item['item_id'].' only has '.$item_info->quantity.' unit while you are asking to '.$item['adjustment'].' '.$item['quantity']. ' unit';
                        return response()->json(compact('state', 'errors', 'data', 'item'));
                    }
                } else {
                    $state = 'fail';
                    $errors = 'Item with id '.$item['item_id'].' doesnt belong to branch_id '.$branch_id;
                    return response()->json(compact('state', 'errors', 'data'));
                }
            }     
            DB::transaction(function () use($request, $user, $branch_id, $item_list){
                //create quantity_checking_sheets
                $quant_checking_sheet_id = DB::table('quantity_checking_sheets')->insertGetID([
                    'branch_id'     => $branch_id,
                    'checker_id'    => $user->id,
                    'reason'        => $request->input('reason'),
                ]);
                //creat multiple quantity_checking_items records
                foreach($item_list as  $item){
                    $item_info_table = FrequentQuery::getItemInfo($branch_id);
                    $item_info = $item_info_table->select('items.id', 'item_quantities.quantity')
                                        ->where('items.id', $item['item_id'])
                                        ->first();
                    $old_quant = $item_info->quantity;
                    $change_quant = $item['quantity'];
                    $sign = $item['adjustment'] == "increase"? "+" : "-";
                    if($item_info !== null && $old_quant !== null){
                        //insert into quantity_checking_items
                        DB::table('quantity_checking_items')->insert([
                            'quant_checking_sheet_id'   => $quant_checking_sheet_id,
                            'item_id'                   => $item['item_id'],
                            'changes'                   => $sign . $change_quant,
                            'old_quant'                 => $old_quant,
                            'new_quant'                 => DB::raw("$old_quant $sign $change_quant"),
                        ]);
                        //update curresponding item quantity
                        $update_item_quant = FrequentQuery::getItemInfo($branch_id);
                        $update_item_quant->where('items.id', $item['item_id'])->update([
                            'item_quantities.quantity'  => DB::raw("item_quantities.quantity $sign $change_quant")
                        ]);
                    }
                }    
            });
            $state ='success';
            $errors = 'none';
            return response()->json(compact('state', 'errors', 'data'));
        } else {
            $state = 'fail';
            $errors = !$check_permission? 'You dont have the permission to do this': $validator->errors();
            return response()->json(compact('state', 'errors', 'data'));
        }
    }
    
    public function getQuantChangeHistoryOfItem(Request $request, $store_id, $branch_id)
    {
        $user = auth()->user();
        $data = $request->except('token');
        $rules = [
            'item_id'                   => 'nullable', 
            'quant_checking_sheet_id'   => 'nullable', 
        ];
        $validator = Validator::make($data, $rules);

        $check_permission = FrequentQuery::checkPermission($user->id, $branch_id, ['purchasing']);
        $check_both = 0;
        $check_id = 0;
        
        if($request->input('item_id') XOR $request->input('quant_checking_sheet_id')){
            $check_both = 1;
            if($request->input('item_id')){
                $check_id = FrequentQuery::getItemInfo($branch_id);
                $check_id = $check_id->where('items.id', $request->input('item_id'))->exists();
            } else if($request->input('quant_checking_sheet_id')){
                $check_id = DB::table('quantity_checking_sheets')
                            ->where('quantity_checking_sheets.branch_id', $branch_id)
                            ->where('quantity_checking_sheets.id', $request->input('quant_checking_sheet_id'))
                            ->exists();
            }
        }

        if(!$validator->fails() && $check_permission && $check_id && $check_both){
            $quant_change_history = DB::table('quantity_checking_sheets')
                            ->leftJoin('quantity_checking_items', 'quantity_checking_items.quant_checking_sheet_id', '=', 'quantity_checking_sheets.id')
                            ->leftJoin('users', 'users.id', '=','quantity_checking_sheets.checker_id');

            if($request->input('item_id')){
                $quant_change_history = $quant_change_history->where('quantity_checking_items.item_id', $request->input('item_id'));
            } else if($request->input('quant_checking_sheet_id')){
                $quant_change_history = $quant_change_history->where('quantity_checking_sheets.id', $request->input('quant_checking_sheet_id'));
            }                
            
            $quant_change_history = $quant_change_history->selectRaw('quantity_checking_sheets.id AS quant_checking_sheet_id, quantity_checking_items.item_id, quantity_checking_items.changes, quantity_checking_items.old_quant, quantity_checking_items.new_quant, quantity_checking_sheets.checker_id, users.name AS checker_name, quantity_checking_sheets.reason, quantity_checking_sheets.created_datetime');
            $quant_change_history = $quant_change_history->orderByRaw('quantity_checking_sheets.created_datetime DESC');
            $quant_change_history = $quant_change_history->get();

            $state ='success';
            $errors = 'none';
            return response()->json(compact('state', 'errors', 'data', 'quant_change_history'));
        } else {
            $state = 'fail';            
            if(!$check_permission){
                $errors = 'You dont have the permission to do this';
            } else if(!$check_both){
                $errors = "You can only pick one and only one, either item_id or quant_checking_sheet_id";
            } else if(!$check_id){
                $errors = "item id or sheet id doesnt belong to branch";
            } else if($validator->fails()){
                $errors = $validator->errors();
            }
            return response()->json(compact('state', 'errors', 'data'));
        }
    }
}