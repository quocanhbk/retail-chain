<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\Customer;
use Illuminate\Support\Str;

class CustomerController extends Controller
{
    /**
     * @OA\Post(
     *   path="/customer",
     *   summary="Create a new customer",
     *   tags={"Customer"},
     *   operationId="createCustomer",
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(ref="#/components/schemas/CreateCustomerInput")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(ref="#/components/schemas/Customer")
     *   ),
     * )
     */
    public function create(Request $request)
    {
        $store_id = $request->get("store_id");

        $data = $request->all();

        $rules = [
            "name" => ["required", "string", "max:255"],
            "phone" => [
                "required_without:email",
                "string",
                "max:255",
                Rule::unique("customers")->where("store_id", $store_id),
            ],
            "email" => [
                "required_without:phone",
                "string",
                "email",
                "max:255",
                Rule::unique("customers")->where("store_id", $store_id),
            ],
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return response()->json(["message" => $this->formatValidationError($validator->errors())], 400);
        }

        $customer = Customer::create([
            "store_id" => $store_id,
            "code" => Str::uuid(),
            "name" => $data["name"],
            "phone" => $data["phone"] ?? null,
            "email" => $data["email"] ?? null,
        ]);

        return response()->json($customer);
    }

    /**
     * @OA\Get(
     *   path="/customer",
     *   summary="Get all customers",
     *   tags={"Customer"},
     *   operationId="getCustomers",
     *   @OA\Parameter(name="search", in="query", @OA\Schema(type="string")),
     *   @OA\Parameter(name="order_by", in="query", @OA\Schema(type="string")),
     *   @OA\Parameter(name="order_type", in="query", @OA\Schema(type="string", enum={"asc", "desc"})),
     *   @OA\Parameter(name="from", in="query", @OA\Schema(type="integer")),
     *   @OA\Parameter(name="to", in="query", @OA\Schema(type="integer")),
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(
     *       type="array",
     *       @OA\Items(ref="#/components/schemas/Customer")
     *     ),
     *   ),
     * )
     */
    public function getCustomers(Request $request)
    {
        $store_id = $request->get("store_id");

        [$search, $from, $to, $order_by, $order_type] = $this->getQuery($request);

        $customers = Customer::where("store_id", $store_id)
            ->where(function ($query) use ($search) {
                $query
                    ->where("name", "iLike", "%" . $search . "%")
                    ->orWhere("phone", "iLike", "%" . $search . "%")
                    ->orWhere("email", "iLike", "%" . $search . "%")
                    ->orWhere("code", "iLike", "%" . $search . "%");
            })
            ->orderBy($order_by, $order_type)
            ->offset($from)
            ->limit($to - $from)
            ->get();

        return response()->json($customers);
    }

    /**
     * @OA\Get(
     *   path="/customer/one",
     *   summary="Get a customer",
     *   tags={"Customer"},
     *   operationId="getCustomer",
     *   @OA\Parameter(name="id", in="query", @OA\Schema(type="integer")),
     *   @OA\Parameter(name="code", in="query", @OA\Schema(type="string")),
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(ref="#/components/schemas/Customer")
     *   ),
     * )
     */
    public function getCustomer(Request $request)
    {
        $id = $request->get("id");
        $code = $request->get("code");
        if (!isset($id) && !isset($code)) {
            return response()->json(["message" => "Missing id or code"], 400);
        }

        $store_id = $request->get("store_id");

        $customer = Customer::where("store_id", $store_id)
            ->when(isset($id), function ($query) use ($id) {
                $query->where("id", $id);
            })
            ->when(isset($code) && !isset($id), function ($query) use ($code) {
                $query->where("code", $code);
            })
            ->first();

        if (!$customer) {
            return response()->json(["message" => "Customer not found."], 404);
        }

        return response()->json($customer);
    }

