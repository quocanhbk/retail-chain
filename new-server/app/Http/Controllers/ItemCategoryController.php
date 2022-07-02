<?php

namespace App\Http\Controllers;

use App\Models\ItemCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ItemCategoryController extends Controller
{
    /**
     * @OA\Post(
     *   path="/item-category",
     *   tags={"ItemCategory"},
     *   summary="Create a new item category",
     *   operationId="createItemCategory",
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(ref="#/components/schemas/CreateItemCategoryInput")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(ref="#/components/schemas/ItemCategory")
     *   )
     * )
     */
    public function create(Request $request)
    {
        $store_id = $request->get("store_id");

        $data = $request->all();

        $rules = [
            "name" => ["required", "string", "max:32", Rule::unique("item_categories")->where("store_id", $store_id)],
            "description" => ["nullable", "string", "max:255"],
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return response()->json(["message" => $this->formatValidationError($validator->errors())], 400);
        }

        $item_category = ItemCategory::create([
            "store_id" => $store_id,
            "name" => $data["name"],
            "description" => $data["description"] ?? null,
        ]);

        return response()->json($item_category);
    }

    /**
     * @OA\Get(
     *   path="/item-category",
     *   tags={"ItemCategory"},
     *   summary="Get item categories",
     *   operationId="getItemCategories",
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
     *       @OA\Items(ref="#/components/schemas/ItemCategory")
     *     )
     *   )
     * )
     */
    public function getMany(Request $request)
    {
        $store_id = $request->get("store_id");

        [$search, $from, $to, $order_by, $order_type] = $this->getQuery($request);

        $item_categories = ItemCategory::where("store_id", $store_id)
            ->where(function ($query) use ($search) {
                $query->where("name", "iLike", "%{$search}%")->orWhere("description", "iLike", "%{$search}%");
            })
            ->orderBy($order_by, $order_type)
            ->offset($from)
            ->limit($to - $from)
            ->get();

        return response()->json($item_categories);
    }

    /**
     * @OA\Get(
     *   path="/item-category/{category_id}",
     *   tags={"ItemCategory"},
     *   summary="Get an item category",
     *   operationId="getItemCategory",
     *   @OA\Parameter(name="category_id", in="path", @OA\Schema(type="integer"), required=true),
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(ref="#/components/schemas/ItemCategory")
     *   )
     * )
     */
    public function getOne(Request $request, $category_id)
    {
        $store_id = $request->get("store_id");

        $item_category = ItemCategory::with("items")
            ->where("store_id", $store_id)
            ->where("id", $category_id)
            ->first();

        if (!$item_category) {
            return response()->json(["message" => "Item category not found"], 404);
        }

        return response()->json($item_category);
    }

    /**
     * @OA\Put(
     *   path="/item-category/{item_category_id}",
     *   tags={"ItemCategory"},
     *   summary="Update an item category",
     *   operationId="updateItemCategory",
     *   @OA\Parameter(name="item_category_id", in="path", @OA\Schema(type="integer"), required=true),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(ref="#/components/schemas/UpsertItemCategoryInput")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(
     *       required={"message"},
     *       @OA\Property(property="message", type="string")
     *     )
     *   )
     * )
     */
    public function update(Request $request, $item_category_id)
    {
        $store_id = $request->get("store_id");

        $data = $request->all();

        $item_category = ItemCategory::where("store_id", $store_id)
            ->where("id", $item_category_id)
            ->first();

        if (!$item_category) {
            return response()->json(["message" => "Item category not found."], 404);
        }

        $rules = [
            "name" => [
                "nullable",
                "string",
                "max:32",
                Rule::unique("item_categories")
                    ->where("store_id", $store_id)
                    ->ignore($item_category_id),
            ],
            "description" => ["nullable", "string", "max:255"],
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return response()->json(["message" => $this->formatValidationError($validator->errors())], 400);
        }

        $item_category->name = $data["name"] ?? $item_category->name;
        $item_category->description = $data["description"] ?? $item_category->description;

        $item_category->save();

        return response()->json([
            "message" => "Updated item category successfully.",
        ]);
    }

    /**
     * @OA\Delete(
     *   path="/item-category/{item_category_id}",
     *   tags={"ItemCategory"},
     *   summary="Delete an item category",
     *   operationId="deleteItemCategory",
     *   @OA\Parameter(name="item_category_id", in="path", @OA\Schema(type="integer"), required=true),
     *   @OA\Parameter(name="force", in="query", @OA\Schema(type="boolean")),
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(
     *       required={"message"},
     *       @OA\Property(property="message", type="string")
     *     )
     *   )
     * )
     */
    public function delete(Request $request, $item_category_id)
    {
        $store_id = $request->get("store_id");

        $as = $request->get("as");

        $force = $request->query("force") ?? false;

        $item_category = ItemCategory::where("store_id", $store_id)
            ->where("id", $item_category_id)
            ->first();

        if (!$item_category) {
            return response()->json(["message" => "Item category not found."], 404);
        }

        if ($as == "employee" && $force) {
            return response()->json(["message" => "You are not allowed to force delete this item category."], 403);
        }

        $item_category
            ->when($force, function ($query) {
                $query->forceDelete();
            })
            ->when(!$force, function ($query) {
                $query->delete();
            });

        return response()->json([
            "message" => "Deleted item category successfully.",
        ]);
    }

    /**
     * @OA\Get(
     *   path="/item-category/deleted",
     *   tags={"ItemCategory"},
     *   summary="Get deleted item categories",
     *   operationId="getDeletedItemCategories",
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
     *       @OA\Items(ref="#/components/schemas/ItemCategory")
     *     )
     *   )
     * )
     */
    public function getDeleted(Request $request)
    {
        $store_id = $request->get("store_id");

        [$search, $from, $to, $order_by, $order_type] = $this->getQuery($request);

        $item_categories = ItemCategory::onlyTrashed()
            ->where("store_id", $store_id)
            ->where(function ($query) use ($search) {
                $query->where("name", "iLike", "%{$search}%")->orWhere("description", "iLike", "%{$search}%");
            })
            ->orderBy($order_by, $order_type)
            ->offset($from)
            ->limit($to - $from)
            ->get();

        return response()->json($item_categories);
    }

    /**
     * @OA\Post(
     *   path="/item-category/{item_category_id}/restore",
     *   tags={"ItemCategory"},
     *   summary="Restore an item category",
     *   operationId="restoreItemCategory",
     *   @OA\Parameter(name="item_category_id", in="path", @OA\Schema(type="integer"), required=true),
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(
     *       required={"message"},
     *       @OA\Property(property="message", type="string")
     *     )
     *   )
     * )
     */
    public function restore(Request $request, $item_category_id)
    {
        $store_id = $request->get("store_id");

        $item_category = ItemCategory::onlyTrashed()
            ->where("store_id", $store_id)
            ->where("id", $item_category_id)
            ->first();

        if (!$item_category) {
            return response()->json(["message" => "Item category not found."], 404);
        }

        $item_category->restore();

        return response()->json([
            "message" => "Restored item category successfully.",
        ]);
    }

    /**
     * @OA\Delete(
     *   path="/item-category/{item_category_id}/force",
     *   tags={"ItemCategory"},
     *   summary="Force delete an item category",
     *   operationId="forceDeleteItemCategory",
     *   @OA\Parameter(name="item_category_id", in="path", @OA\Schema(type="integer"), required=true),
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(
     *       required={"message"},
     *       @OA\Property(property="message", type="string")
     *     )
     *   )
     * )
     */
    public function forceDelete(Request $request, $item_category_id)
    {
        $store_id = $request->get("store_id");

        $item_category = ItemCategory::withTrashed()
            ->where("store_id", $store_id)
            ->where("id", $item_category_id)
            ->first();

        if (!$item_category) {
            return response()->json(["message" => "Item category not found."], 404);
        }

        $item_category->forceDelete();

        return response()->json([
            "message" => "Permanently deleted item category successfully.",
        ]);
    }
}
