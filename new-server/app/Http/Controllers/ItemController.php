<?php

namespace App\Http\Controllers;

use App\Models\DefaultItem;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\ItemPriceHistory;
use App\Models\ItemProperty;
use App\Models\ItemQuantity;
use App\Models\PurchaseSheet;
use App\Models\PurchaseSheetItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ItemController extends Controller
{
    private function genItemCode($store_id)
    {
        $count = Item::where("store_id", $store_id)->count();

        $code = $data["code"] ?? "SP" . str_pad($count + 1, 6, "0", STR_PAD_LEFT);
        // ensure code is unique
        while (
            Item::where("code", $code)
                ->where("store_id", $store_id)
                ->exists()
        ) {
            $count++;
            $code = "IT" . str_pad($count, 6, "0", STR_PAD_LEFT);
        }
        return $code;
    }

    // * Create item for the store, initial quantity, base price, sell price for the purchaser's branch
    public function create(Request $request)
    {
        $store_id = Auth::user()->store_id;
        $branch_id = Auth::user()->employment->branch_id;
        $data = $request->all();
        $rules = [
            "category_id" => [
                "required",
                "integer",
                Rule::exists("item_categories", "id")->where("store_id", $store_id),
            ],
            "code" => ["nullable", "string", "max:255", Rule::unique("items")->where("store_id", $store_id)],
            "barcode" => ["required", "string", "max:255", Rule::unique("items")->where("store_id", $store_id)],
            "name" => ["required", "string", "max:255"],
            "quantity" => ["nullable", "numberic", "min:0"],
            "base_price" => ["nullable", "numeric", "min:0"],
            "sell_price" => ["nullable", "numeric", "min:0"],
            "image" => ["nullable", "string", "max:2048"],
        ];

        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return response()->json(
                [
                    "message" => "Validation failed",
                    "errors" => $validator->errors(),
                ],
                400
            );
        }

        $code = $this->genItemCode($store_id);

        $item = Item::create([
            "category_id" => $data["category_id"],
            "code" => $code,
            "barcode" => $data["barcode"],
            "name" => $data["name"],
            "store_id" => $store_id,
            "image" => $data["image"],
        ]);

        ItemProperty::create([
            "item_id" => $item->id,
            "branch_id" => $branch_id,
            "quantity" => $data["quantity"] ?? 0,
            "sell_price" => $data["sell_price"] ?? 0,
            "base_price" => $data["base_price"] ?? 0,
        ]);

        return response()->json($item);
    }

    public function updateImage(Request $request)
    {
        $store_id = Auth::user()->store_id;
        $rules = [
            "id" => ["required", "integer", Rule::exists("items", "id")->where("store_id", $store_id)],
            "image" => ["nullable", "image", "mimes:jpeg,png,jpg", "max:2048"],
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(
                [
                    "message" => "Validation failed",
                    "errors" => $validator->errors(),
                ],
                400
            );
        }

        $item = Item::find($request->id);
        $path = $request
            ->file("image")
            ->storeAs(
                "items",
                $store_id . $item->barcode . "." . $request->file("image")->getClientOriginalExtension()
            );
        $item->image = $path;
        $item->save();

        return response()->json([
            "message" => "Image updated",
        ]);
    }

    // * Only used for item that is already added to the database
    public function getItem(Request $request, $item_id)
    {
        $store_id = $request->get("store_id");
        $item = Item::where("id", $item_id)
            ->where("store_id", $store_id)
            ->first();
        return response()->json($item);
    }

    // * Used for both item from the database and item from the default database
    public function getItemByBarCode(Request $request, $barcode)
    {
        $store_id = $request->get("store_id");
        $item = Item::where("store_id", $store_id)
            ->where("barcode", $barcode)
            ->first();

        if ($item) {
            $item["type"] = "current";
            return response()->json($item);
        }

        // else, search in the default items
        $item = DefaultItem::with("category")
            ->where("bar_code", $barcode)
            ->first();

        if ($item) {
            return response()->json([
                "id" => $item->id,
                "store_id" => $store_id,
                "barcode" => $item->bar_code,
                "name" => $item->product_name,
                "image" => "https://149.28.148.73/merged-db/" . $item->image_url,
                "category" => $item->category,
                "type" => "default",
            ]);
        }

        return response()->json(
            [
                "message" => "Item not found",
            ],
            404
        );
    }

    public function getItemsBySearch(Request $request)
    {
        $store_id = $request->get("store_id");
        $search = $request->query("search") ?? "";
        $count = $request->query("count") ?? 10;

        $current_items = Item::where("store_id", $store_id)
            ->where("code", "iLike", "%" . $search . "%")
            ->orWhere("barcode", "iLike", "%" . $search . "%")
            ->orWhere("name", "iLike", "%" . $search . "%")
            ->orWhereHas("category", function ($query) use ($search) {
                $query->where("name", "iLike", "%" . $search . "%");
            })
            ->take($count)
            ->get();

        $default_items = DefaultItem::with("category")
            ->where("product_name", "iLike", "%" . $search . "%")
            ->orWhere("bar_code", "iLike", "%" . $search . "%")
            ->orWhereHas("category", function ($query) use ($search) {
                $query->where("name", "iLike", "%" . $search . "%");
            })
            ->take($count)
            ->get(["id", "product_name AS name", "bar_code AS barcode", "image_url AS image", "category_id"]);

        foreach ($default_items as $item) {
            $item["image"] = "https://149.28.148.73/merged-db/" . $item["image"];
        }

        // remove default items that have barcode that is already in the current items
        foreach ($current_items as $item) {
            $default_items = $default_items->filter(function ($value, $key) use ($item) {
                return $value["barcode"] != $item["barcode"];
            });
        }

        $items = [
            "current" => $current_items,
            // using flatten to turn an object into an array
            "default" => $default_items->flatten(),
        ];

        return response()->json($items);
    }

    public function getSellingItems(Request $request)
    {
        $store_id = Auth::user()->store_id;
        $branch_id = Auth::user()->employment->branch_id;

        [$search, $from, $to, $order_by, $order_type] = $this->getQuery($request);

        $items = Item::with("category", "itemProperties")
            ->where("store_id", $store_id)
            ->whereHas("itemProperties", function ($query) use ($branch_id) {
                $query->where("branch_id", $branch_id);
            })
            ->where("name", "iLike", "%" . $search . "%")
            ->orWhere("barcode", "iLike", "%" . $search . "%")
            ->orWhereHas("category", function ($query) use ($search) {
                $query->where("name", "iLike", "%" . $search . "%");
            })
            ->orderBy($order_by, $order_type)
            ->skip($from)
            ->take($to - $from)
            ->get();

        return response()->json($items);
    }

    // move item from default to current
    public function moveItem(Request $request)
    {
        $store_id = Auth::user()->store_id;
        $branch_id = Auth::user()->employment->branch_id;
        $barcode = $request->input("barcode");
        if (!$barcode) {
            return response()->json(
                [
                    "message" => "Barcode is required",
                ],
                400
            );
        }

        $default_item = DefaultItem::where("bar_code", $barcode)->first();
        if (!$default_item) {
            return response()->json(
                [
                    "message" => "Item not found",
                ],
                404
            );
        }

        // if item is already in the store, return error
        $item = Item::where("store_id", $store_id)
            ->where("barcode", $barcode)
            ->first();
        if ($item) {
            return response()->json(
                [
                    "message" => "Item already in the store",
                ],
                400
            );
        }

        // find a suitable category
        $category = ItemCategory::where("store_id", $store_id)
            ->where("name", "iLike", "%" . $default_item->category->name . "%")
            ->first();

        $code = $this->genItemCode($store_id);

        $item = Item::create([
            "store_id" => $store_id,
            "barcode" => $default_item->bar_code,
            "code" => $code,
            "name" => $default_item->product_name,
            "category_id" => $category->id,
            "image" => "https://149.28.148.73/merged-db/" . $default_item->image_url,
        ]);

        ItemProperty::create([
            "item_id" => $item->id,
            "branch_id" => $branch_id,
            "quantity" => 0,
            "sell_price" => 0,
            "base_price" => 0,
        ]);

        return response()->json($item);
    }

    public function getPriceHistory(Request $request, $item_id)
    {
        $store_id = $request->get("store_id");
        // make sure store own item
        $item = Item::where("id", $item_id)
            ->where("store_id", $store_id)
            ->first();
        if (!$item) {
            return response()->json(
                [
                    "message" => "Item not found",
                ],
                404
            );
        }

        $price_history = ItemPriceHistory::where("item_id", $item_id)->get();
        return response()->json($price_history);
    }

    // maybe only for manager ?
    public function update(Request $request, $item_id)
    {
        $store_id = Auth::guard("stores")->user()->id;
        $data = $request->all();
        $data["id"] = $item_id;
        $rules = [
            "id" => ["required", "numeric", Rule::exists("items")->where("store_id", $store_id)],
            "code" => [
                "nullable",
                "string",
                "max:255",
                Rule::unique("items")
                    ->where("store_id", $store_id)
                    ->ignore($item_id),
            ],
            "barcode" => [
                "nullable",
                "string",
                "max:255",
                Rule::unique("items")
                    ->where("store_id", $store_id)
                    ->ignore($item_id),
            ],
            "name" => ["nullable", "string", "max:255"],
            "category_id" => [
                "nullable",
                "integer",
                Rule::exists("item_categories", "id")->where("store_id", $store_id),
            ],
        ];

        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return response()->json(
                [
                    "message" => "Validation failed",
                    "errors" => $validator->errors(),
                ],
                400
            );
        }

        $item = Item::find($item_id);
        $item->code = $data["code"] ?? $item->code;
        $item->barcode = $data["barcode"] ?? $item->barcode;
        $item->name = $data["name"] ?? $item->name;
        $item->category_id = $data["category_id"] ?? $item->category_id;
        $item->save();

        return response()->json($item);
    }

    // get last purchase price of an item
    public function getLastPurchasePrice(Request $request, $item_id)
    {
        $branch_id = Auth::user()->employment->branch_id;

        // get last purchase sheet item of an item
        $purchase_sheet_item = PurchaseSheetItem::where("item_id", $item_id)
            ->whereHas("purchaseSheet", function ($query) use ($branch_id) {
                $query->where("branch_id", $branch_id);
            })
            ->orderBy("id", "desc")
            ->first();

        if (!$purchase_sheet_item) {
            return response()->json(0);
        }

        return response()->json($purchase_sheet_item->price);
    }

    // get items from a purchase sheet
    public function getItemsFromPurchaseSheet(Request $request, $purchase_sheet_id)
    {
        $branch_id = Auth::user()->employment->branch_id;

        $search = $request->query("search") ?? "";

        $purchase_sheet_items = PurchaseSheetItem::with("item")
            ->where("purchase_sheet_id", $purchase_sheet_id)
            ->where("branch_id", $branch_id)
            ->whereHas("item", function ($query) use ($search) {
                $query->where("name", "iLike", "%" . $search . "%");
            })
            ->orWhereHas("item", function ($query) use ($search) {
                $query->where("barcode", "iLike", "%" . $search . "%");
            })
            ->get();

        foreach ($purchase_sheet_items as &$purchase_sheet_item) {
            $property = ItemProperty::where("item_id", $purchase_sheet_id->item_id)
                ->where("branch_id", $branch_id)
                ->first();
            $purchase_sheet_item["property"] = $property;
        }

        return response()->json($purchase_sheet_items);
    }
}
