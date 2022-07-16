<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class SupplierController extends Controller
{
    /**
     * @OA\Post(
     *   path="/supplier",
     *   summary="Create a new supplier",
     *   tags={"Supplier"},
     *   operationId="createSupplier",
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(ref="#/components/schemas/CreateSupplierInput")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(ref="#/components/schemas/Supplier")
     *   ),
     * )
     */
    public function create(Request $request)
    {
        $store_id = $request->get("store_id");

        $as = $request->get("as");

        $data = $request->all();

        $rules = [
            "name" => ["required", "string", "max:255"],
            "tax_number" => ["nullable", "string", "max:255"],
            "note" => ["nullable", "string", "max:255"],
            "code" => ["nullable", "string", "max:255", Rule::unique("suppliers")->where("store_id", $store_id)],
            "address" => ["nullable", "string", "max:255"],
            "phone" => [
                "required_without:email",
                "string",
                "max:255",
                Rule::unique("suppliers")->where("store_id", $store_id),
            ],
            "email" => [
                "required_without:phone",
                "string",
                "email",
                "max:255",
                Rule::unique("suppliers")->where("store_id", $store_id),
            ],
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return response()->json(["message" => $this->formatValidationError($validator->errors())], 400);
        }

        // create code if not provided
        if (!isset($data["code"])) {
            $supplier_count = Supplier::where("store_id", $store_id)->count();
            $data["code"] = "SUP" . str_pad($supplier_count + 1, 6, "0", STR_PAD_LEFT);
        }

        $supplier = Supplier::create([
            "store_id" => $store_id,
            "name" => $data["name"],
            "code" => $data["code"],
            "address" => $data["address"] ?? null,
            "phone" => $data["phone"] ?? null,
            "email" => $data["email"] ?? null,
            "tax_number" => $data["tax_number"] ?? null,
            "note" => $data["note"] ?? null,
        ]);

        return response()->json($supplier);
    }

    /**
     * @OA\Get(
     *   path="/supplier",
     *   summary="Get all suppliers",
     *   tags={"Supplier"},
     *   operationId="getSuppliers",
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
     *       @OA\Items(ref="#/components/schemas/Supplier")
     *     )
     *   ),
     * )
     */
    public function getMany(Request $request)
    {
        $store_id = $request->get("store_id");

        [$search, $from, $to, $order_by, $order_type] = $this->getQuery($request);
        // search for suppliers by name, phone, email, code
        $suppliers = Supplier::where("store_id", $store_id)
            ->where(
                fn($query) => $query
                    ->where("name", "iLike", "%" . $search . "%")
                    ->orWhere("phone", "iLike", "%" . $search . "%")
                    ->orWhere("email", "iLike", "%" . $search . "%")
                    ->orWhere("code", "iLike", "%" . $search . "%")
            )
            ->orderBy($order_by, $order_type)
            ->offset($from)
            ->limit($to - $from)
            ->get();

        return response()->json($suppliers);
    }

    /**
     * @OA\Get(
     *   path="/supplier/{id}",
     *   summary="Get a supplier",
     *   tags={"Supplier"},
     *   operationId="getSupplier",
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     description="Supplier ID",
     *     required=true,
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(ref="#/components/schemas/Supplier")
     *   ),
     * )
     */
    public function getOne(Request $request, $id)
    {
        $store_id = $request->get("store_id");
        $supplier = Supplier::where(["store_id" => $store_id, "id" => $id])->first();

        if (!$supplier) {
            return response()->json(["message" => "Supplier not found."], 404);
        }

        return response()->json($supplier);
    }

    /**
     * @OA\Put(
     *   path="/supplier/{id}",
     *   summary="Update a supplier",
     *   tags={"Supplier"},
     *   operationId="updateSupplier",
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     description="Supplier ID",
     *     required=true,
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(ref="#/components/schemas/UpdateSupplierInput")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(ref="#/components/schemas/Supplier")
     *   ),
     * )
     */
    public function update(Request $request, $id)
    {
        $store_id = $request->get("store_id");

        $data = $request->all();

        $supplier = Supplier::where(["store_id" => $store_id, "id" => $id])->first();

        if (!$supplier) {
            return response()->json(["message" => "Supplier not found."], 404);
        }

        $rules = [
            "code" => [
                "nullable",
                "string",
                "max:255",
                Rule::unique("suppliers")
                    ->where("store_id", $store_id)
                    ->ignore($id),
            ],
            "name" => ["nullable", "string", "max:255"],
            "address" => ["nullable", "string", "max:255"],
            "phone" => [
                "nullable",
                "string",
                "max:255",
                Rule::unique("suppliers")
                    ->where("store_id", $store_id)
                    ->ignore($id),
            ],
            "email" => [
                "nullable",
                "string",
                "email",
                "max:255",
                Rule::unique("suppliers")
                    ->where("store_id", $store_id)
                    ->ignore($id),
            ],
            "tax_number" => ["nullable", "string", "max:255"],
            "note" => ["nullable", "string", "max:255"],
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return response()->json(["message" => $this->formatValidationError($validator->errors())], 400);
        }

        $supplier->name = $data["name"] ?? $supplier->name;
        $supplier->address = $data["address"] ?? $supplier->address;
        $supplier->phone = $data["phone"] ?? $supplier->phone;
        $supplier->email = $data["email"] ?? $supplier->email;
        $supplier->tax_number = $data["tax_number"] ?? $supplier->tax_number;
        $supplier->note = $data["note"] ?? $supplier->note;

        $supplier->save();

        return response()->json($supplier);
    }

    /**
     * @OA\Delete(
     *   path="/supplier/{id}",
     *   summary="Delete a supplier",
     *   tags={"Supplier"},
     *   operationId="deleteSupplier",
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     description="Supplier ID",
     *     required=true,
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\Parameter(
     *     name="force",
     *     in="query",
     *     description="Force delete",
     *     @OA\Schema(type="boolean")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(ref="#/components/schemas/Message")
     *   ),
     * )
     */
    public function delete(Request $request, $id)
    {
        $store_id = $request->get("store_id");

        $as = $request->get("as");

        $isForce = $request->query("force") ?? false;

        $supplier = Supplier::where(["store_id" => $store_id, "id" => $id])->first();

        if (!$supplier) {
            return response()->json(["message" => "Supplier not found."], 404);
        }

        if ("employee" == $as && $isForce) {
            return response()->json(["message" => "Unauthorized to force delete."], 403);
        }

        $supplier->when($isForce, fn($query) => $query->forceDelete(), fn($query) => $query->delete());

        return response()->json(["message" => "Supplier deleted."]);
    }

    /**
     * @OA\Get(
     *   path="/supplier/deleted",
     *   summary="Get all soft deleted suppliers",
     *   tags={"Supplier"},
     *   operationId="getDeletedSuppliers",
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
     *       @OA\Items(ref="#/components/schemas/Supplier")
     *     )
     *   ),
     * )
     */
    public function getDeleted(Request $request)
    {
        $store_id = $request->get("store_id");

        [$search, $from, $to, $order_by, $order_type] = $this->getQuery($request);

        $suppliers = Supplier::onlyTrashed()
            ->where("store_id", $store_id)
            ->where(
                fn($query) => $query
                    ->where("name", "iLike", "%" . $search . "%")
                    ->orWhere("phone", "iLike", "%" . $search . "%")
                    ->orWhere("email", "iLike", "%" . $search . "%")
                    ->orWhere("code", "iLike", "%" . $search . "%")
            )
            ->orderBy($order_by, $order_type)
            ->skip($from)
            ->take($to - $from)
            ->get();

        return response()->json($suppliers);
    }

    /**
     * @OA\Post(
     *   path="/supplier/{id}/restore",
     *   summary="Restore a supplier",
     *   tags={"Supplier"},
     *   operationId="restoreSupplier",
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     description="Supplier ID",
     *     required=true,
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(ref="#/components/schemas/Message")
     *   ),
     * )
     */
    public function restore(Request $request, $id)
    {
        $store_id = $request->get("store_id");

        $supplier = Supplier::onlyTrashed()
            ->where("store_id", $store_id)
            ->where("id", $id)
            ->first();

        if (!$supplier) {
            return response()->json(["message" => "Supplier not found."], 404);
        }

        $supplier->restore();

        return response()->json(["message" => "Supplier restored."]);
    }

    /**
     * @OA\Delete(
     *   path="/supplier/{id}/force",
     *   summary="Force delete a supplier",
     *   tags={"Supplier"},
     *   operationId="forceDeleteSupplier",
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     description="Supplier ID",
     *     required=true,
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(ref="#/components/schemas/Message")
     *   ),
     * )
     */
    public function forceDelete(Request $request, $id)
    {
        $store_id = $request->get("store_id");

        $supplier = Supplier::withTrashed()
            ->where(["store_id" => $store_id, "id" => $id])
            ->first();

        if (!$supplier) {
            return response()->json(["message" => "Supplier not found."], 404);
        }

        $supplier->forceDelete();

        return response()->json(["message" => "Supplier deleted."]);
    }
}
