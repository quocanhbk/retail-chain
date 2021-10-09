<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use App\FrequentQuery;
use App\TokenController;

class EmployeeController extends Controller
{
    public function createEmployee(Request $request, $store_id, $branch_id){
        $owner = auth()->user();
        $data = $request->except('token');
        $rules = [
            'name'              => 'required',
            'username'          => 'required|unique:users',
            'password'          => 'required',
            'email'             => 'unique:users|email',
            'phone'             => '',
            'date_of_birth'     => 'date_format:Y-m-d',
            'gender'            => 'in:male,female',

            'selling'           => 'required|boolean',
            'managing'          => 'required|boolean',
            'purchasing'        => 'required|boolean',
            'reporting'         => 'required|boolean',
        ];
        $validator = Validator::make($data, $rules);
        
        if($validator->fails()){
            $state = 'fail';
            $errors = $validator->errors();
            return response()->json(compact('state', 'errors', 'data'));
        } else {
            DB::transaction(function () use ($request, $branch_id) {

                $user_id = DB::table('users')->insertGetId([
                    'name'          => $request->input('name'),
                    'username'      => $request->input('username'),
                    'password'      => Hash::make($request->input('password')),
                    'email'         => $request->input('email'),
                    'phone'         => $request->input('phone'),
                    'date_of_birth' => $request->input('date_of_birth'),
                    'gender'        => $request->input('gender'),
                ]);
                $roles = DB::table('roles')->get();
                foreach($roles as $role){
                    if($request->input($role->name) ){
                        DB::table('works')->insert([
                            'branch_id' => $branch_id,
                            'user_id'   => $user_id,
                            'role_id'   => $role->id,
                        ]);
                    }
                }
            });
            
            $state = 'success';
            $errors = 'none';
            return response()->json(compact('state', 'errors', 'data'));
            // if($user_id && $user_right){
            //     $state = 'success';
            //     $errors = 'none';
            //     return response()->json(compact('state', 'errors', 'data'));
            // } else {
            //     $state = 'fail';
            //     if(!$user_id){
            //         $errors = "Can't insert user";
            //     } else if(!$user_right){
            //         $errors = "Can't insert user rights";
            //     }
            //     return response()->json(compact('state', 'errors', 'data'));
            // }
            // return response()->json(compact('store','store_id'));
        }
    }

