<?php

namespace App\Http\Controllers;

use App\Models\ItemProperty;
use App\Models\QuantityCheckingItem;
use App\Models\QuantityCheckingSheet;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class QuantityCheckingSheetController extends Controller
{
    private function generateCode(Store $store)
    {
        // calculate purchase sheet code using perchase sheet count
        $count = QuantityCheckingSheet::whereRelation("branch", "store_id", $store->id)->count();

        $code = "QS" . str_pad($count + 1, 6, "0", STR_PAD_LEFT);

        // ensure code is unique
        while (
            QuantityCheckingSheet::where("code", $code)
                ->whereRelation("branch", "store_id", $store->id)
                ->count() > 0
        ) {
            ++$count;
            $code = "QS" . str_pad($count, 6, "0", STR_PAD_LEFT);
        }

        return $code;
    }

    /**
     * @OA\Post(
     *   path="/quantity-checking-sheet",
     *   tags={"QuantityCheckingSheet"},
     *   summary="Create a new quantity checking sheet",
     *   operationId="createQuantityCheckingSheet",
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(ref="#/components/schemas/CreateQuantityCheckingSheetInput")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Created quantity checking sheet",
     *     @OA\JsonContent(ref="#/components/schemas/QuantityCheckingSheet")
     *   )
     * )
     */
    public function create(Request $request)
    {
        $employee = Auth::user();

        $branch_id = Auth::user()->employment->branch_id;

        $data = $request->all();

        $rules = [
            "code" => [
                "nullable",
                "string",
                "max:255",
                Rule::unique("quantity_checking_sheets")->where("branch_id", $branch_id),
            ],
            "note" => ["nullable", "string", "max:255"],
            "items" => ["required", "array", "min:1"],
            "items.*.id" => [
                "required",
                "integer",
                Rule::exists("item_properties", "item_id")->where("branch_id", $branch_id),
            ],
            "items.*.actual_quantity" => ["required", "integer", "min:0"],
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return response()->json(["message" => $this->formatValidationError($validator->errors())], 400);
        }

        // if code is not set, generate one
        if (!isset($data["code"])) {
            $data["code"] = $this->generateCode($employee->store);
        }

        $quantity_checking_sheet = QuantityCheckingSheet::create([
            "code" => $data["code"],
            "employee_id" => $employee->id,
            "branch_id" => $branch_id,
            "note" => $data["note"] ?? null,
        ]);

        // calculate total of each item
        $items_data = [];
        $items = $data["items"];
        foreach ($items as &$item) {
            $item_property = ItemProperty::where(["branch_id" => $branch_id, "item_id" => $item["id"]])->first();

            $item["total"] = ($item["actual_quantity"] - $item_property->quantity) * $item_property->base_price;

            $items_data[] = [
                "quantity_checking_sheet_id" => $quantity_checking_sheet->id,
                "item_id" => $item["id"],
                "actual_quantity" => $item["actual_quantity"],
                "expected_quantity" => $item_property->quantity,
                "total" => $item["total"],
                "created_at" => now(),
                "updated_at" => now(),
            ];

            if ($item_property) {
                $item_property->update([
                    "quantity" => $item["actual_quantity"],
                ]);
            }
        }

        QuantityCheckingItem::insert($items_data);

        return response()->json($quantity_checking_sheet);
    }

    /**
     * @OA\Get(
     *   path="/quantity-checking-sheet",
     *   tags={"QuantityCheckingSheet"},
     *   summary="Get quantity checking sheets",
     *   description="Get quantity checking sheets",
     *   operationId="getQuantityCheckingSheets",
     *   @OA\Parameter(name="search", in="query", @OA\Schema(type="string")),
     *   @OA\Parameter(name="order_by", in="query", @OA\Schema(type="string")),
     *   @OA\Parameter(name="order_type", in="query", @OA\Schema(type="string", enum={"asc", "desc"})),
     *   @OA\Parameter(name="from", in="query", @OA\Schema(type="integer")),
     *   @OA\Parameter(name="to", in="query", @OA\Schema(type="integer")),
     *   @OA\Response(
     *     response=200,
     *     description="Quantity checking sheets",
     *     @OA\JsonContent(
     *       type="array",
     *       @OA\Items(ref="#/components/schemas/QuantityCheckingSheetWithEmployee")
     *     )
     *   )
     * )
     */
    public function getMany(Request $request)
    {
        $branch_id = Auth::user()->employment->branch_id;

        [$search, $from, $to, $order_by, $order_type] = $this->getQuery($request);

        $quantity_checking_sheets = QuantityCheckingSheet::with("employee")
            ->where("branch_id", $branch_id)
            ->where(
                fn($query) => $query
                    ->where("code", "iLike", "%" . $search . "%")
                    ->orWhere("note", "iLike", "%" . $search . "%")
                    ->orWhereRelation("employee", "name", "iLike", "%" . $search . "%")
                    ->orWhereRelation("employee", "email", "iLike", "%" . $search . "%")
                    ->orWhereRelation("employee", "phone", "iLike", "%" . $search . "%")
            )
            ->orderBy($order_by, $order_type)
            ->offset($from)
            ->limit($to)
            ->get();

        return response()->json($quantity_checking_sheets);
    }

    /**
     * @OA\Get(
     *   path="/quantity-checking-sheet/{id}",
     *   tags={"QuantityCheckingSheet"},
     *   summary="Get quantity checking sheet",
     *   description="Get quantity checking sheet",
     *   operationId="getQuantityCheckingSheet",
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\Response(
     *     response=200,
     *     description="Quantity checking sheet",
     *     @OA\JsonContent(ref="#/components/schemas/QuantityCheckingSheetDetail")
     *   )
     * )
     */
    public function getOne(int $id)
    {
        $branch_id = Auth::user()->employment->branch_id;

        $quantity_checking_sheet = QuantityCheckingSheet::with(["employee", "items.item"])
            ->where(["branch_id" => $branch_id, "id" => $id])
            ->first();

        if (!$quantity_checking_sheet) {
            return response()->json(["message" => "Quantity checking sheet not found"], 404);
        }

        return response()->json($quantity_checking_sheet);
    }

    /**
     * @OA\Put(
     *   path="/quantity-checking-sheet/{id}/note",
     *   tags={"QuantityCheckingSheet"},
     *   summary="Update quantity checking sheet note",
     *   operationId="updateQuantityCheckingSheetNote",
     *   @OA\Parameter(name="id", in="path", @OA\Schema(type="integer"), required=true),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(required={"note"}, @OA\Property(property="note", type="string"))
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(ref="#/components/schemas/Message")
     *   )
     * )
     */
    public function updateNote(Request $request, int $id)
    {
        $branch_id = Auth::user()->employment->branch_id;

        $data = $request->all();

        $sheet = QuantityCheckingSheet::where(["branch_id" => $branch_id, "id" => $id])->first();

        if (!$sheet) {
            return response()->json(["message" => "Quantity checking sheet not found"], 404);
        }

        $rules = [
            "note" => ["required", "string", "max:255"],
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return response()->json(["message" => $this->formatValidationError($validator->errors())], 400);
        }

        $sheet->update([
            "note" => $data["note"],
        ]);

        return response()->json(["message" => "Quantity checking sheet updated"]);
    }

    /**
     * @OA\Delete(
     *   path="/quantity-checking-sheet/{id}",
     *   tags={"QuantityCheckingSheet"},
     *   summary="Delete quantity checking sheet",
     *   description="Delete quantity checking sheet",
     *   operationId="deleteQuantityCheckingSheet",
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\Response(
     *     response=200,
     *     description="Quantity checking sheet deleted",
     *     @OA\JsonContent(ref="#/components/schemas/Message")
     *   )
     * )
     */
    public function delete(int $id)
    {
        $branch_id = Auth::user()->employment->branch_id;

        $quantity_checking_sheet = QuantityCheckingSheet::with("items")
            ->where(["branch_id" => $branch_id, "id" => $id])
            ->first();

        if (!$quantity_checking_sheet) {
            return response()->json(["message" => "Quantity checking sheet not found"], 404);
        }

        foreach ($quantity_checking_sheet->items as $item) {
            $quantity_difference = $item->actual_quantity - $item->expected_quantity;

            $item_property = ItemProperty::where(["branch_id" => $branch_id, "item_id" => $item->item_id])->first();

            $item_property->update(["quantity" => max($item_property->quantity - $quantity_difference, 0)]);
        }

        $quantity_checking_sheet->delete();

        return response()->json(["message" => "Quantity checking sheet deleted successfully"]);
    }
}
