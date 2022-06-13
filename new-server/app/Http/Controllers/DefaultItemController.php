<?php

namespace App\Http\Controllers;

use App\Models\DefaultItem;
use Illuminate\Http\Request;

class DefaultItemController extends Controller
{
    /**
     * @OA\Get(
     *   path="/default-item",
     *   summary="Get all default items",
     *   operationId="getDefaultItems",
     *   tags={"DefaultItem"},
     *   @OA\Parameter(name="search", in="query", @OA\Schema(type="string")),
     *   @OA\Parameter(name="order_by", in="query", @OA\Schema(type="string")),
     *   @OA\Parameter(name="order_type", in="query", @OA\Schema(type="string", enum={"asc", "desc"})),
     *   @OA\Parameter(name="from", in="query", @OA\Schema(type="integer")),
     *   @OA\Parameter(name="to", in="query", @OA\Schema(type="integer")),
     *   @OA\Response(
     *     response=200,
     *     description="A list with default items",
     *     @OA\JsonContent(
     *       type="array",
     *       @OA\Items(ref="#/components/schemas/DefaultItemWithCategory")
     *     )
     *   ),
     * )
     */
    public function getItems(Request $request)
    {
        [$search, $from, $to] = $this->getQuery($request);

        $items = DefaultItem::with("category")
            ->where("product_name", "iLike", "%" . $search . "%")
            ->orWhere("bar_code", "iLike", "%" . $search . "%")
            ->orWhere("qr_code", "iLike", "%" . $search . "%")
            ->orWhere("brand", "iLike", "%" . $search . "%")
            ->orWhere("made_in", "iLike", "%" . $search . "%")
            ->orWhere("unit", "iLike", "%" . $search . "%")
            ->orWhere("description", "iLike", "%" . $search . "%")
            ->orWhere("source_url", "iLike", "%" . $search . "%")
            ->orWhereHas("category", function ($query) use ($search) {
                $query->where("name", "iLike", "%" . $search . "%");
            })
            ->skip($from)
            ->take($to - $from)
            ->get();

        return response()->json($items);
    }

    /**
     * @OA\Get(
     *   path="/default-item/barcode/{barcode}",
     *   summary="Get default item by barcode",
     *   operationId="getDefaultItemByBarcode",
     *   tags={"DefaultItem"},
     *   @OA\Parameter(name="barcode", in="path", @OA\Schema(type="string")),
     *   @OA\Response(
     *     response=200,
     *     description="A default item",
     *     @OA\JsonContent(ref="#/components/schemas/DefaultItemWithCategory")
     *   ),
     * )
     */
    public function getItemByBarcode($bar_code)
    {
        $item = DefaultItem::with("category")
            ->where("bar_code", $bar_code)
            ->first();

        if (!$item) {
            return response()->json(["message" => "Item not found"], 404);
        }

        return response()->json($item);
    }

    /**
     * @OA\Get(
     *   path="/default-item/category/{category_id}",
     *   summary="Get default items by category",
     *   operationId="getDefaultItemsByCategory",
     *   tags={"DefaultItem"},
     *   @OA\Parameter(name="category_id", in="path", @OA\Schema(type="integer")),
     *   @OA\Response(
     *     response=200,
     *     description="A list with default items",
     *     @OA\JsonContent(
     *       type="array",
     *       @OA\Items(ref="#/components/schemas/DefaultItemWithCategory")
     *     )
     *   ),
     * )
     */
    public function getItemsByCategory(Request $request, $category_id)
    {
        [$search, $from, $to] = $this->getQuery($request);

        $items = DefaultItem::with("category")
            ->where("category_id", $category_id)
            ->where(function ($query) use ($search) {
                $query
                    ->where("product_name", "iLike", "%" . $search . "%")
                    ->orWhere("bar_code", "iLike", "%" . $search . "%")
                    ->orWhere("qr_code", "iLike", "%" . $search . "%")
                    ->orWhere("brand", "iLike", "%" . $search . "%")
                    ->orWhere("made_in", "iLike", "%" . $search . "%")
                    ->orWhere("unit", "iLike", "%" . $search . "%")
                    ->orWhere("description", "iLike", "%" . $search . "%")
                    ->orWhere("source_url", "iLike", "%" . $search . "%");
            })
            ->skip($from)
            ->take($to - $from)
            ->get();

        return response()->json($items);
    }
}
