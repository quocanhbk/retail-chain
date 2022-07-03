<?php

namespace App\Http\Controllers;

use App\Models\DefaultItem;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\ItemPriceHistory;
use App\Models\ItemProperty;
use App\Models\PurchaseSheetItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

// https://149.28.148.73/merged-db

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
            $code = "SP" . str_pad($count, 6, "0", STR_PAD_LEFT);
        }
        return $code;
    }

    /**
     * @OA\Post(
     *   path="/item",
     *   tags={"Item"},
     *   summary="Create new item",
     *   operationId="createItem",
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\MediaType(
     *       mediaType="multipart/form-data",
     *       @OA\Schema(ref="#/components/schemas/CreateItemInput")
     *     )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="successful operation",
     *     @OA\JsonContent(ref="#/components/schemas/Item")
     *   )
     * )
     */
    public function create(Request $request)
    {
        $store_id = $request->get("store_id");

        $data = $request->all();

        $rules = [
            "category_id" => [
                "nullable",
                "integer",
                Rule::exists("item_categories", "id")->where("store_id", $store_id),
            ],
            "code" => ["nullable", "string", "max:255", Rule::unique("items")->where("store_id", $store_id)],
            "barcode" => ["required", "string", "max:255", Rule::unique("items")->where("store_id", $store_id)],
            "name" => ["required", "string", "max:255"],
            "image" => ["nullable", "image", "max:2048"],
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return response()->json(["message" => $this->formatValidationError($validator->errors())], 400);
        }

        $code = $this->genItemCode($store_id);

        $image_path = $request->hasFile("image")
            ? $request
                ->file("image")
                ->storeAs(
                    "images/{$store_id}/items",
                    $store_id . Str::uuid() . "." . $request->file("image")->getClientOriginalExtension()
                )
            : null;

        $item = Item::create([
            "category_id" => $data["category_id"] ?? null,
            "code" => $code,
            "barcode" => $data["barcode"],
            "name" => $data["name"],
            "store_id" => $store_id,
            "image" => $image_path,
            "image_key" => $request->hasFile("image") ? Str::uuid() : null,
        ]);

        return response()->json($item);
    }

    /**
     * @OA\Get(
     *   path="/item/one",
     *   tags={"Item"},
     *   summary="Get item by id",
     *   operationId="getItemById",
     *   @OA\Parameter(name="id", in="query", @OA\Schema(type="integer")),
     *   @OA\Parameter(name="barcode", in="query", @OA\Schema(type="integer")),
     *   @OA\Response(
     *     response=200,
     *     description="successful operation",
     *     @OA\JsonContent(ref="#/components/schemas/ItemWithCategory")
     *   )
     * )
     */
    public function getOne(Request $request)
    {
        $store_id = $request->get("store_id");

        $item_id = $request->query("id");

        $barcode = $request->query("barcode");

        if (!$item_id && !$barcode) {
            return response()->json(["message" => "id or barcode is required"], 400);
        }

        $item = Item::with("itemCategory")
            ->where("store_id", $store_id)
            ->when(isset($item_id), function ($query) use ($item_id) {
                $query->where("id", $item_id);
            })
            ->when(isset($barcode) && !isset($item_id), function ($query) use ($barcode) {
                $query->where("barcode", $barcode);
            })
            ->first();

        if (!$item) {
            return response()->json(["message" => "Item not found"], 404);
        }

        return response()->json($item);
    }

    /**
     * @OA\Get(
     *   path="/item",
     *   tags={"Item"},
     *   summary="Get all items",
     *   operationId="getAllItems",
     *   @OA\Parameter(name="search", in="query", @OA\Schema(type="string")),
     *   @OA\Parameter(name="from", in="query", @OA\Schema(type="integer")),
     *   @OA\Parameter(name="to", in="query", @OA\Schema(type="integer")),
     *   @OA\Parameter(name="order_by", in="query", @OA\Schema(type="string")),
     *   @OA\Parameter(name="order_type", in="query", @OA\Schema(type="string", enum={"asc", "desc"})),
     *   @OA\Response(
     *     response=200,
     *     description="successful operation",
     *     @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Item"))
     *   )
     * )
     */
    public function getMany(Request $request)
    {
        $store_id = $request->get("store_id");

        [$search, $from, $to, $order_by, $order_type] = $this->getQuery($request);

        $items = Item::with("itemCategory")->where("store_id", $store_id)
            ->where(function ($query) use ($search) {
                $query
                    ->where("code", "iLike", "%" . $search . "%")
                    ->orWhere("barcode", "iLike", "%" . $search . "%")
                    ->orWhere("name", "iLike", "%" . $search . "%")
                    ->orWhereHas("itemCategory", function ($query) use ($search) {
                        $query->where("name", "iLike", "%" . $search . "%");
                    });
            })
            ->orderBy($order_by, $order_type)
            ->skip($from)
            ->take($to - $from)
            ->get();

        return response()->json($items);
    }

    /**
     * @OA\Get(
     *   path="/item/selling",
     *   tags={"Item"},
     *   summary="Get all selling items",
     *   operationId="getAllSellingItems",
     *   @OA\Parameter(name="search", in="query", @OA\Schema(type="string")),
     *   @OA\Parameter(name="from", in="query", @OA\Schema(type="integer")),
     *   @OA\Parameter(name="to", in="query", @OA\Schema(type="integer")),
     *   @OA\Parameter(name="order_by", in="query", @OA\Schema(type="string")),
     *   @OA\Parameter(name="order_type", in="query", @OA\Schema(type="string", enum={"asc", "desc"})),
     *   @OA\Response(
     *     response=200,
     *     description="successful operation",
     *     @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/ItemWithProperties"))
     *   )
     * )
     */
    public function getSellingItems(Request $request)
    {
        $store_id = Auth::user()->store_id;

        $branch_id = Auth::user()->employment->branch_id;

        [$search, $from, $to, $order_by, $order_type] = $this->getQuery($request);

        $items = Item::with("category", "properties")
            ->where("store_id", $store_id)
            ->whereHas("properties", function ($query) use ($branch_id) {
                $query->where("branch_id", $branch_id);
            })
            ->where(function ($query) use ($search) {
                $query
                    ->where("code", "iLike", "%" . $search . "%")
                    ->orWhere("barcode", "iLike", "%" . $search . "%")
                    ->orWhere("name", "iLike", "%" . $search . "%")
                    ->orWhereHas("category", function ($query) use ($search) {
                        $query->where("name", "iLike", "%" . $search . "%");
                    });
            })
            ->orderBy($order_by, $order_type)
            ->skip($from)
            ->take($to - $from)
            ->get();

        return response()->json($items);
    }

    /**
     * @OA\Post(
     *   path="/item/move",
     *   tags={"Item"},
     *   summary="Move item",
     *   description="Move item from default to current",
     *   operationId="moveItem",
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       required={"barcode"},
     *       @OA\Property(property="barcode", type="string")
     *     )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="successful operation",
     *     @OA\JsonContent(ref="#/components/schemas/Item")
     *   )
     * )
     */
    public function moveItem(Request $request)
    {
        $store_id = Auth::user()->store_id;

        $barcode = $request->input("barcode");

        if (!$barcode) {
            return response()->json(["message" => "Barcode is required"], 400);
        }

        $default_item = DefaultItem::where("bar_code", $barcode)->first();

        if (!$default_item) {
            return response()->json(["message" => "Item not found"], 404);
        }

        // if item is already in the store, return error
        $item = Item::where("store_id", $store_id)
            ->where("barcode", $barcode)
            ->first();

        if ($item) {
            return response()->json(["message" => "Item already in the store"], 400);
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
            "category_id" => $category->id ?? null,
            "image" => null, // will be dealt with later
        ]);

        return response()->json($item);
    }

    /**
     * @OA\Get(
     *   path="/item/{item_id}/price-history",
     *   tags={"Item"},
     *   summary="Get item price history",
     *   operationId="getItemPriceHistory",
     *   @OA\Parameter(name="item_id", in="path", @OA\Schema(type="integer"), required=true),
     *   @OA\Response(
     *     response=200,
     *     description="successful operation",
     *     @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/ItemPriceHistory"))
     *   )
     * )
     */
    public function getPriceHistory(Request $request, $item_id)
    {
        $store_id = $request->get("store_id");
        // make sure store own item
        $item = Item::where(["store_id" => $store_id, "id" => $item_id])->first();

        if (!$item) {
            return response()->json(["message" => "Item not found"], 404);
        }

        $price_history = ItemPriceHistory::where("item_id", $item_id)->get();

        return response()->json($price_history);
    }

    /**
     * @OA\Put(
     *   path="/item/{item_id}",
     *   tags={"Item"},
     *   summary="Update item",
     *   operationId="updateItem",
     *   @OA\Parameter(name="item_id", in="path", @OA\Schema(type="integer"), required=true),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\MediaType(
     *       mediaType="multipart/form-data",
     *       @OA\Schema(ref="#/components/schemas/UpsertItemInput")
     *     )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="successful operation",
     *     @OA\JsonContent(ref="#/components/schemas/Item")
     *   )
     * )
     */
    public function update(Request $request, $item_id)
    {
        $store_id = $request->get("store_id");

        $data = $request->all();

        $item = Item::where("id", $item_id)
            ->where("store_id", $store_id)
            ->first();

        if (!$item) {
            return response()->json(["message" => "Item not found"], 404);
        }

        $rules = [
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
            "image" => ["nullable", "image", "max:2048"],
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return response()->json(["message" => $this->formatValidationError($validator->errors())], 400);
        }

        $item = Item::find($item_id);
        $item->code = $data["code"] ?? $item->code;
        $item->barcode = $data["barcode"] ?? $item->barcode;
        $item->name = $data["name"] ?? $item->name;
        $item->category_id = $data["category_id"] ?? $item->category_id;

        $has_image = $request->hasFile("image");
        if ($has_image) {
            $image_name = $store_id . Str::uuid() . "." . $request->file("image")->getClientOriginalExtension();
            $item->image = $request->file("image")->storedAs("images/{$store_id}/items", $image_name);
            $item->image_key = Str::uuid();
        }

        $item->save();

        return response()->json($item);
    }

    // get last purchase price of an item
    public function getLastPurchasePrice($item_id)
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
