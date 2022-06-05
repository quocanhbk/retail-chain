<?php

namespace App\Http\Controllers;

use App\Models\ItemProperty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PriceController extends Controller
{
    public function updateSellingPrice(Request $request)
    {
        $branch_id = Auth::user()->employment->branch_id;

        $data = $request->all();
        $rules = [
            "id" => ["required", Rule::exists("item_properties", "item_id")->where("branch_id", $branch_id)],
            "sell_price" => ["required", "numeric", "min:0"],
        ];

        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return response()->json(["errors" => $validator->errors()], 400);
        }

        $item_property = ItemProperty::where("item_id", $data["id"])
            ->where("branch_id", $branch_id)
            ->first();
        $item_property->sell_price = $data["sell_price"];
        $item_property->save();

        return response()->json(
            [
                "message" => "Selling price updated successfully",
            ],
            200
        );
    }
}
