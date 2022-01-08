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
            'remember' => ['boolean', 'nullable'],
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
