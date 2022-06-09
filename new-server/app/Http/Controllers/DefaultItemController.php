<?php

namespace App\Http\Controllers;

use App\Models\DefaultItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DefaultItemController extends Controller
{
    public function getItems(Request $request)
    {
        $search = $request->query("search") ?? "";
        $count = $request->query("count") ?? 10;
        // search by product name, barcode, qrcode, brand, made in, unit, description, source url, category name
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
            ->orderBy("id", "desc")
            ->take($count)
            ->get();

        return response()->json($items);
    }

    public function getItemByBarcode(Request $request, $bar_code)
    {
        $item = DefaultItem::where("bar_code", $bar_code)->first();

        if (!$item) {
            return response()->json(["message" => "Item not found"], 404);
        }

        return response()->json($item);
    }

    public function getItemsByCategory(Request $request, $category_id)
    {
        $search = $request->query("search") ?? "";
        $count = $request->query("count") ?? 10;

        $items = DefaultItem::with("category")
            ->whereHas("category", function ($query) use ($category_id) {
                $query->where("id", $category_id);
            })
            ->where("produc_name", "iLike", "%" . $search . "%")
            ->orWhere("bar_code", "iLike", "%" . $search . "%")
            ->orWhere("qr_code", "iLike", "%" . $search . "%")
            ->orWhere("brand", "iLike", "%" . $search . "%")
            ->orWhere("made_in", "iLike", "%" . $search . "%")
            ->orWhere("unit", "iLike", "%" . $search . "%")
            ->orWhere("description", "iLike", "%" . $search . "%")
            ->orWhere("source_url", "iLike", "%" . $search . "%")
            ->orderBy("id", "desc")
            ->take($count)
            ->get();

        return response()->json($items);
    }
}
