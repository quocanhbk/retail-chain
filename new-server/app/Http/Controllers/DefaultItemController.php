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
     *   @OA\Parameter(name="category_id", in="query", @OA\Schema(type="integer")),
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

        $category_id = $request->query("category_id") ?? null;

        $items = DefaultItem::with("category")
            ->when($category_id, function ($query) use ($category_id) {
                return $query->where("category_id", $category_id);
            })
            ->where(function ($query) use ($search) {
                $query
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
                    });
            })
            ->skip($from)
            ->take($to - $from)
            ->get();

        return response()->json($items);
    }

    /**
     * @OA\Get(
     *   path="/default-item/one",
     *   summary="Get default item by barcode",
     *   operationId="getDefaultItemByBarcode",
     *   tags={"DefaultItem"},
     *   @OA\Parameter(name="barcode", in="query", @OA\Schema(type="string")),
     *   @OA\Parameter(name="id", in="query", @OA\Schema(type="integer")),
     *   @OA\Response(
     *     response=200,
     *     description="A default item",
     *     @OA\JsonContent(ref="#/components/schemas/DefaultItemWithCategory")
     *   ),
     * )
     */
    public function getItem(Request $request)
    {
        $id = $request->query("id");
        $barcode = $request->query("barcode");

        if (!$id && !$barcode) {
            return response()->json(["message" => "Missing barcode or id"], 400);
        }

        $item = DefaultItem::with("category")
            ->when(isset($id), function ($query) use ($id) {
                $query->where("id", $id);
            })
            ->when(isset($barcode) && !isset($id), function ($query) use ($barcode) {
                $query->where("bar_code", $barcode);
            })
            ->first();

        if (!$item) {
            return response()->json(["message" => "Item not found"], 404);
        }

        return response()->json($item);
    }
}
