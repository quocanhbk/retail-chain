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

class CustomerController extends Controller
{
    public function createCustomer(Request $request, $store_id, $branch_id)
    {
        $user = auth()->user();
        $data = $request->except('token');
        $rules = [
            'name'          => '',
            'email'         => 'email',
            'phone'         => ['required',Rule::unique('customers','phone')->where('branch_id', $branch_id)->where('deleted', 0)],
            'date_of_birth' => 'date_format:Y-m-d',
            'gender'        => 'in:male,female',
            'address'       => ''
        ];
        $validator = Validator::make($data, $rules);
        $check_permission = FrequentQuery::checkPermission($user->id, $branch_id, ['selling','reporting']);

        if($check_permission && !$validator->fails()){
            DB::transaction(function () use ($request, $branch_id) {
                $customer_id = DB::table('customers')->insertGetId([
                    'branch_id'     => $branch_id,
                    'name'          => $request->input('name'),
                    'email'         => $request->input('email'),
                    'phone'         => $request->input('phone'),
                    'date_of_birth' => $request->input('date_of_birth'),
                    'gender'        => $request->input('gender'),
                    'address'       => $request->input('address'),
                    'email'         => $request->input('email'),
                ]);
            });
            $state = 'success';
            $errors = 'none';
            return response()->json(compact('state', 'errors', 'data'));
        } else {
            $state = 'fail';
            $errors = !$check_permission? 'You dont have the permission to do this':$validator->errors();
            return response()->json(compact('state', 'errors', 'data'));
        }
    }

    public function getCustomer(Request $request, $store_id, $branch_id){
        $user = auth()->user();
        $data = $request->except('token');
        $rules = [
            'customer_id'   => ['integer',Rule::exists('customers','id')->where('branch_id',$branch_id)]
        ];
        $validator = Validator::make($data, $rules);
        $check_permission = FrequentQuery::checkPermission($user->id, $branch_id, ['selling','reporting']);

        if($check_permission && !$validator->fails()){
            $customer = DB::table('customers')
                        ->where('branch_id', $branch_id)
                        ->where('deleted', 0);
            $customer = $request->input('customer_id')? $customer->where('id', $request->input('customer_id')) : $customer;
            $customer = $customer->get();

            $state = 'success';
            $errors = 'none';
            return response()->json(compact('state', 'errors', 'data', 'customer'));
        } else {
            $state = 'fail';
            $errors = !$check_permission? 'You dont have the permission to do this':$validator->errors();
            return response()->json(compact('state', 'errors', 'data'));
        }
    }

    public function editCustomer(Request $request, $store_id, $branch_id)
    {
        $user = auth()->user();
        $data = $request->except('token');
        $rules = [
            'customer_id'   => ['required', Rule::exists('customers', 'id')->where('branch_id', $branch_id)->where('deleted', 0)],
            'deleted'       => 'required|boolean',
            'phone'         => [Rule::requiredIf($request->input('deleted') == 0),
                                Rule::unique('customers','phone')
                                    ->where('branch_id', $branch_id)
                                    ->where('deleted', 0)
                                    ->ignore($request->input('customer_id'))],
            'date_of_birth' => 'nullable|date_format:Y-m-d',
            'gender'        => 'nullable|in:male,female',
            'address'       => '',
            'name'          => '',
            'email'         => 'nullable|email',
        ];
        $validator = Validator::make($data, $rules);
        $check_permission = FrequentQuery::checkPermission($user->id, $branch_id, ['selling','reporting']);

        if($check_permission && !$validator->fails()){
            $customer = DB::table('customers')
                            ->where('id', $request->input('customer_id'))
                            ->where('branch_id', $branch_id)
                            ->where('deleted', 0);
            DB::transaction(function () use ($request, $customer){
                if($request->input('deleted')){
                    $customer = $customer->update([
                        'deleted'       => 1
                    ]);
                } else {
                    $customer = $customer->update([
                        'name'          => $request->input('name'),
                        'email'         => $request->input('email'),
                        'phone'         => $request->input('phone'),
                        'date_of_birth' => $request->input('date_of_birth'),
                        'gender'        => $request->input('gender'),
                        'address'       => $request->input('address'),
                        'email'         => $request->input('email'),
                    ]);
                }
            });

            $state = 'success';
            $errors = 'none';
            return response()->json(compact('state', 'errors', 'data'));
        } else {
            $state = 'fail';
            $errors = !$check_permission? 'You dont have the permission to do this':$validator->errors();
            return response()->json(compact('state', 'errors', 'data'));
        }
    }
}