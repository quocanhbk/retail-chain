<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    /**
     * @OA\Post(
     *   path="/category",
     *   tags={"Category"},
     *   summary="Create a new item category",
     *   operationId="createCategory",
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(ref="#/components/schemas/CreateCategoryInput")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(ref="#/components/schemas/Category")
     *   )
     * )
     */
    public function create(Request $request)
    {
        $store_id = $request->get("store_id");

        $data = $request->all();

        $rules = [
            "name" => ["required", "string", "max:32", Rule::unique("categories")->where("store_id", $store_id)],
            "description" => ["nullable", "string", "max:255"],
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return response()->json(["message" => $this->formatValidationError($validator->errors())], 400);
        }

        $category = Category::create([
            "store_id" => $store_id,
            "name" => $data["name"],
            "description" => $data["description"] ?? null,
        ]);

        return response()->json($category);
    }

    /**
     * @OA\Get(
     *   path="/category",
     *   tags={"Category"},
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
     *       @OA\Items(ref="#/components/schemas/Category")
     *     )
     *   )
     * )
     */
    public function getMany(Request $request)
    {
        $store_id = $request->get("store_id");

        [$search, $from, $to, $order_by, $order_type] = $this->getQuery($request);

        $categories = Category::where("store_id", $store_id)
            ->where(
                fn($query) => $query
                    ->where("name", "iLike", "%{$search}%")
                    ->orWhere("description", "iLike", "%{$search}%")
            )
            ->orderBy($order_by, $order_type)
            ->offset($from)
            ->limit($to - $from)
            ->get();

        return response()->json($categories);
    }

    /**
     * @OA\Get(
     *   path="/category/{id}",
     *   tags={"Category"},
     *   summary="Get an item category",
     *   operationId="getCategory",
     *   @OA\Parameter(name="id", in="path", @OA\Schema(type="integer"), required=true),
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(ref="#/components/schemas/Category")
     *   )
     * )
     */
    public function getOne(Request $request, $id)
    {
        $store_id = $request->get("store_id");

        $category = Category::with("items")
            ->where("store_id", $store_id)
            ->where("id", $id)
            ->first();

        if (!$category) {
            return response()->json(["message" => "Item category not found"], 404);
        }

        return response()->json($category);
    }

    /**
     * @OA\Put(
     *   path="/category/{id}",
     *   tags={"Category"},
     *   summary="Update an item category",
     *   operationId="updateCategory",
     *   @OA\Parameter(name="id", in="path", @OA\Schema(type="integer"), required=true),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(ref="#/components/schemas/UpsertCategoryInput")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(ref="#/components/schemas/Message")
     *   )
     * )
     */
    public function update(Request $request, $id)
    {
        $store_id = $request->get("store_id");

        $data = $request->all();

        $category = Category::where("store_id", $store_id)
            ->where("id", $id)
            ->first();

        if (!$category) {
            return response()->json(["message" => "Item category not found."], 404);
        }

        $rules = [
            "name" => [
                "nullable",
                "string",
                "max:32",
                Rule::unique("categories")
                    ->where("store_id", $store_id)
                    ->ignore($id),
            ],
            "description" => ["nullable", "string", "max:255"],
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return response()->json(["message" => $this->formatValidationError($validator->errors())], 400);
        }

        $category->name = $data["name"] ?? $category->name;
        $category->description = $data["description"] ?? $category->description;

        $category->save();

        return response()->json([
            "message" => "Updated item category successfully.",
        ]);
    }

    /**
     * @OA\Delete(
     *   path="/category/{id}",
     *   tags={"Category"},
     *   summary="Delete an item category",
     *   operationId="deleteCategory",
     *   @OA\Parameter(name="id", in="path", @OA\Schema(type="integer"), required=true),
     *   @OA\Parameter(name="force", in="query", @OA\Schema(type="boolean")),
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(ref="#/components/schemas/Message")
     *   )
     * )
     */
    public function delete(Request $request, $id)
    {
        $store_id = $request->get("store_id");

        $as = $request->get("as");

        $force = $request->query("force") ?? false;

        $category = Category::where("store_id", $store_id)
            ->where("id", $id)
            ->first();

        if (!$category) {
            return response()->json(["message" => "Item category not found."], 404);
        }

        if ("employee" == $as && $force) {
            return response()->json(["message" => "You are not allowed to force delete this item category."], 403);
        }

        $category->when($force, fn($query) => $query->forceDelete(), fn($query) => $query->delete());

        return response()->json([
            "message" => "Deleted item category successfully.",
        ]);
    }

    /**
     * @OA\Get(
     *   path="/category/deleted",
     *   tags={"Category"},
     *   summary="Get deleted item categories",
     *   operationId="getDeletedCategories",
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
     *       @OA\Items(ref="#/components/schemas/Category")
     *     )
     *   )
     * )
     */
    public function getDeleted(Request $request)
    {
        $store_id = $request->get("store_id");

        [$search, $from, $to, $order_by, $order_type] = $this->getQuery($request);

        $categories = Category::onlyTrashed()
            ->where("store_id", $store_id)
            ->where(
                fn($query) => $query
                    ->where("name", "iLike", "%{$search}%")
                    ->orWhere("description", "iLike", "%{$search}%")
            )
            ->orderBy($order_by, $order_type)
            ->offset($from)
            ->limit($to - $from)
            ->get();

        return response()->json($categories);
    }

    /**
     * @OA\Post(
     *   path="/category/{id}/restore",
     *   tags={"Category"},
     *   summary="Restore an item category",
     *   operationId="restoreCategory",
     *   @OA\Parameter(name="id", in="path", @OA\Schema(type="integer"), required=true),
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(ref="#/components/schemas/Message")
     *   )
     * )
     */
    public function restore(Request $request, $id)
    {
        $store_id = $request->get("store_id");

        $category = Category::onlyTrashed()
            ->where("store_id", $store_id)
            ->where("id", $id)
            ->first();

        if (!$category) {
            return response()->json(["message" => "Item category not found."], 404);
        }

        $category->restore();

        return response()->json([
            "message" => "Restored item category successfully.",
        ]);
    }

    /**
     * @OA\Delete(
     *   path="/category/{id}/force",
     *   tags={"Category"},
     *   summary="Force delete an item category",
     *   operationId="forceDeleteCategory",
     *   @OA\Parameter(name="id", in="path", @OA\Schema(type="integer"), required=true),
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(ref="#/components/schemas/Message")
     *   )
     * )
     */
    public function forceDelete(Request $request, $id)
    {
        $store_id = $request->get("store_id");

        $category = Category::withTrashed()
            ->where("store_id", $store_id)
            ->where("id", $id)
            ->first();

        if (!$category) {
            return response()->json(["message" => "Item category not found."], 404);
        }

        $category->forceDelete();

        return response()->json([
            "message" => "Permanently deleted item category successfully.",
        ]);
    }
}
