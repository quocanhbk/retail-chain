<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\Customer;

class CustomerController extends Controller
{
    public function create(Request $request) {
        $store_id = Auth::guard('stores')->user()->id;
        $data = $request->all();
        error_log($data["name"]);
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'birthday' => ['nullable', 'date'],
            'gender' => ['nullable', 'string']
 ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 400);
        }

        $customer = Customer::create([
            'store_id' => $store_id,
            'name' => $data['name'],
            'address' => $data['address'],
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'],
            'birthday' => $data['birthday'],
            'gender' => $data['gender'],

        ]);

        return response()->json($customer);
    }

    public function getAllCustomers(Request $request) {
        $store_id = Auth::guard('stores')->user()->id;
        $customer = Customer::where('store_id', $store_id)->get();
        return response()->json($customer);
    }    

    public function getCustomer(Request $request, $customer_id) {
        $store_id = Auth::guard('stores')->user()->id;
        $customer = Customer::where('store_id', $store_id)->where('id', $customer_id)->first();
        if (!$customer) {
            return response()->json([
                'message' => 'Customer not found.',
            ], 404);
        }

        return response()->json($customer);

    }

    public function update(Request $request, $customer_id) {
        $store_id = Auth::guard('stores')->user()->id;
        $data = $request->all();
        $data['customer_id'] = $customer_id;
        $rules = [
            'customer_id' => ['required', 'integer', Rule::exists('customer', 'id')->where('store_id', $store_id)],
            'name' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'email', 'max:255'],
            'birthday' => ['nullable','date'],
            'gender' => ['nullable', 'string']
        ];

        $validator = Validator::make($data, $rules);
        if($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 400);
        }
        error_log($data['name']);
        $customer = Customer::where('store_id', $store_id)->where('id', $customer_id)->first();
        $customer->name = $data['name'] ?? $customer->name;
        $customer->address = $data['address'] ?? $customer->address;
        $customer->phone = $data['phone'] ?? $customer->phone;
        $customer->email = $data['email'] ?? $customer->email;
        $customer->birthday = $data['birthday'] ?? $customer->birthday;
        $customer->gender = $data['gender'] ?? $customer->gender;
        $customer->save();
        error_log($customer);
        return response()->json($customer);
    }

    public function delete(Request $request, $supplier_id) {
        $store_id = Auth::guard('stores')->user()->id;
        $customer = Customer::where('store_id', $store_id)->where('id', $customer_id)->first();
        if (!$customer) {
            return response()->json([
                'message' => 'Customer not found.',
            ], 404);
        }

        $customer->delete();

        return response()->json($customer);
    }

}
