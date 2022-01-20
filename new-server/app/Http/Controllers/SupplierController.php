<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\Supplier;

class SupplierController extends Controller
{
    public function create(Request $request) {
        $store_id = Auth::guard('stores')->user()->id;
        $data = $request->all();
        error_log($data["name"]);
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('suppliers')->where('store_id', $store_id)],
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 400);
        }

        $supplier = Supplier::create([
            'store_id' => $store_id,
            'name' => $data['name'],
            'address' => $data['address'],
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'],
        ]);

        return response()->json($supplier);
    }

    public function getSuppliers(Request $request) {
        $store_id = $request->get('store_id');
        $suppliers = Supplier::where('store_id', $store_id)->get();
        return response()->json($suppliers);
    }

    public function getSupplier(Request $request, $supplier_id) {
        $store_id = $request->get('store_id');
        $supplier = Supplier::where('store_id', $store_id)->where('id', $supplier_id)->first();
        if (!$supplier) {
            return response()->json([
                'message' => 'Supplier not found.',
            ], 404);
        }

        return response()->json($supplier);
    }

    public function update(Request $request, $supplier_id) {
        $store_id = $request->get('store_id');
        $data = $request->all();
        $data['supplier_id'] = $supplier_id;
        $rules = [
            'supplier_id' => ['required', 'integer', Rule::exists('suppliers', 'id')->where('store_id', $store_id)],
            'name' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'email', 'max:255', Rule::unique('suppliers')->where('store_id', $store_id)->ignore($supplier_id)]
        ];

        $validator = Validator::make($data, $rules);
        if($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 400);
        }
        error_log($data['name']);
        $supplier = Supplier::where('store_id', $store_id)->where('id', $supplier_id)->first();
        $supplier->name = $data['name'] ?? $supplier->name;
        $supplier->address = $data['address'] ?? $supplier->address;
        $supplier->phone = $data['phone'] ?? $supplier->phone;
        $supplier->email = $data['email'] ?? $supplier->email;
        $supplier->save();
        error_log($supplier);
        return response()->json($supplier);
    }

    public function delete(Request $request, $supplier_id) {
        $store_id = $request->get('store_id');
        $supplier = Supplier::where('store_id', $store_id)->where('id', $supplier_id)->first();
        if (!$supplier) {
            return response()->json([
                'message' => 'Supplier not found.',
            ], 404);
        }

        $supplier->delete();

        return response()->json($supplier);
    }
}
