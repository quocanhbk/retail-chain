<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\ItemPriceHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ItemController extends Controller
{
    public function create(Request $request) {
        $store_id = Auth::guard('stores')->user()->id;
        $data = $request->all();
        $rules = [
            'category_id' => ['required', 'integer', Rule::exists('item_categories', 'id')->where('store_id', $store_id)],
            'code' => ['nullable', 'string', 'max:255', Rule::unique('items')->where('store_id', $store_id)],
            'barcode' => ['required', 'string', 'max:255', Rule::unique('items')->where('store_id', $store_id)],
            'name' => ['required', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
            'price' => ['required', 'numeric', 'integer', 'min:0'],
        ];

        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 400);
        }

        $path = $request->hasFile('image')
            ? $request->file('image')
                ->storeAs('items', $store_id . $data['barcode'] . "." . $request->file('image')->getClientOriginalExtension())
            : null;

        $count = Item::where('store_id', $store_id)->count() + 1;

        $code = $data['code'] ?? "SP" . str_pad($count, 6, '0', STR_PAD_LEFT);
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
            'image' => $path,
            'price' => $data['price'],
            'store_id' => $store_id,
        ]);

        // insert into price history
        ItemPriceHistory::create([
            'item_id' => $item->id,
            'price' => $data['price'],
            'start_date' => date('Y-m-d'),
            'end_date' => null,
        ]);

        return response()->json($item);
    }

    public function getItems(Request $request) {
        $store_id = $request->get('store_id');
        $items = Item::where('store_id', $store_id)->get();
        return response()->json($items);
    }

    public function getItem(Request $request, $item_id) {
        $store_id = $request->get('store_id');
        $item = Item::where('id', $item_id)->where('store_id', $store_id)->first();
        return response()->json($item);
    }

    public function getItemsByBarCode(Request $request, $barcode) {
        $store_id = $request->get('store_id');
        $item = Item::where('store_id', $store_id)->where('barcode', $barcode)->first();
        return response()->json($item);
    }

    public function search(Request $request, $search) {
        $store_id = $request->get('store_id');
        // perform search on code, barcode, name, category
        $items = Item::where('store_id', $store_id)
            ->where('code', 'like', '%' . $search . '%')
            ->orWhere('barcode', 'like', '%' . $search . '%')
            ->orWhere('name', 'like', '%' . $search . '%')
            ->orWhereHas('category', function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%');
            })
            ->get();
        return response()->json($items);
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

    public function update(Request $request, $item_id) {
        $store_id = Auth::guard('stores')->user()->id;
        $data = $request->all();
        $data['id'] = $item_id;
        $rules = [
            'id' => ['required', 'numeric', Rule::exists('items')->where('store_id', $store_id)],
            'code' => ['nullable', 'string', 'max:255', Rule::unique('items')->where('store_id', $store_id)->ignore($item_id)],
            'barcode' => ['nullable', 'string', 'max:255', Rule::unique('items')->where('store_id', $store_id)->ignore($item_id)],
            'name' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
            'price' => ['nullable', 'numeric'],
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
        $old_price = $item->price;
        $item->code = $data['code'] ?? $item->code;
        $item->barcode = $data['barcode'] ?? $item->barcode;
        $item->name = $data['name'] ?? $item->name;
        $item->price = $data['price'] ?? $item->price;
        $item->category_id = $data['category_id'] ?? $item->category_id;
        // if price changes, update price history
        if ($old_price != $item->price) {
            $price_history = ItemPriceHistory::where('item_id', $item_id)->where('end_date', null)->first();
            $price_history->end_date = date('Y-m-d');
            $price_history->save();

            ItemPriceHistory::create([
                'item_id' => $item_id,
                'price' => $data['price'],
                'start_date' => date('Y-m-d'),
                'end_date' => null,
            ]);
        }

        // if image changes, update image
        if ($request->hasFile('image')) {
            $path = $request->file('image')
                ->storeAs('items', $store_id . $data['barcode'] . "." . $request->file('image')->getClientOriginalExtension());
            $item->image = $path;
        }

        $item->save();
        return response()->json($item);
    }

    public function delete(Request $request, $item_id) {
        $store_id = Auth::guard('stores')->user()->id;
        $item = Item::where('id', $item_id)->where('store_id', $store_id)->first();
        if (!$item) {
            return response()->json([
                'message' => 'Item not found'
            ], 404);
        }
        // update price history
        $price_history = ItemPriceHistory::where('item_id', $item_id)->where('end_date', null)->first();
        $price_history->end_date = date('Y-m-d');
        $price_history->save();

        $item->delete();
        return response()->json($item);
    }
}
