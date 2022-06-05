<?php

namespace App\Http\Controllers;

use App\Models\ItemProperty;
use App\Models\QuantityCheckingItem;
use App\Models\QuantityCheckingSheet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class QuantityCheckingSheetController extends Controller
{
    public function create(Request $request)
    {
        $employee_id = Auth::user()->id;
        $branch_id = Auth::user()->employment->branch_id;

        $data = $request->all();
        $rules = [
            "code" => ["nullable", "string", "max:255"],
            "note" => ["nullable", "string", "max:255"],

            "items" => ["required", "array", "min:1"],
            "items.*.item_id" => [
                "required",
                "integer",
                Rule::exists("item_properties", "item_id")->where("branch_id", $branch_id),
            ],
            "items.*.actual" => ["required", "integer", "min:0"],
        ];

        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return response()->json(["error" => $validator->errors()], 400);
        }

        // if code is not set, generate one
        if (!isset($data["code"])) {
            $return_sheet_count = QuantityCheckingSheet::where("branch_id", $branch_id)->count();
            $code = "QS" . str_pad($return_sheet_count + 1, 6, "0", STR_PAD_LEFT);

            // ensure code is unique
            while (
                QuantityCheckingSheet::where("code", $code)
                    ->where("branch_id", $branch_id)
                    ->count() > 0
            ) {
                $return_sheet_count++;
                $code = "QS" . str_pad($return_sheet_count + 1, 6, "0", STR_PAD_LEFT);
            }
            $data["code"] = $code;
        }

        $quantity_checking_sheet = QuantityCheckingSheet::create([
            "code" => $data["code"],
            "employee_id" => $employee_id,
            "branch_id" => $branch_id,
            "note" => $data["note"] ?? null,
        ]);

        // calculate total of each item
        $items = $data["items"];
        $items_data = [];
        foreach ($items as &$item) {
            $item_property = ItemProperty::where("branch_id", $branch_id)
                ->where("item_id", $item->id)
                ->first();
            $item["expected"] = $item_property->quantity;
            $item_base_price = $item_property->base_price;
            $item["total"] = ($item["actual"] - $item["expected"]) * $item_base_price;
            $items_data[] = [
                "quantity_checking_sheet_id" => $quantity_checking_sheet->id,
                ...$item,
            ];

            // update item base price and quantity
            $item_property = ItemProperty::where("item_id", $item["item_id"])
                ->where("branch_id", $branch_id)
                ->first();
            if ($item_property) {
                $item_property->update([
                    "quantity" => $item["actual"],
                ]);
            }
        }

        QuantityCheckingItem::insert($items_data);

        return response()->json([
            "message" => "Quantity checking sheet created successfully",
        ]);
    }

    public function getQuantityCheckingSheets(Request $request)
    {
        $branch_id = Auth::user()->employment->branch_id;

        [$search, $from, $to, $order_by, $order_type] = $this->getQuery($request);

        $quantity_checking_sheets = QuantityCheckingSheet::with(["employee"])
            ->where("branch_id", $branch_id)
            ->where("code", "iLike", "%" . $search . "%")

            ->orderBy($order_by, $order_type)
            ->offset($from)
            ->limit($to)
            ->get();

        return response()->json($quantity_checking_sheets);
    }

    public function getQuantityCheckingSheet(Request $request, $id)
    {
        $branch_id = Auth::user()->employment->branch_id;
        $quantity_checking_sheet = QuantityCheckingSheet::with(["employee", "branch", "quantityCheckingItems"])
            ->where("id", $id)
            ->where("branch_id", $branch_id)
            ->first();

        if (!$quantity_checking_sheet) {
            return response()->json(
                [
                    "error" => "Quantity checking sheet not found",
                ],
                404
            );
        }

        return response()->json($quantity_checking_sheet);
    }
}
