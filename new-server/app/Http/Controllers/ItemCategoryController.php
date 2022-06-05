<?php

namespace App\Http\Controllers;

use App\Models\ItemCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ItemCategoryController extends Controller
{
    public function create(Request $request)
    {
        $store_id = Auth::guard("employees")->user()->id;
        $data = $request->all();
        $rules = [
            "name" => ["required", "string", "max:255", Rule::unique("item_categories")->where("store_id", $store_id)],
            "description" => ["nullable", "string", "max:255"],
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return response()->json(
                [
                    "message" => "Validation failed.",
                    "errors" => $validator->errors(),
                ],
                400
            );
        }

        $item_category = ItemCategory::create([
            "store_id" => $store_id,
            "name" => $data["name"],
            "description" => $data["description"] ?? null,
        ]);

        return response()->json($item_category);
    }

    public function createBulk(Request $request)
    {
        $store_id = Auth::guard("employees")->user()->id;
        $data = $request->all();
        $rules = [
            "item_categories" => ["required", "array"],
            "item_categories.*.name" => [
                "required",
                "string",
                "max:255",
                Rule::unique("item_categories")->where("store_id", $store_id),
            ],
            "item_categories.*.description" => ["nullable", "string", "max:255"],
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return response()->json(
                [
                    "message" => "Validation failed.",
                    "errors" => $validator->errors(),
                ],
                400
            );
        }

        $item_categories = [];
        foreach ($data["item_categories"] as $item_category) {
            $item_categories[] = [
                "store_id" => $store_id,
                "name" => $item_category["name"],
                "description" => $item_category["description"] ?? null,
            ];
        }

        ItemCategory::insert($item_categories);

        return response()->json($item_categories);
    }

    public function getItemCategories(Request $request)
    {
        $store_id = Auth::guard("employees")->user()->id;
        $search = $request->query("search");
        $item_categories = ItemCategory::where("store_id", $store_id)
            ->where("name", "iLike", "%" . $search . "%")
            ->get();

        return response()->json($item_categories);
    }

    public function update(Request $request, $item_category_id)
    {
        $store_id = Auth::guard("employees")->user()->id;
        $data = $request->all();
        $data["id"] = $item_category_id;
        $rules = [
            "name" => ["nullable", "string", "max:255"],
            "description" => ["nullable", "string", "max:255"],
            "id" => ["required", "integer", Rule::exists("item_categories", "id")->where("store_id", $store_id)],
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return response()->json(
                [
                    "message" => "Validation failed.",
                    "errors" => $validator->errors(),
                ],
                400
            );
        }

        $item_category = ItemCategory::where("store_id", $store_id)
            ->where("id", $data["id"])
            ->first();
        $item_category->name = $data["name"] ?? $item_category->name;
        $item_category->description = $data["description"] ?? $item_category->description;
        $item_category->save();

        return response()->json($item_category);
    }

    public function delete(Request $request, $item_category_id)
    {
        $store_id = Auth::guard("employees")->user()->id;
        $item_category = ItemCategory::where("store_id", $store_id)
            ->where("id", $item_category_id)
            ->first();
        if (!$item_category) {
            return response()->json(
                [
                    "message" => "Item category not found.",
                ],
                404
            );
        }
        $item_category->delete();
        return response()->json($item_category);
    }
}