    public function getEmployee(Request $request, $store_id, $branch_id)
    {
        $user = auth()->user();
        $data = $request->except('token');
        $rules = [
            'user_id'       => ['nullable',Rule::exists('users','id')],
        ];
        $validator = Validator::make($data, $rules);

        $check_permission = FrequentQuery::checkPermission($user->id, $branch_id, ['managing']);

        if(!$validator->fails() && $check_permission){
            $is_owner = DB::table('stores')
                    ->where('owner_id', $user->id)
                    ->where('id', $store_id)
                    ->exists();

            $employee_list = FrequentQuery::getEmployeeListExcludeOwner($branch_id, $is_owner);
            $employee_list = $request->input('user_id')? $employee_list->where('users.id', $request->input('user_id')) : $employee_list;
            $employee_list = $employee_list->get();

            if($is_owner){
                for($i = 0; $i < count($employee_list); $i++){
                    $roles =  DB::table('works')->where('works.user_id', $employee_list[$i]->user_id)->where('works.branch_id', $branch_id)
                                ->leftJoin('roles', 'roles.id','=','works.role_id')
                                ->selectRaw('roles.name')
                                ->get();
                    $role_array = [];
                    foreach($roles as $role){
                        $role_array[] = $role->name; 
                    }           
                    $employee_list[$i]->roles = $role_array;
                }
            }
            
            $state ='success';
            $errors = 'none';
            return response()->json(compact('state', 'errors', 'data', 'employee_list'));
        } else {
            $state = 'fail';
            if(!$check_permission){
                $errors = 'You dont have the permission to do this';
            } else if($validator->fails()){
                $errors = $validator->errors();
            }
            return response()->json(compact('state', 'errors', 'data'));
        }
    }
    //only owner
    public function resetEmployeePassword(Request $request, $store_id, $branch_id)
    {
        $user = auth()->user();
        $data = $request->except('token');
        $rules = [
            'user_id'       => ['required',Rule::exists('works','user_id')->where('branch_id',$branch_id)],
            'new_password'  => 'required',
        ];
        $validator = Validator::make($data, $rules);
        $is_employee = FrequentQuery::isEmployee($request->input('user_id'), $branch_id, $store_id);


        if(!$validator->fails() && $is_employee){
            DB::transaction(function () use ($request) {
                $user_id = DB::table('users')->where('users.id', $request->input('user_id'))
                    ->update([
                    'password'      => Hash::make($request->input('new_password'))
                ]);
            });
            
            TokenController::invalidateByID($request->input('user_id'));
            $state ='success';
            $errors = 'none';
            return response()->json(compact('state', 'errors', 'data'));
        } else {
            $state = 'fail';
            if($validator->fails()){
                $errors = $validator->errors();
            } else if(!$is_employee){
                $errors = "user_id: ".$request->input('user_id')." is not employee of branch_id: ".$branch_id;
            }
            return response()->json(compact('state', 'errors', 'data'));
        }
    }
    //only owner
    public function changeEmployeeInfo(Request $request, $store_id, $branch_id)
    {
        $user = auth()->user();
        $data = $request->except('token');
        $rules = [
            'user_id'           => ['required',Rule::exists('works','user_id')->where('branch_id',$branch_id)],
            'name'              => 'required',
            'email'             => ['nullable','email',
                                    Rule::unique('users','email')
                                        ->where('status', 'enable')
                                        ->ignore($request->input('user_id'))
                                    ],
            'phone'             => 'nullable',
            'date_of_birth'     => 'nullable|date_format:Y-m-d',
            'gender'            => 'nullable|in:male,female',

            'role_list'         => 'required|array',
            'role_list.*'       => ['required',Rule::exists('roles','name')]
        ];
        $validator = Validator::make($data, $rules);
        $is_employee = FrequentQuery::isEmployee($request->input('user_id'), $branch_id, $store_id);

        if(!$validator->fails() && $is_employee){
            DB::transaction(function () use ($branch_id, $request){
                $user_id = $request->input('user_id');
                DB::table('users')->where('users.id', $user_id)
                    ->update([
                        'name'          => $request->input('name'),
                        'email'         => $request->input('email'),
                        'phone'         => $request->input('phone'),
                        'date_of_birth' => $request->input('date_of_birth'),
                        'gender'        => $request->input('gender'),
                    ]);
                    
                $role_list = $request->input('role_list');
                DB::table('works')->where('user_id', $user_id)->where('branch_id', $branch_id)->delete();
                foreach($role_list as $role_name){
                    $role_id = DB::table('roles')->where('roles.name', $role_name)->select('roles.id')->first();
                    $role_id = $role_id->id;
                    DB::table('works')->insert([
                        'branch_id'     => $branch_id,
                        'user_id'       => $user_id,
                        'role_id'       => $role_id
                    ]);
                };
            });
            
            $state ='success';
            $errors = 'none';
            return response()->json(compact('state', 'errors', 'data'));
        } else {
            $state = 'fail';
            if($validator->fails()){
                $errors = $validator->errors();
            } else if(!$is_employee){
                $errors = "user_id: ".$request->input('user_id')." is not employee of branch_id: ".$branch_id;
            }
            return response()->json(compact('state', 'errors', 'data'));
        }
    }
    //only owner
    public function changeEmployeeStatus(Request $request, $store_id, $branch_id)
    {
        $user = auth()->user();
        $data = $request->except('token');
        $rules = [
            'user_id'   => ['required',Rule::exists('works','user_id')->where('branch_id',$branch_id)],
            'status'    => 'in:enable,disable|required'        
        ];
        $validator = Validator::make($data, $rules);
        $is_employee = FrequentQuery::isEmployee($request->input('user_id'), $branch_id, $store_id);

        if(!$validator->fails() && $is_employee){
            DB::transaction(function () use ($branch_id, $request){
                $user_id = $request->input('user_id');
                DB::table('users')->where('users.id', $user_id)
                    ->update([
                        'status'    => $request->input('status'),
                    ]);
            });
            if($request->input('status') == "disable"){
                $user_id = $request->input('user_id');
                $token = auth()->tokenById($user_id);
                auth()->setToken($token)->invalidate();
            }
            
            $state ='success';
            $errors = 'none';
            return response()->json(compact('state', 'errors', 'data'));
        } else {
            $state = 'fail';
            if($validator->fails()){
                $errors = $validator->errors();
            } else if(!$is_employee){
                $errors = "user_id: ".$request->input('user_id')." is not employee of branch_id: ".$branch_id;
            }
            return response()->json(compact('state', 'errors', 'data'));
        }
    }
    
