<?php

namespace App\Http\Controllers;

use App\Models\ItemCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            "name" => ["required", "string", "max:255", Rule::unique("item_categories")->where("store_id", $store_id)],
            "description" => ["nullable", "string", "max:255"],
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return response()->json(
                [
                    "message" => $this->formatValidationError($validator->errors()),
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

    /**
     * @OA\Post(
     *   path="/item-category/many",
     *   tags={"ItemCategory"},
     *   summary="Create many item categories",
     *   operationId="createManyItemCategories",
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       @OA\Property(
     *         property="item_categories",
     *         type="array",
     *         @OA\Items(ref="#/components/schemas/CreateItemCategoryInput")
     *       )
     *     )
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
    public function createMany(Request $request)
    {
        $store_id = $request->get("store_id");

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

        // return error if input has duplicate names
        $names = array_unique(array_column($data["item_categories"], "name"));
        if (count($names) !== count($data["item_categories"])) {
            return response()->json(
                [
                    "message" => "Duplicate names.",
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

        return response()->json([
            "message" => "Created item categories successfully.",
        ]);
    }

    /**
     * @OA\Get(
     *   path="/item-category",
     *   tags={"ItemCategory"},
     *   summary="Get all item categories",
     *   operationId="getAllItemCategories",
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
    public function getItemCategories(Request $request)
    {
        $store_id = $request->get("store_id");

        [$search, $from, $to, $order_by, $order_type] = $this->getQuery($request);

        $item_categories = ItemCategory::where("store_id", $store_id)
            ->where("name", "iLike", "%" . $search . "%")
            ->orderBy($order_by, $order_type)
            ->offset($from)
            ->limit($to - $from)
            ->get();

        return response()->json($item_categories);
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
            return response()->json(
                [
                    "message" => "Item category not found.",
                ],
                404
            );
        }

        $rules = [
            "name" => ["nullable", "string", "max:255"],
            "description" => ["nullable", "string", "max:255"],
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return response()->json(
                [
                    "message" => $this->formatValidationError($validator->errors()),
                ],
                400
            );
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

        if (!$item_category->items->isEmpty()) {
            return response()->json(
                [
                    "message" => "Item category has items.",
                ],
                400
            );
        }

        $item_category->delete();

        return response()->json([
            "message" => "Deleted item category successfully.",
        ]);
    }
}
