<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class StoreController extends Controller
{
    /** Register new store */
    public function register(Request $request) {

        $data = $request->all();
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:stores'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'remember' => ['nullable', 'boolean'],
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 400);
        }

        $store = Store::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $remember = $data['remember'] ?? false;
        Auth::guard('stores')->login($store, $remember);

        $store->categories()->createMany([
            ['name' => 'ĐỒ UỐNG CÁC LOẠI'],
            ['name' => 'SỮA UỐNG CÁC LOẠI'],
            ['name' => 'BÁNH KẸO CÁC LOẠI'],
            ['name' => 'MÌ, CHÁO, PHỞ, BÚN'],
            ['name' => 'DẦU ĂN, GIA VỊ'],
            ['name' => 'GẠO, BỘT, ĐỒ KHÔ'],
            ['name' => 'ĐỒ MÁT, ĐÔNG LẠNH'],
            ['name' => 'TÃ, ĐỒ CHO BÉ'],
            ['name' => 'CHĂM SÓC CÁ NHÂN'],
            ['name' => 'VỆ SINH NHÀ CỬA'],
            ['name' => 'ĐỒ DÙNG GIA ĐÌNH'],
            ['name' => 'VĂN PHÒNG PHẨM'],
            ['name' => 'THUỐC VÀ THỰC PHẨM CHỨC NĂNG']
        ]);

        return response()->json($store);
    }

    /** Login store */
    public function login(Request $request) {
        $data = $request->all();
        $rules = [
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:6'],
            'remember' => ['boolean', 'nullable'],
        ];
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 400);
        }

        $remember = $data['remember'] ?? false;

        $check = Auth::guard('stores')->attempt([
            'email' => $data['email'],
            'password' => $data['password'],
        ], $remember);

        if (!$check) {
            return response()->json([
                'message' => 'Invalid credentials.',
            ], 401);
        }

        return response()->json(Auth::guard('stores')->user());
    }

    /** Logout store */
    public function logout(Request $request) {
        Auth::guard('stores')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return response()->json([
            'message' => 'Logged out.',
        ]);
    }

    /** Get store info */
    public function getStore() {
        return response()->json(Auth::guard('stores')->user());
    }

    public function getGuard() {
        if (Auth::guard('stores')->check()) {
            return response()->json("store");
        }
        if (Auth::guard('employees')->check()) {
            return response()->json("employee");
        }
        return response()->json("guest");
    }
}