    public function getActionHistory(Request $request, $store_id, $branch_id){
        $user = auth()->user();
        $data = $request->except('token');
        $rules = [
            'user_id'   => [Rule::exists('works','user_id')->where('branch_id',$branch_id)],
            'from_date' => 'date_format:Y-m-d',
            'to_date'   => 'date_format:Y-m-d',
            'type'      => 'in:invoices,purchased_sheets,refund_sheets,return_purchased_sheets,quantity_checking_sheets'
        ];
        $validator = Validator::make($data, $rules);
        
        $check_permission = FrequentQuery::checkPermission($user->id, $branch_id, []);
        if(!$validator->fails() && $check_permission){
            $invoice_history = DB::table('invoices')->where('invoices.branch_id', $branch_id)
                                    ->leftJoin('users', 'users.id', '=', 'invoices.seller_id');
            $purchased_history = DB::table('purchased_sheets')->where('purchased_sheets.branch_id', $branch_id)
                                    ->leftJoin('users', 'users.id', '=', 'purchased_sheets.purchaser_id')
                                    ->where('purchased_sheets.total_purchase_price', '>', 0);
            $refund_history = DB::table('refund_sheets')->where('refund_sheets.branch_id', $branch_id)
                                ->leftJoin('users', 'users.id', '=', 'refund_sheets.refunder_id');
            $return_purchased_history = DB::table('return_purchased_sheets')->where('return_purchased_sheets.branch_id', $branch_id)
                                            ->leftJoin('users', 'users.id', '=', 'return_purchased_sheets.returner_id');
            $quant_checking_history = DB::table('quantity_checking_sheets')->where('quantity_checking_sheets.branch_id', $branch_id)
                                            ->leftJoin('users', 'users.id', '=', 'quantity_checking_sheets.checker_id');
                                            
            if($request->input('user_id')){
                $invoice_history = $invoice_history->where('invoices.seller_id', $request->input('user_id'));
                $purchased_history = $purchased_history->where('purchased_sheets.purchaser_id', $request->input('user_id'));
                $refund_history = $refund_history->where('refund_sheets.refunder_id', $request->input('user_id'));
                $return_purchased_history = $return_purchased_history->where('return_purchased_sheets.returner_id', $request->input('user_id'));
                $quant_checking_history = $quant_checking_history->where('quantity_checking_sheets.checker_id', $request->input('user_id'));
            }
            if($request->input('from_date')){
                $invoice_history = $invoice_history->where('invoices.created_datetime', '>=', $request->input('from_date')." 00:00:00");
                $purchased_history = $purchased_history->where('purchased_sheets.delivery_datetime', '>=', $request->input('from_date')." 00:00:00");
                $refund_history = $refund_history->where('refund_sheets.created_datetime', '>=', $request->input('from_date')." 00:00:00");
                $return_purchased_history = $return_purchased_history->where('return_purchased_sheets.created_datetime', '>=', $request->input('from_date')." 00:00:00");
                $quant_checking_history = $quant_checking_history->where('quantity_checking_sheets.created_datetime', '>=', $request->input('from_date')." 00:00:00");
            }
            if($request->input('to_date')){
                $invoice_history = $invoice_history->where('invoices.created_datetime', '<=', $request->input('to_date')." 23:59:59");
                $purchased_history = $purchased_history->where('purchased_sheets.delivery_datetime', '<=', $request->input('to_date')." 23:59:59");
                $refund_history = $refund_history->where('refund_sheets.created_datetime', '<=', $request->input('to_date')." 23:59:59");
                $return_purchased_history = $return_purchased_history->where('return_purchased_sheets.created_datetime', '<=', $request->input('to_date')." 23:59:59");
                $quant_checking_history = $quant_checking_history->where('quantity_checking_sheets.created_datetime', '<=', $request->input('to_date')." 23:59:59");
            }
            
            $invoice_history = $invoice_history->selectRaw('invoices.id, users.name AS user_name, invoices.created_datetime AS created_datetime');
            $purchased_history = $purchased_history->selectRaw('purchased_sheets.id, users.name AS user_name, purchased_sheets.delivery_datetime AS created_datetime');
            $refund_history = $refund_history->selectRaw('refund_sheets.id, users.name AS user_name, refund_sheets.created_datetime AS created_datetime');
            $return_purchased_history = $return_purchased_history->selectRaw('return_purchased_sheets.id, users.name AS user_name, return_purchased_sheets.created_datetime AS created_datetime');
            $quant_checking_history = $quant_checking_history->selectRaw('quantity_checking_sheets.id, users.name AS user_name, quantity_checking_sheets.created_datetime AS created_datetime');
            
            $invoice_history = $invoice_history->orderByRaw('invoices.created_datetime DESC');
            $purchased_history = $purchased_history->orderByRaw('purchased_sheets.delivery_datetime DESC');
            $refund_history = $refund_history->orderByRaw('refund_sheets.created_datetime DESC');
            $return_purchased_history = $return_purchased_history->orderByRaw('return_purchased_sheets.created_datetime DESC');
            $quant_checking_history = $quant_checking_history->orderByRaw(' quantity_checking_sheets.created_datetime DESC');

            $merged_array = [];

            switch($request->input('type')){
                case 'invoices':
                    $invoice_history = $invoice_history->get();
                    $this->append_text_to_array($invoice_history, 'create', 'invoices');
                    $merged_array = $this->merge_arr($merged_array, $invoice_history);
                    break;
                case 'purchased_sheets':
                    $purchased_history = $purchased_history->get();
                    $this->append_text_to_array($purchased_history, 'create', 'purchased_sheets');
                    $merged_array = $this->merge_arr($merged_array, $purchased_history);
                    break;
                case 'refund_sheets':
                    $refund_history = $refund_history->get();
                    $this->append_text_to_array($refund_history, 'create', 'refund_sheets');
                    $merged_array = $this->merge_arr($merged_array, $refund_history);
                    break;
                case 'return_purchased_sheets':
                    $return_purchased_history = $return_purchased_history->get();
                    $this->append_text_to_array($return_purchased_history, 'create', 'return_purchased_sheets');
                    $merged_array = $this->merge_arr($merged_array, $return_purchased_history);
                    break;
                case 'quantity_checking_sheets':
                    $quant_checking_history = $quant_checking_history->get();
                    $this->append_text_to_array($quant_checking_history, 'create', 'quantity_checking_sheets');
                    $merged_array = $this->merge_arr($merged_array, $quant_checking_history);
                    break;
                default:
                    $invoice_history = $invoice_history->get();
                    $purchased_history = $purchased_history->get();
                    $refund_history = $refund_history->get();
                    $return_purchased_history = $return_purchased_history->get();
                    $quant_checking_history = $quant_checking_history->get();

                    $this->append_text_to_array($invoice_history, 'create', 'invoices');
                    $this->append_text_to_array($purchased_history, 'create', 'purchased_sheets');
                    $this->append_text_to_array($refund_history, 'create', 'refund_sheets');
                    $this->append_text_to_array($return_purchased_history, 'create', 'return_purchased_sheets');
                    $this->append_text_to_array($quant_checking_history, 'create', 'quantity_checking_sheets');

                    $merged_array = $this->merge_arr($merged_array, $invoice_history);
                    $merged_array = $this->merge_arr($merged_array, $purchased_history);
                    $merged_array = $this->merge_arr($merged_array, $refund_history);
                    $merged_array = $this->merge_arr($merged_array, $return_purchased_history);
                    $merged_array = $this->merge_arr($merged_array, $quant_checking_history);
                    break;
            }
            usort($merged_array, array($this, "sort_arr_obj_logic"));
            
            $state ='success';
            $errors = 'none';
            return response()->json(compact('state', 'errors', 'data', 'merged_array'));
        } else {
            $state = 'fail';
            if(!$check_permission){
                $errors = 'You dont have the permission to do this';
            } else if($validator->fails()){
                $errors = $validator->errors();
            }
            return response()->json(compact('state', 'errors', 'data'));
        }
    }

    private function merge_arr($arr_merge_to, $arr_source){
        foreach($arr_source as $arr){
            $arr_merge_to[] = $arr;
        }
        return $arr_merge_to;
    }
    
    private function sort_arr_obj_logic($a, $b){
        if ($a->created_datetime == $b->created_datetime) {
            return 0;
        }
        return ($a->created_datetime < $b->created_datetime) ? 1 : -1;
    }
    
    private function append_text_to_array(&$array, $action, $type){
        foreach($array as &$element){
            $element->action = $action;
            $element->type = $type;
        }
    }
}
