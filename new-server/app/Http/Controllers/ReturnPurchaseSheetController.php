<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\ItemProperty;
use App\Models\PurchaseSheet;
use App\Models\PurchaseSheetItem;
use App\Models\ReturnPurchaseSheet;
use App\Models\ReturnPurchaseSheetItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ReturnPurchaseSheetController extends Controller
{
    // a return purchase sheet could be belong to a purchase sheet, or not belong to any purchase sheet
    public function create(Request $request)
    {
        $store_id = Auth::user()->store_id;
        $employee_id = Auth::user()->id;
        $branch_id = Auth::user()->employment->branch_id;
        $data = $request->all();
        $rules = [
            "code" => [
                "nullable",
                "string",
                "max:255",
                Rule::unique("purchase_sheets")->where("branch_id", $branch_id),
            ],
            "purchase_sheet_id" => [
                "required",
                "integer",
                Rule::exists("purchase_sheets", "id")->where("branch_id", $branch_id),
            ],
            "supplier_id" => ["required", "integer", Rule::exists("suppliers", "id")->where("store_id", $store_id)],
            "discount" => ["nullable", "numeric", "min:0"],
            "discount_type" => ["nullable", "string", "max:255"],
            "paid_amount" => ["required", "numeric", "min:0"],
            "note" => ["nullable", "string", "max:255"],

            "items" => ["required", "array", "min:1"],
            "items.*.item_id" => [
                "required",
                "integer",
                Rule::exists("purchase_sheet_items", "item_id")->where(
                    "purchase_sheet_id",
                    $data["purchase_sheet_id"] ?? 0
                ),
            ],
            "items.*.quantity" => ["required", "numeric", "min:0"],
            "items.*.return_price" => ["nullable", "numeric", "min:0"],
            "items.*.return_price_type" => ["nullable", "string", "max:255"],
        ];

        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return response()->json(["errors" => $validator->errors()], 400);
        }

        $items = $data["items"];
        // check each item have enough quantity
        foreach ($items as $item) {
            $item_property = ItemProperty::where("item_id", $item["item_id"])
                ->where("branch_id", $branch_id)
                ->first();
            if ($item_property->quantity < $item["quantity"]) {
                return response()->json(
                    [
                        "errors" => [
                            "items" => ["Item " . $item["item_id"] . " does not have enough quantity"],
                        ],
                    ],
                    400
                );
            }

            // get all return purchase sheet items of that item
            $return_purchase_sheet_items = ReturnPurchaseSheetItem::with("returnPurchaseSheet")
                ->where("item_id", $item["item_id"])
                ->whereHas(
                    "returnPurchaseSheet",
                    fn($query) => $query->where("purchase_sheet_id", $data["purchase_sheet_id"])
                )
                ->get();

            // sum all quantity of return purchase sheet items of that item
            $sum_quantity = $item["quantity"];
            foreach ($return_purchase_sheet_items as $return_purchase_sheet_item) {
                $sum_quantity += $return_purchase_sheet_item->quantity;
            }
            // throw invalid quantity if sum of return purchase sheet items of that item is greater than the quantity of purchase sheet item
            $purchase_sheet_item = PurchaseSheetItem::where("item_id", $item["item_id"])
                ->where("purchase_sheet_id", $data["purchase_sheet_id"])
                ->first();
            if ($purchase_sheet_item->quantity < $sum_quantity) {
                return response()->json(
                    [
                        "errors" => [
                            "items" => ["Item " . $item["item_id"] . " does not have enough quantity"],
                        ],
                    ],
                    400
                );
            }
        }

        // calculate total for each item
        foreach ($items as &$item) {
            $purchase_sheet_item = PurchaseSheetItem::where("item_id", $item["item_id"])
                ->where("purchase_sheet_id", $data["purchase_sheet_id"])
                ->first();
            $item["price"] = $purchase_sheet_item["price"];
            // if return price type is not set, set to `percent`
            $item["return_price_type"] = $item["return_price_type"] ?? "percent";
            // calculate return price, if return price is not set, it will be 100 percent, or purchase price
            $item["return_price"] =
                $item["return_price"] ?? ("percent" == $item["return_price_type"] ? 100 : $item["price"]);
            $final_price =
                "percent" == $item["return_price_type"]
                    ? $item["price"] * (1 - $item["return_price"] / 100)
                    : $item["price"] - $item["return_price"];
            $item["total"] = $item["quantity"] * $final_price;
        }

        // calculate total of return purchase sheet
        $total = 0;
        foreach ($items as $item) {
            $total += $item["total"];
        }

        // calculate discount
        $data["discount_type"] = $data["discount_type"] ?? "percent";
        $data["discount"] = $data["discount"] ?? 0;
        $total_discount = "percent" == $data["discount_type"] ? ($data["discount"] / 100) * $total : $data["discount"];
        $total -= $total_discount;

        // if return purchase sheet code is not set, generate one
        if (!isset($data["code"])) {
            $return_purchase_sheet_count = ReturnPurchaseSheet::where("branch_id", $branch_id)->count();
            $code = "RPS" . str_pad($return_purchase_sheet_count + 1, 6, "0", STR_PAD_LEFT);
            // ensure code is unique
            while (
                ReturnPurchaseSheet::where("code", $code)
                    ->where("branch_id", $branch_id)
                    ->exists()
            ) {
                ++$return_purchase_sheet_count;
                $code = "RPS" . str_pad($return_purchase_sheet_count, 6, "0", STR_PAD_LEFT);
            }
            $data["code"] = $code;
        }

        // create return purchase sheet
        $return_purchase_sheet = ReturnPurchaseSheet::create([
            "code" => $data["code"],
            "purchase_sheet_id" => $data["purchase_sheet_id"],
            "employee_id" => $employee_id,
            "branch_id" => $branch_id,
            "supplier_id" => $data["supplier_id"],
            "discount" => $data["discount"],
            "discount_type" => $data["discount_type"],
            "total" => $total,
            "paid_amount" => $data["paid_amount"],
            "note" => $data["note"] ?? null,
        ]);

        // create return purchase sheet items
        $itemData = [];
        foreach ($items as $item) {
            $itemData[] = [
                "return_purchase_sheet_id" => $return_purchase_sheet->id,
                "item_id" => $item["item_id"],
                "quantity" => $item["quantity"],
                "price" => $item["price"],
                "return_price" => $item["return_price"],
                "return_price_type" => $item["return_price_type"],
                "total" => $item["total"],
            ];

            // recalculate item base price and quantity
            $item_property = ItemProperty::where("item_id", $item["item_id"])
                ->where("branch_id", $branch_id)
                ->first();
            $new_quantity = max($item_property->quantity - $item["quantity"], 0);
            $new_base_price =
                $new_quantity > 0
                    ? max(($item_property->price * $item_property->quantity - $item["total"]) / $new_quantity, 0)
                    : 0;

            $item_property->update([
                "quantity" => $new_quantity,
                "base_price" => $new_base_price,
            ]);
        }
        $items = ReturnPurchaseSheetItem::insert($itemData);
        $purchase_sheet["items"] = $items;

        return response()->json([
            "message" => "Return purchase sheet created successfully",
        ]);
    }

    public function getReturnPurchaseSheets(Request $request)
    {
        $branch_id = Auth::user()->employment->branch_id;

        [$search, $from, $to, $order_by, $order_type] = $this->getQuery($request);

        // search by employee, branch, supplier, code, note
        $return_purchase_sheets = ReturnPurchaseSheet::with("supplier")
            ->where("branch_id", $branch_id)
            ->orWhereHas("supplier", fn($query) => $query->where("name", "iLike", "%" . $search . "%"))
            ->orWhere("code", "iLike", "%" . $search . "%")
            ->orWhere("note", "iLike", "%" . $search . "%")
            ->orderBy($order_by, $order_type)
            ->offset($from)
            ->limit($to)
            ->get();

        return response()->json($return_purchase_sheets);
    }

    public function getReturnPurchaseSheet(Request $request, $id)
    {
        $branch_id = Auth::user()->employment->branch_id;
        $return_purchase_sheet = ReturnPurchaseSheet::with(
            "employee",
            "branch",
            "supplier",
            "returnPurchaseSheetItems.item"
        )
            ->where("branch_id", $branch_id)
            ->where("id", $id)
            ->first();
        if (!$return_purchase_sheet) {
            return response()->json(
                [
                    "message" => "Return purchase sheet not found",
                ],
                404
            );
        }
        $return_purchase_sheet->items = ReturnPurchaseSheetItem::where(
            "return_purchase_sheet_id",
            $return_purchase_sheet->id
        )->get();

        return response()->json($return_purchase_sheet);
    }

    public function getReturnableItems(Request $request, $purchase_sheet_id)
    {
        $branch_id = Auth::user()->employment->branch_id;
        $purchase_sheet = PurchaseSheet::with("purchaseSheetItems")
            ->where("branch_id", $branch_id)
            ->where("id", $purchase_sheet_id)
            ->first();

        if (!$purchase_sheet) {
            return response()->json(
                [
                    "message" => "Purchase sheet not found",
                ],
                404
            );
        }

        $previous_return_purchase_sheets = ReturnPurchaseSheet::with("returnPurchaseSheetItems")
            ->where("purchase_sheet_id", $purchase_sheet_id)
            ->get();

        $returnable_items = [];
        foreach ($purchase_sheet->purchaseSheetItems as $purchase_sheet_item) {
            // sum quantity from previous return purchase sheets of this item
            $quantity = 0;
            foreach ($previous_return_purchase_sheets as $return_purchase_sheet) {
                foreach ($return_purchase_sheet->return_purchase_sheet_items as $return_purchase_sheet_item) {
                    if ($return_purchase_sheet_item->item_id == $purchase_sheet_item->item_id) {
                        $quantity += $return_purchase_sheet_item->quantity;
                    }
                }
            }

            // calculate returnable quantity
            $returnable_quantity = $purchase_sheet_item->quantity - $quantity;

            $item = Item::where("id", $purchase_sheet_item->item_id)->first();
            $item_base_price = ItemProperty::where("item_id", $item->id)
                ->where("branch_id", $branch_id)
                ->first()->base_price;

            $returnable_items[] = [
                ...$item->toArray(),
                "quantity" => $returnable_quantity,
                "base_price" => $item_base_price,
            ];
        }

        return response()->json($returnable_items);
    }

    public function update(Request $request, $id)
    {
        $store_id = Auth::user()->store_id;
        $branch_id = Auth::user()->employment->branch_id;
        $data = $request->all();
        $data["id"] = $id;
        $rules = [
            "id" => [
                "required",
                "integer",
                Rule::exists("return_purchase_sheets", "id")
                    ->where("branch_id", $branch_id)
                    ->where("employee_id", Auth::user()->id),
            ],
            "discount" => ["nullable", "numeric", "min:0"],
            "discount_type" => ["nullable", "string", "max:255"],
            "paid_amount" => ["required", "numeric", "min:0"],
            "note" => ["nullable", "string", "max:255"],

            "items" => ["required", "array", "min:1"],
            "items.*.item_id" => ["required", "integer", Rule::exists("items", "id")->where("store_id", $store_id)],
            "items.*.quantity" => ["required", "numeric", "min:0"],
            "items.*.price" => ["required", "numeric", "min:0"],
            "items.*.return_price" => ["required", "numeric", "min:0"],
            "items.*.return_price_type" => ["required", "string", "max:255"],
        ];

        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return response()->json(
                [
                    "message" => "Validation Failed",
                    "errors" => $validator->errors(),
                ],
                422
            );
        }

        $return_purchase_sheet = ReturnPurchaseSheet::find($id);

        // calculate total for each item
        $items = $data["items"];
        foreach ($items as &$item) {
            $final_price =
                "percent" == $item["return_price_type"]
                    ? ($item["return_price"] / 100) * $item["price"]
                    : $item["return_price"];
            $item["total"] = $item["quantity"] * $final_price;
        }

        // calculate total of return purchase sheet
        $total = 0;
        foreach ($items as $item) {
            $total += $item["total"];
        }

        // calculate discount
        $discount = "percent" == $data["discount_type"] ? ($data["discount"] / 100) * $total : $data["discount"];
        $total -= $discount;

        // update return purchase sheet
        $return_purchase_sheet->update([
            "discount" => $discount,
            "discount_type" => $data["discount_type"],
            "total" => $total,
            "paid_amount" => $data["paid_amount"],
            "note" => $data["note"],
        ]);

        // update return purchase sheet items
        $itemData = [];
        foreach ($items as $item) {
            $itemData[] = [
                "return_purchase_sheet_id" => $return_purchase_sheet->id,
                "item_id" => $item["item_id"],
                "quantity" => $item["quantity"],
                "price" => $item["price"],
                "return_price" => $item["return_price"],
                "return_price_type" => $item["return_price_type"],
                "total" => $item["total"],
            ];

            $item_property = ItemProperty::where("item_id", $item["item_id"])->first();
            // find previous return purchase sheet item
            $previous_return_purchase_sheet_item = ReturnPurchaseSheetItem::where(
                "return_purchase_sheet_id",
                $return_purchase_sheet->id
            )
                ->where("item_id", $item["item_id"])
                ->first();

            if ($previous_return_purchase_sheet_item) {
                $old_quantity = $item_property->quantity + $previous_return_purchase_sheet_item->quantity;
                $old_base_price =
                    ($item_property->base_price * $item_property->quantity +
                        $previous_return_purchase_sheet_item->total) /
                    $old_quantity;
                $item_property->update([
                    "quantity" => $old_quantity,
                    "base_price" => $old_base_price,
                ]);
            }

            $new_quantity = $item_property->quantity - $item["quantity"];
            $new_base_price = ($item_property->price * $item_property->quantity - $item["total"]) / $new_quantity;

            $item_property->update([
                "quantity" => $new_quantity,
                "base_price" => $new_base_price,
            ]);
        }

        // delete old return purchase sheet items
        $return_purchase_sheet->returnPurchaseSheetItems()->delete();

        $items = ReturnPurchaseSheetItem::insert($itemData);

        return response()->json($return_purchase_sheet);
    }

    public function updateNote(Request $request, $id)
    {
        $branch_id = Auth::user()->employment->branch_id;
        $data = $request->all();
        $data["id"] = $id;
        $rules = [
            "id" => [
                "required",
                "integer",
                Rule::exists("return_purchase_sheets", "id")
                    ->where("branch_id", $branch_id)
                    ->where("employee_id", Auth::user()->id),
            ],
            "note" => ["required", "string", "max:255"],
        ];

        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return response()->json(
                [
                    "message" => "Validation Failed",
                    "errors" => $validator->errors(),
                ],
                400
            );
        }

        $return_purchase_sheet = ReturnPurchaseSheet::find($id);
        $return_purchase_sheet->update([
            "note" => $data["note"],
        ]);

        return response()->json($return_purchase_sheet);
    }

    public function delete(Request $request, $id)
    {
        $branch_id = Auth::user()->employment->branch_id;
        $employee_id = Auth::user()->id;
        $return_purchase_sheet = ReturnPurchaseSheet::where("branch_id", $branch_id)
            ->where("employee_id", $employee_id)
            ->where("id", $id)
            ->first();
        if (!$return_purchase_sheet) {
            return response()->json(
                [
                    "message" => "Return purchase sheet not found",
                ],
                404
            );
        }

        // recalculate base price and quantity for each item
        foreach ($return_purchase_sheet->returnPurchaseSheetItems as $return_purchase_sheet_item) {
            $item_property = ItemProperty::where("item_id", $return_purchase_sheet_item->item_id)
                ->where("branch_id", $branch_id)
                ->first();
            $old_quantity = $item_property->quantity + $return_purchase_sheet_item->quantity;
            $old_base_price =
                ($item_property->base_price * $item_property->quantity + $return_purchase_sheet_item->total) /
                $old_quantity;
            $item_property->update([
                "quantity" => $old_quantity,
                "base_price" => $old_base_price,
            ]);
        }

        // delete all return purchase sheet items
        $return_purchase_sheet->returnPurchaseSheetItems()->delete();

        // delete return purchase sheet
        $return_purchase_sheet->delete();

        return response()->json([
            "message" => "Return purchase sheet deleted",
        ]);
    }
}
