<?php

namespace App\Http\Controllers;

use App\Models\DefaultItem;
use App\Models\Item;
use App\Models\ItemPriceHistory;
use App\Models\ItemProperty;
use App\Models\ItemQuantity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ItemController extends Controller
{
    // Purchaser will create new item, in the case he didn't find it from default items
    // * Create item for the store, initial quantity, base price, sell price for the purchaser's branch
    public function create(Request $request) {
        $store_id = Auth::user()->store_id;
        $branch_id = Auth::user()->employment->branch_id;
        $data = $request->all();
        $rules = [
            'category_id' => ['required', 'integer', Rule::exists('item_categories', 'id')->where('store_id', $store_id)],
            'code' => ['nullable', 'string', 'max:255', Rule::unique('items')->where('store_id', $store_id)],
            'barcode' => ['required', 'string', 'max:255', Rule::unique('items')->where('store_id', $store_id)],
            'name' => ['required', 'string', 'max:255'],
            'quantity' => ['nullable', 'numberic', 'min:0'],
            'base_price' => ['nullable', 'numeric', 'min:0'],
            'sell_price' => ['nullable', 'numeric', 'min:0'],
        ];

        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 400);
        }

        $count = Item::where('store_id', $store_id)->count();

        $code = $data['code'] ?? "SP" . str_pad($count + 1, 6, '0', STR_PAD_LEFT);
        // ensure code is unique
        while (Item::where('code', $code)->where('store_id', $store_id)->exists()) {
            $count++;
            $code = "SP" . str_pad($count, 6, '0', STR_PAD_LEFT);
        }

        $item = Item::create([
            'category_id' => $data['category_id'],
            'code' => $code,
            'barcode' => $data['barcode'],
            'name' => $data['name'],
            'price' => $data['price'],
            'store_id' => $store_id,
        ]);

        ItemProperty::create([
            'item_id' => $item->id,
            'branch_id' => $branch_id,
            'quantity' => $data['quantity'] ?? 0,
            'sell_price' => $data['sell_price'] ?? 0,
            'base_price' => $data['base_price'] ?? 0,
        ]);

        return response()->json($item);
    }

    public function updateImage(Request $request) {
        $store_id = Auth::user()->store_id;
        $rules = [
            'id' => ['required', 'integer', Rule::exists('items', 'id')->where('store_id', $store_id)],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 400);
        }

        $item = Item::find($request->id);
        $path = $request->file('image')
            ->storeAs('items', $store_id . $item->barcode . "." . $request->file('image')->getClientOriginalExtension());
        $item->image = $path;
        $item->save();

        return response()->json([
            'message' => 'Image updated'
        ]);
    }

    public function getItems(Request $request) {
        $store_id = $request->get('store_id');
        $search = $request->query('search') ?? '';
        $count = $request->query('count') ?? 10;

        // search by code, barcode, name, category
        $items = Item::with('category')->where('store_id', $store_id)
            ->where('code', 'like', '%' . $search . '%')
            ->orWhere('barcode', 'like', '%' . $search . '%')
            ->orWhere('name', 'like', '%' . $search . '%')
            ->orWhereHas('category', function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%');
            })->take($count)->get();

        return response()->json($items);
    }

    // * Only used for item that is already added to the database
    public function getItem(Request $request, $item_id) {
        $store_id = $request->get('store_id');
        $item = Item::where('id', $item_id)->where('store_id', $store_id)->first();
        return response()->json($item);
    }

    // * Used for both item from the database and item from the default database
    public function getItemsByBarCode(Request $request, $barcode) {
        $store_id = $request->get('store_id');
        $item = Item::where('store_id', $store_id)->where('barcode', $barcode)->first();

        if ($item) {
            return response()->json($item);
        }

        // else, search in the default items
        $item = DefaultItem::where('bar_code', $barcode)->first();

        // if ($item) {
        //     return response()->json([
        //         'store_id' => $store_id,
        //         'barcode' => $item->bar_code,
        //         'name' => $item->product_name,
        //         'image'
        //     ])
        // }

    }

    public function getPriceHistory(Request $request, $item_id) {
        $store_id = $request->get('store_id');
        // make sure store own item
        $item = Item::where('id', $item_id)->where('store_id', $store_id)->first();
        if (!$item) {
            return response()->json([
                'message' => 'Item not found'
            ], 404);
        }

        $price_history = ItemPriceHistory::where('item_id', $item_id)->get();
        return response()->json($price_history);
    }

    // maybe only for manager ?
    public function update(Request $request, $item_id) {
        $store_id = Auth::guard('stores')->user()->id;
        $data = $request->all();
        $data['id'] = $item_id;
        $rules = [
            'id' => ['required', 'numeric', Rule::exists('items')->where('store_id', $store_id)],
            'code' => ['nullable', 'string', 'max:255', Rule::unique('items')->where('store_id', $store_id)->ignore($item_id)],
            'barcode' => ['nullable', 'string', 'max:255', Rule::unique('items')->where('store_id', $store_id)->ignore($item_id)],
            'name' => ['nullable', 'string', 'max:255'],
            'category_id' => ['nullable', 'integer', Rule::exists('item_categories', 'id')->where('store_id', $store_id)],
        ];

        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 400);
        }

        $item = Item::find($item_id);
        $item->code = $data['code'] ?? $item->code;
        $item->barcode = $data['barcode'] ?? $item->barcode;
        $item->name = $data['name'] ?? $item->name;
        $item->category_id = $data['category_id'] ?? $item->category_id;
        $item->save();

        return response()->json($item);
    }
}
