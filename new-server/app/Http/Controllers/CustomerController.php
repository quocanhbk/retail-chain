<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\Customer;
use Illuminate\Support\Str;

class CustomerController extends Controller
{
    // only for saler
    public function create(Request $request) {
        $store_id = Auth::user()->store_id;
        $data = $request->all();
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:255', Rule::unique('customers')->where('store_id', $store_id)],
            'email' => ['nullable', 'string', 'email', 'max:255', Rule::unique('customers')->where('store_id', $store_id)],
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
            'code' => Str::uuid(),
            'name' => $data['name'],
            'phone' => $data['phone'],
            'email' => $data['email'],
        ]);

        return response()->json($customer);
    }

    public function getCustomers(Request $request) {
        $store_id = Auth::user()->store_id;
        $search = $request->query('search');

        $customers = Customer::where('store_id', $store_id)
            ->where('name', 'like', '%'. $search . '%')
            ->orWhere('phone', 'like', '%' . $search . '%')
            ->orWhere('email', 'like', '%' . $search . '%')
            ->get();

        return response()->json($customers);
    }

    public function getCustomer(Request $request, $id) {
        $store_id = Auth::user()->store_id;
        $customer = Customer::where('store_id', $store_id)->where('id', $id)->first();
        if (!$customer) {
            return response()->json([
                'message' => 'Customer not found.',
            ], 404);
        }

        return response()->json($customer);
    }

    public function getCustomerByCode(Request $request, $code) {
        $store_id = Auth::user()->store_id;
        $customer = Customer::where('store_id', $store_id)->where('code', $code)->first();
        if (!$customer) {
            return response()->json([
                'message' => 'Customer not found.',
            ], 404);
        }

        return response()->json($customer);
    }

    public function update(Request $request, $id) {
        $store_id = Auth::user()->store_id;
        $data = $request->all();
        $data['id'] = $id;
        $rules = [
            'id' => ['required', 'integer', Rule::exists('customer', 'id')->where('store_id', $store_id)],
            'name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'email', 'max:255']
        ];

        $validator = Validator::make($data, $rules);

        if($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 400);
        }

        $customer = Customer::where('store_id', $store_id)->where('id', $id)->first();
        $customer->name = $data['name'] ?? $customer->name;
        $customer->phone = $data['phone'] ?? $customer->phone;
        $customer->email = $data['email'] ?? $customer->email;
        $customer->save();

        return response()->json($customer);
    }

    public function addPoint(Request $request, $id) {
        $store_id = Auth::user()->store_id;
        $data = $request->all();
        $data['id'] = $id;
        $rules = [
            'id' => ['required', 'integer', Rule::exists('customer', 'id')->where('store_id', $store_id)],
            'point' => ['required', 'integer', 'min:1']
        ];

        $validator = Validator::make($data, $rules);

        if($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 400);
        }

        $customer = Customer::where('store_id', $store_id)->where('id', $id)->first();
        $customer->point += $data['point'];
        $customer->save();

        return response()->json($customer);
    }

    public function usePoint(Request $request, $id) {
        $store_id = Auth::user()->store_id;
        $data = $request->all();
        $data['id'] = $id;
        $rules = [
            'id' => ['required', 'integer', Rule::exists('customer', 'id')->where('store_id', $store_id)],
            'point' => ['required', 'integer', 'min:1']
        ];

        $validator = Validator::make($data, $rules);

        if($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 400);
        }

        $customer = Customer::where('store_id', $store_id)->where('id', $id)->first();
        if ($customer->point < $data['point']) {
            return response()->json([
                'message' => 'Not enough point.',
            ], 400);
        }
        $customer->point -= $data['point'];
        $customer->save();

        return response()->json($customer);
    }
}