    /**
     * @OA\Put(
     *   path="/customer/{customer_id}",
     *   summary="Update a customer",
     *   tags={"Customer"},
     *   operationId="updateCustomer",
     *   @OA\Parameter(name="customer_id", in="path", @OA\Schema(type="integer"), required=true),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(ref="#/components/schemas/UpsertCustomerInput")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(
     *       required={"message"},
     *       @OA\Property(property="message", type="string", description="Success message"),
     *     )
     *   ),
     * )
     */
    public function update(Request $request, $customer_id)
    {
        $store_id = $request->get("store_id");

        $data = $request->all();

        $customer = Customer::where("store_id", $store_id)
            ->where("id", $customer_id)
            ->first();

        if (!$customer) {
            return response()->json(["message" => "Customer not found."], 404);
        }

        $rules = [
            "name" => ["nullable", "string", "max:255"],
            "phone" => [
                "nullable",
                "string",
                "max:255",
                Rule::unique("customers")
                    ->where("store_id", $store_id)
                    ->ignore($customer_id),
            ],
            "email" => [
                "nullable",
                "string",
                "email",
                "max:255",
                Rule::unique("customers")
                    ->where("store_id", $store_id)
                    ->ignore($customer_id),
            ],
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return response()->json(["message" => $this->formatValidationError($validator->errors())], 400);
        }

        $customer->name = $data["name"] ?? $customer->name;
        $customer->phone = $data["phone"] ?? $customer->phone;
        $customer->email = $data["email"] ?? $customer->email;

        $customer->save();

        return response()->json([
            "message" => "Customer updated.",
        ]);
    }

    /**
     * @OA\Post(
     *   path="/customer/add-point/{customer_id}",
     *   summary="Create a customer",
     *   tags={"Customer"},
     *   operationId="addCustomerPoint",
     *   @OA\Parameter(name="customer_id", in="path", @OA\Schema(type="integer"), required=true),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       required={"point"},
     *       @OA\Property(property="point", type="integer", description="Point"),
     *     )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(
     *       required={"message"},
     *       @OA\Property(property="message", type="string", description="Success message"),
     *     )
     *   )
     * )
     */
    public function addPoint(Request $request, $customer_id)
    {
        $store_id = $request->get("store_id");

        $data = $request->all();

        $customer = Customer::where("store_id", $store_id)
            ->where("id", $customer_id)
            ->first();

        if (!$customer) {
            return response()->json(
                [
                    "message" => "Customer not found.",
                ],
                404
            );
        }

        $rules = [
            "point" => ["required", "integer", "min:1"],
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return response()->json(["message" => $this->formatValidationError($validator->errors())], 400);
        }

        $customer->point += $data["point"];

        $customer->save();

        return response()->json([
            "message" => "Added point successfully.",
        ]);
    }

    /**
     * @OA\Post(
     *   path="/customer/use-point/{customer_id}",
     *   summary="Use point",
     *   tags={"Customer"},
     *   operationId="useCustomerPoint",
     *   @OA\Parameter(name="customer_id", in="path", @OA\Schema(type="integer"), required=true),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       required={"point"},
     *       @OA\Property(property="point", type="integer", description="Point to use"),
     *     )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(
     *       required={"message"},
     *       @OA\Property(property="message", type="string", description="Success message"),
     *     )
     *   )
     * )
     */
    public function usePoint(Request $request, $customer_id)
    {
        $store_id = $request->get("store_id");

        $data = $request->all();

        $customer = Customer::where("store_id", $store_id)
            ->where("id", $customer_id)
            ->first();

        if (!$customer) {
            return response()->json(["message" => "Customer not found."], 404);
        }

        $rules = [
            "point" => ["required", "integer", "min:1"],
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return response()->json(["message" => $this->formatValidationError($validator->errors())], 400);
        }

        if ($customer->point < $data["point"]) {
            return response()->json(["message" => "Not enough point."], 400);
        }

        $customer->point -= $data["point"];

        $customer->save();

        return response()->json(["message" => "Used point successfully."]);
    }
}
