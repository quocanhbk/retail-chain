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

class SupplierController extends Controller
{
    public function createSupplier(Request $request, $store_id, $branch_id)
    {
        $user = auth()->user();
        $data = $request->except('token');
        $rules = [
            'name'          => 'required',
            'email'         => 'email',
            'phone'         => '',
            'address'       => '',
        ];
        $validator = Validator::make($data, $rules);

        $check_permission = FrequentQuery::checkPermission($user->id, $branch_id, ['purchasing']);
        if($check_permission && !$validator->fails()){
            DB::transaction(function () use ($request, $branch_id) {
                $supplier_id = DB::table('suppliers')->insertGetId([
                    'branch_id'     => $branch_id,
                    'name'          => $request->input('name'),
                    'email'         => $request->input('email'),
                    'phone'         => $request->input('phone'),
                    'address'       => $request->input('address'),
                ]);
            });
                $state = 'success';
                $errors = 'none';
                return response()->json(compact('state', 'errors', 'data'));
        } else {
            $state = 'fail';
            $errors = !$check_permission? 'You dont have the permission to do this': $validator->errors();
            return response()->json(compact('state', 'errors', 'data'));
        }
    }

    public function getSupplier(Request $request, $store_id, $branch_id)
    {
        $user = auth()->user();
        $data = $request->except('token');
        $rules = [
            'supplier_id'   => ['integer',Rule::exists('suppliers','id')->where('branch_id',$branch_id)]
        ];
        $validator = Validator::make($data, $rules);

        $check_permission = FrequentQuery::checkPermission($user->id, $branch_id, ['purchasing']);
        if($check_permission && !$validator->fails()){
            $supplier = DB::table('suppliers')
                        ->where('branch_id', $branch_id)
                        ->where('deleted',0);
            $supplier = $request->input('supplier_id')? $supplier->where('id', $request->input('supplier_id')) : $supplier;
            $supplier = $supplier->get();

            $state ='success';
            $errors = 'none';
            return response()->json(compact('state', 'errors', 'data', 'supplier'));
        } else {
            $state = 'fail';
            $errors = !$check_permission? 'You dont have the permission to do this': $validator->errors();
            return response()->json(compact('state', 'errors', 'data'));
        }
    }

    public function editSupplier(Request $request, $store_id, $branch_id)
    {
        $user = auth()->user();
        $data = $request->except('token');
        $rules = [
            'supplier_id'   => ['required', Rule::exists('suppliers', 'id')->where('branch_id', $branch_id)->where('deleted', 0)],
            'deleted'       => 'required|boolean',
            'name'          => [Rule::requiredIf($request->input('deleted') == 0)],
            'email'         => 'nullable|email',
            'phone'         => '',
            'address'       => '',
        ];
        $validator = Validator::make($data, $rules);
        $check_permission = FrequentQuery::checkPermission($user->id, $branch_id, ['purchasing']);

        if($check_permission && !$validator->fails()){
            $supplier = DB::table('suppliers')
                            ->where('id', $request->input('supplier_id'))
                            ->where('branch_id', $branch_id)
                            ->where('deleted', 0);
            DB::transaction(function () use ($request, $supplier) {
                if($request->input('deleted')){
                    $supplier = $supplier->update([
                        'deleted'       => 1,
                    ]);
                } else {
                    $supplier = $supplier->update([
                        'name'          => $request->input('name'),
                        'email'         => $request->input('email'),
                        'phone'         => $request->input('phone'),
                        'address'       => $request->input('address'),
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