<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\DefaultItem;
use App\Models\Item;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
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
            ++$count;
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
            "category_id" => ["nullable", "integer", Rule::exists("categories", "id")->where("store_id", $store_id)],
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

        $id = $request->query("id");

        $barcode = $request->query("barcode");

        if (!$id && !$barcode) {
            return response()->json(["message" => "id or barcode is required"], 400);
        }

        $item = Item::with("category")
            ->where("store_id", $store_id)
            ->when(isset($id), fn($query) => $query->where("id", $id))
            ->when(isset($barcode) && !isset($id), fn($query) => $query->where("barcode", $barcode))
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

        $items = Item::with("category")
            ->where("store_id", $store_id)
            ->where(
                fn($query) => $query
                    ->where("code", "iLike", "%" . $search . "%")
                    ->orWhere("barcode", "iLike", "%" . $search . "%")
                    ->orWhere("name", "iLike", "%" . $search . "%")
                    ->orWhereRelation("category", "name", "iLike", "%" . $search . "%")
            )
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
     *   operationId="getSelling",
     *   @OA\Parameter(name="search", in="query", @OA\Schema(type="string")),
     *   @OA\Parameter(name="from", in="query", @OA\Schema(type="integer")),
     *   @OA\Parameter(name="to", in="query", @OA\Schema(type="integer")),
     *   @OA\Parameter(name="order_by", in="query", @OA\Schema(type="string")),
     *   @OA\Parameter(name="order_type", in="query", @OA\Schema(type="string", enum={"asc", "desc"})),
     *   @OA\Parameter(name="branch_id", in="query", @OA\Schema(type="integer")),
     *   @OA\Response(
     *     response=200,
     *     description="successful operation",
     *     @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/ItemWithProperties"))
     *   )
     * )
     */
    public function getSelling(Request $request)
    {
        $store_id = $request->get("store_id");

        $as = $request->get("as");

        if ("admin" == $as && !$request->query("branch_id")) {
            return response()->json(["message" => "branch_id is required"], 400);
        }

        $branch_id = "admin" == $as ? $request->query("branch_id") : Auth::user()->employment->branch_id;

        [$search, $from, $to, $order_by, $order_type] = $this->getQuery($request);

        $items = Item::with("category", "properties")
            ->where("store_id", $store_id)
            ->whereRelation("properties", "branch_id", $branch_id)
            ->where(
                fn($query) => $query
                    ->where("code", "iLike", "%" . $search . "%")
                    ->orWhere("barcode", "iLike", "%" . $search . "%")
                    ->orWhere("name", "iLike", "%" . $search . "%")
                    ->orWhereRelation("category", "name", "iLike", "%" . $search . "%")
            )
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
        $store_id = $request->get("store_id");

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
        $category = Category::where("store_id", $store_id)
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
     * @OA\Put(
     *   path="/item/{id}",
     *   tags={"Item"},
     *   summary="Update item",
     *   operationId="updateItem",
     *   @OA\Parameter(name="id", in="path", @OA\Schema(type="integer"), required=true),
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
    public function update(Request $request, $id)
    {
        $store_id = $request->get("store_id");

        $data = $request->all();

        $item = Item::where("id", $id)
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
                    ->ignore($id),
            ],
            "barcode" => [
                "nullable",
                "string",
                "max:255",
                Rule::unique("items")
                    ->where("store_id", $store_id)
                    ->ignore($id),
            ],
            "name" => ["nullable", "string", "max:255"],
            "category_id" => ["nullable", "integer", Rule::exists("categories", "id")->where("store_id", $store_id)],
            "image" => ["nullable", "image", "max:2048"],
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return response()->json(["message" => $this->formatValidationError($validator->errors())], 400);
        }

        $item = Item::find($id);
        $item->code = $data["code"] ?? $item->code;
        $item->barcode = $data["barcode"] ?? $item->barcode;
        $item->name = $data["name"] ?? $item->name;
        $item->category_id = $data["category_id"] ?? $item->category_id;

        $has_image = $request->hasFile("image");
        if ($has_image) {
            if ($item->image) {
                Storage::delete($item->image);
            }
            $image_name = $store_id . Str::uuid() . "." . $request->file("image")->getClientOriginalExtension();
            $item->image = $request->file("image")->storeAs("images/{$store_id}/items", $image_name);
            $item->image_key = Str::uuid();
        }

        $item->save();

        return response()->json($item);
    }

    /**
     * @OA\Delete(
     *   path="/item/{id}",
     *   tags={"Item"},
     *   summary="Delete item",
     *   operationId="deleteItem",
     *   @OA\Parameter(name="id", in="path", @OA\Schema(type="integer"), required=true),
     *   @OA\Parameter(name="force", in="query", @OA\Schema(type="boolean")),
     *   @OA\Response(
     *     response=200,
     *     description="successful operation",
     *     @OA\JsonContent(ref="#/components/schemas/Message")
     *   )
     * )
     */
    public function delete(Request $request, $id)
    {
        $store_id = $request->get("store_id");

        $as = $request->get("as");

        $force = $request->query("force") ?? false;

        $item = Item::where("id", $id)
            ->where("store_id", $store_id)
            ->first();

        if (!$item) {
            return response()->json(["message" => "Item not found"], 404);
        }

        if ("employee" == $as && $force) {
            return response()->json(["message" => "Forced delete is not allowed"], 403);
        }

        $item->when($force, fn($query) => $query->forceDelete(), fn($query) => $query->delete());

        return response()->json(["message" => "Item deleted"]);
    }

    /**
     * @OA\Get(
     *   path="/item/deleted",
     *   tags={"Item"},
     *   summary="Get deleted items",
     *   operationId="getDeletedItems",
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
    public function getDeleted(Request $request)
    {
        $store_id = $request->get("store_id");

        [$search, $from, $to, $order_by, $order_type] = $this->getQuery($request);

        $items = Item::onlyTrashed()
            ->with("category")
            ->where("store_id", $store_id)
            ->where(
                fn($query) => $query
                    ->where("code", "iLike", "%" . $search . "%")
                    ->orWhere("barcode", "iLike", "%" . $search . "%")
                    ->orWhere("name", "iLike", "%" . $search . "%")
                    ->orWhereRelation("category", "name", "iLike", "%" . $search . "%")
            )
            ->orderBy($order_by, $order_type)
            ->skip($from)
            ->take($to - $from)
            ->get();

        return response()->json($items);
    }

    /**
     * @OA\Post(
     *   path="/item/{id}/restore",
     *   tags={"Item"},
     *   summary="Restore item",
     *   operationId="restoreItem",
     *   @OA\Parameter(name="id", in="path", @OA\Schema(type="integer"), required=true),
     *   @OA\Response(
     *     response=200,
     *     description="successful operation",
     *     @OA\JsonContent(ref="#/components/schemas/Message")
     *   )
     * )
     */
    public function restore(Request $request, $id)
    {
        $store_id = $request->get("store_id");

        $item = Item::onlyTrashed()
            ->where("id", $id)
            ->where("store_id", $store_id)
            ->first();

        if (!$item) {
            return response()->json(["message" => "Item not found"], 404);
        }

        $item->restore();

        return response()->json(["message" => "Item restored"]);
    }

    /**
     * @OA\Delete(
     *   path="/item/{id}/force",
     *   tags={"Item"},
     *   summary="Force delete item",
     *   operationId="forceDeleteItem",
     *   @OA\Parameter(name="id", in="path", @OA\Schema(type="integer"), required=true),
     *   @OA\Response(
     *     response=200,
     *     description="successful operation",
     *     @OA\JsonContent(ref="#/components/schemas/Message")
     *   )
     * )
     */
    public function forceDelete(Request $request, $id)
    {
        $store_id = $request->get("store_id");

        $item = Item::withTrashed()
            ->where(["store_id" => $store_id, "id" => $id])
            ->first();

        if (!$item) {
            return response()->json(["message" => "Item not found"], 404);
        }

        $item->forceDelete();

        return response()->json(["message" => "Item deleted"]);
    }
}
