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
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:255', Rule::unique('suppliers')->where('store_id', $store_id)],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:255', Rule::unique('suppliers')->where('store_id', $store_id)],
            'email' => ['nullable', 'string', 'email', 'max:255', Rule::unique('suppliers')->where('store_id', $store_id)],
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 400);
        }

        // create code if not provided
        if (!$data['code']) {
            $supplier_count = Supplier::where('store_id', $store_id)->count();
            $data['code'] = 'SUP' . str_pad($supplier_count + 1, 6, '0', STR_PAD_LEFT);
        }

        $supplier = Supplier::create([
            'store_id' => $store_id,
            'name' => $data['name'],
            'phone' => $data['phone'],
            'email' => $data['email'] ?? null,
            'address' => $data['address'] ?? null,
            'code' => $data['code'],
        ]);

        return response()->json($supplier);
    }

    public function getSuppliers(Request $request) {
        $store_id = $request->get('store_id');
        $search = $request->query('search');
        // search for suppliers by name, phone, email, code
        $suppliers = Supplier::where('store_id', $store_id)
            ->where('name', 'like', '%' . $search . '%')
            ->orWhere('phone', 'like', '%' . $search . '%')
            ->orWhere('email', 'like', '%' . $search . '%')
            ->orWhere('code', 'like', '%' . $search . '%')
            ->get();

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

        $supplier = Supplier::where('store_id', $store_id)->where('id', $supplier_id)->first();
        $supplier->name = $data['name'] ?? $supplier->name;
        $supplier->address = $data['address'] ?? $supplier->address;
        $supplier->phone = $data['phone'] ?? $supplier->phone;
        $supplier->email = $data['email'] ?? $supplier->email;
        $supplier->save();

        return response()->json($supplier);
    }
}
