<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Store;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class StoreController extends Controller
{
    /**
     * @OA\Post(
     *   path="/store/register",
     *   operationId="registerStore",
     *   tags={"Store"},
     *   summary="Register a new store",
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(ref="#/components/schemas/RegisterStoreInput")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(
     *       required={"message"},
     *       @OA\Property(property="message", type="string"),
     *     )
     *   ),
     * )
     */
    public function register(Request $request)
    {
        // request validation
        $data = $request->all();
        $rules = [
            "name" => ["required", "string", "max:255"],
            "email" => ["required", "string", "email", "max:255", "unique:stores"],
            "password" => ["required", "string", "min:6", "confirmed"],
            "remember" => ["nullable", "boolean"],
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return response()->json(
                [
                    "message" => $this->formatValidationError($validator->errors()),
                ],
                400
            );
        }

        // create store and login
        $store = Store::create([
            "name" => $data["name"],
            "email" => $data["email"],
            "password" => Hash::make($data["password"]),
        ]);

        $remember = $data["remember"] ?? false;
        Auth::guard("stores")->login($store, $remember);

        event(new Registered($store));

        // create default item categories
        $store
            ->categories()
            ->createMany([
                ["name" => "????? U???NG C??C LO???I"],
                ["name" => "S???A U???NG C??C LO???I"],
                ["name" => "B??NH K???O C??C LO???I"],
                ["name" => "M??, CH??O, PH???, B??N"],
                ["name" => "D???U ??N, GIA V???"],
                ["name" => "G???O, B???T, ????? KH??"],
                ["name" => "????? M??T, ????NG L???NH"],
                ["name" => "T??, ????? CHO B??"],
                ["name" => "CH??M S??C C?? NH??N"],
                ["name" => "V??? SINH NH?? C???A"],
                ["name" => "????? D??NG GIA ????NH"],
                ["name" => "V??N PH??NG PH???M"],
                ["name" => "THU???C V?? TH???C PH???M CH???C N??NG"],
            ]);

        return response()->json([
            "message" => "Please check email to verify your account.",
        ]);
    }

    /**
     * @OA\Post(
     *   path="/store/login",
     *   operationId="loginStore",
     *   tags={"Store"},
     *   summary="Login as store owner",
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(ref="#/components/schemas/LoginStoreInput")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(ref="#/components/schemas/Store")
     *   ),
     * )
     */
    public function login(Request $request)
    {
        $data = $request->all();
        $rules = [
            "email" => ["required", "string", "email", "max:255"],
            "password" => ["required", "string", "min:6"],
            "remember" => ["boolean", "nullable"],
        ];
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return response()->json(
                [
                    "message" => $this->formatValidationError($validator->errors()),
                ],
                400
            );
        }

        $remember = $data["remember"] ?? false;

        $check = Auth::guard("stores")->attempt(
            [
                "email" => $data["email"],
                "password" => $data["password"],
            ],
            $remember
        );

        if (!$check) {
            return response()->json(
                [
                    "message" => "T??i kho???n ho???c m???t kh???u kh??ng h???p l???",
                ],
                401
            );
        }

        return response()->json(Auth::guard("stores")->user());
    }

    /**
     * @OA\Post(
     *   path="/store/logout",
     *   operationId="logoutStore",
     *   tags={"Store"},
     *   summary="Logout as store owner",
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(
     *       required={"message"},
     *       @OA\Property(property="message", type="string")
     *     )
     *   ),
     * )
     */
    public function logout(Request $request)
    {
        Auth::guard("stores")->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return response()->json([
            "message" => "Logged out.",
        ]);
    }

    /**
     * @OA\Get(
     *   path="/store/me",
     *   operationId="getStore",
     *   tags={"Store"},
     *   summary="Get store information",
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(ref="#/components/schemas/Store")
     *   ),
     * )
     */
    public function getStore(Request $request)
    {
        error_log(json_encode($request->user("stores")));
        return response()->json(Auth::guard("stores")->user());
    }

    /**
     * @OA\Get(
     *   path="/guard",
     *   operationId="getGuard",
     *   tags={"Guard"},
     *   summary="Get guard",
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(
     *       required={"message"},
     *       @OA\Property(property="guard", type="string")
     *     )
     *   )
     * )
     */
    public function getGuard()
    {
        if (Auth::guard("stores")->check()) {
            return response()->json(["guard" => "store"]);
        }
        if (Auth::guard("employees")->check()) {
            return response()->json(["guard" => "employee"]);
        }
        return response()->json(["guard" => "guest"]);
    }
}
