<?php

namespace App\Http\Controllers;

use App\Models\ItemProperty;
use App\Models\PurchaseSheet;
use App\Models\PurchaseSheetItem;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PurchaseSheetController extends Controller
{
    private function generateCode(Store $store)
    {
        // calculate purchase sheet code using perchase sheet count
        $count = PurchaseSheet::whereRelation("branch", "store_id", $store->id)->count();

        $code = "PS" . str_pad($count + 1, 6, "0", STR_PAD_LEFT);

        // ensure code is unique
        while (
            PurchaseSheet::where("code", $code)
                ->whereRelation("branch", "store_id", $store->id)
                ->count() > 0
        ) {
            ++$count;
            $code = "PS" . str_pad($count, 6, "0", STR_PAD_LEFT);
        }

        return $code;
    }

    /**
     * @OA\Post(
     *   path="/purchase-sheet",
     *   tags={"PurchaseSheet"},
     *   summary="Create a new purchase sheet",
     *   operationId="createPurchaseSheet",
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(ref="#/components/schemas/CreatePurchaseSheetInput")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Successfull operation",
     *     @OA\JsonContent(ref="#/components/schemas/PurchaseSheet")
     *   )
     * )
     */
    public function create(Request $request)
    {
        $employee = Auth::user();

        $employee_id = $employee->id;

        $store_id = $employee->store_id;

        $branch_id = $employee->employment->branch_id;

        $data = $request->all();

        $rules = [
            "code" => [
                "nullable",
                "string",
                "max:255",
                Rule::unique("purchase_sheets")->where("branch_id", $branch_id),
            ],
            "supplier_id" => ["nullable", "integer", Rule::exists("suppliers", "id")->where("store_id", $store_id)],
            "discount" => ["required_with:discount_type", "integer", "min:0"],
            "discount_type" => ["required_with:discount", "string", Rule::in(["percent", "amount"])],
            "paid_amount" => ["nullable", "integer", "min:0"],
            "note" => ["nullable", "string", "max:255"],
            "items" => ["required", "array", "min:1"],
            "items.*.id" => ["required", "integer", Rule::exists("items", "id")->where("store_id", $store_id)],
            "items.*.quantity" => ["required", "integer", "min:1"],
            "items.*.price" => ["required", "integer", "min:0"],
            "items.*.discount" => ["required_with:items.*.discount_type", "integer"],
            "items.*.discount_type" => ["required_with:items.*.discount", Rule::in(["percent", "amount"])],
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return response()->json(["message" => $this->formatValidationError($validator->errors())], 400);
        }

        // calculate total for each item
        $items = $data["items"];

        foreach ($items as &$item) {
            $discount = $item["discount"] ?? 0;

            $discount_type = $item["discount_type"] ?? "amount";

            $final_price = max(
                $discount_type == "amount" ? $item["price"] - $discount : $item["price"] * (1 - $discount / 100),
                0
            );

            $item["total"] = $item["quantity"] * $final_price;
        }

        // calculate total of purchase sheet
        $original_total = array_reduce($items, fn($carry, $item) => $carry + $item["total"], 0);

        $total_discount = isset($data["discount"])
            ? ($data["discount_type"] == "amount"
                ? $data["discount"]
                : $original_total * ($data["discount"] / 100))
            : 0;

        $discounted_total = $original_total - $total_discount;

        // if perchase sheet code is not set, generate one
        if (!isset($data["code"])) {
            $data["code"] = $this->generateCode($employee->store);
        }

        // create purchase sheet
        $purchase_sheet = PurchaseSheet::create([
            "code" => $data["code"],
            "employee_id" => $employee_id,
            "branch_id" => $branch_id,
            "supplier_id" => $data["supplier_id"] ?? null,
            "discount" => $data["discount"] ?? null,
            "discount_type" => $data["discount_type"] ?? null,
            "total" => round($discounted_total),
            "paid_amount" => $data["paid_amount"] ?? 0,
            "note" => $data["note"] ?? "",
        ]);

        // create purchase sheet items data
        foreach ($items as &$item) {
            PurchaseSheetItem::create([
                "purchase_sheet_id" => $purchase_sheet->id,
                "item_id" => $item["id"],
                "quantity" => $item["quantity"],
                "price" => $item["price"],
                "discount" => $item["discount"] ?? null,
                "discount_type" => $item["discount_type"] ?? null,
                "total" => $item["total"],
            ]);

            // update item base price and quantity
            $item_property = ItemProperty::where(["item_id" => $item["id"], "branch_id" => $branch_id])->first();

            // split discount from purchase sheet to item
            $split_discount = ($total_discount / $original_total) * $item["total"];

            $base_price = round(($item["total"] - $split_discount) / $item["quantity"]);

            if (!$item_property) {
                $item_property = ItemProperty::create([
                    "item_id" => $item["id"],
                    "branch_id" => $branch_id,
                    "base_price" => $base_price,
                    "last_purchase_price" => $item["price"],
                    "quantity" => $item["quantity"],
                ]);
            } else {
                $new_base_price = round(
                    ($item_property->base_price * $item_property->quantity + $base_price * $item["quantity"]) /
                        ($item_property->quantity + $item["quantity"])
                );

                $item_property->update([
                    "base_price" => $new_base_price,
                    "quantity" => $item_property->quantity + $item["quantity"],
                    "last_purchase_price" => $item["price"],
                ]);
            }
        }

        return response()->json($purchase_sheet);
    }

    /**
     * @OA\Get(
     *   path="/purchase-sheet",
     *   tags={"PurchaseSheet"},
     *   summary="Get all purchase sheets",
     *   operationId="getPurchaseSheets",
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
     *       @OA\Items(ref="#/components/schemas/PurchaseSheetWithSupplierAndEmployee")
     *     )
     *   )
     * )
     */
    public function getMany(Request $request)
    {
        $branch_id = Auth::user()->employment->branch_id;

        [$search, $from, $to, $order_by, $order_type] = $this->getQuery($request);

        $purchase_sheets = PurchaseSheet::with(["supplier", "employee"])
            ->where("branch_id", $branch_id)
            ->where(
                fn($query) => $query
                    ->where("code", "iLike", "%" . $search . "%")
                    ->orWhere("note", "iLike", "%" . $search . "%")
                    ->orWhereRelation("supplier", "name", "iLike", "%" . $search . "%")
                    ->orWhereRelation("employee", "name", "iLike", "%" . $search . "%")
                    ->orWhereRelation("employee", "phone", "iLike", "%" . $search . "%")
                    ->orWhereRelation("employee", "email", "iLike", "%" . $search . "%")
            )
            ->orderBy($order_by, $order_type)
            ->offset($from)
            ->limit($to - $from)
            ->get();

        return response()->json($purchase_sheets);
    }

    /**
     * @OA\Get(
     *   path="/purchase-sheet/{id}",
     *   tags={"PurchaseSheet"},
     *   summary="Get purchase sheet by id",
     *   operationId="getPurchaseSheet",
     *   @OA\Parameter(name="id", in="path", @OA\Schema(type="integer"), required=true),
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(ref="#/components/schemas/PurchaseSheetDetail")
     *   )
     * )
     */
    public function getOne($id)
    {
        $branch_id = Auth::user()->employment->branch_id;

        $purchase_sheet = PurchaseSheet::with(["items.item", "employee", "branch", "supplier"])
            ->where("branch_id", $branch_id)
            ->find($id);

        if (!$purchase_sheet) {
            return response()->json(["message" => "Purchase sheet not found"], 404);
        }

        return response()->json($purchase_sheet);
    }

    /**
     * @OA\Put(
     *   path="/purchase-sheet/{id}/note",
     *   tags={"PurchaseSheet"},
     *   summary="Update purchase sheet note",
     *   operationId="updatePurchaseSheetNote",
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
    public function updateNote(Request $request, $id)
    {
        $branch_id = Auth::user()->employment->branch_id;

        $data = $request->all();

        $purchase_sheet = PurchaseSheet::where("branch_id", $branch_id)->find($id);

        if (!$purchase_sheet) {
            return response()->json(["message" => "Purchase sheet not found"], 404);
        }

        $rules = [
            "note" => ["required", "string", "max:255"],
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return response()->json(["message" => $this->formatValidationError($validator->errors())], 400);
        }

        $purchase_sheet->update([
            "note" => $data["note"],
        ]);

        return response()->json(["message" => "Purchase sheet note updated"]);
    }

    /**
     * @OA\Delete(
     *   path="/purchase-sheet/{id}",
     *   tags={"PurchaseSheet"},
     *   summary="Delete purchase sheet",
     *   operationId="deletePurchaseSheet",
     *   @OA\Parameter(name="id", in="path", @OA\Schema(type="integer"), required=true),
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(ref="#/components/schemas/Message")
     *   )
     * )
     */
    public function delete($id)
    {
        $branch_id = Auth::user()->employment->branch_id;

        $purchase_sheet = PurchaseSheet::where("branch_id", $branch_id)->find($id);

        if (!$purchase_sheet) {
            return response()->json(["message" => "Purchase sheet not found"], 404);
        }

        $purchase_sheet_discount =
            $purchase_sheet->discount_type == "amount"
                ? $purchase_sheet->discount
                : ($purchase_sheet->discount_type == "percent"
                    ? $purchase_sheet->total * (100 / $purchase_sheet->discount - 1)
                    : 0);

        $original_total = $purchase_sheet->total + $purchase_sheet_discount;

        // recalculate base price and quantity for each purchase sheet item
        foreach ($purchase_sheet->items as $purchase_sheet_item) {
            $item_property = ItemProperty::where([
                "branch_id" => $branch_id,
                "item_id" => $purchase_sheet_item->item_id,
            ])->first();

            $previous_quantity = max($item_property->quantity - $purchase_sheet_item->quantity, 0);

            $split_discount = ($purchase_sheet_discount * $purchase_sheet_item->total) / $original_total;

            $discounted_total = $purchase_sheet_item->total - $split_discount;

            $previous_base_price =
                ($item_property->base_price * $item_property->quantity - $discounted_total) / $previous_quantity;

            $item_property->update([
                "base_price" => round($previous_base_price),
                "quantity" => $previous_quantity,
            ]);
        }

        // delete purchase sheet
        $purchase_sheet->delete();

        return response()->json(["message" => "Purchase sheet deleted"]);
    }
}
