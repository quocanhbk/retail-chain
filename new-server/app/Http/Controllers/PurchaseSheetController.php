<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\ItemProperty;
use App\Models\PurchaseSheet;
use App\Models\PurchaseSheetItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PurchaseSheetController extends Controller
{
    // Purchaser create purchase sheet, along with:
    // * Item that is sold before in the store (has data in `items` table)
    // * Item that is never sold before in the store (get from `default_items.barcode_data`)
    // * Item that is never sold before in the store (create manually)
    public function create(Request $request) {
        $store_id = Auth::user()->store_id;
        $employee_id = Auth::user()->id;
        $branch_id = Employee::find($employee_id)->employment->branch_id;
        $data = $request->all();
        $rules = [
            'code' => ['nullable', 'string', 'max:255', Rule::unique('purchase_sheets')->where('branch_id', $branch_id)],
            // Purchase sheet may have unknown supplier
            'supplier_id' => ['required', 'integer', Rule::exists('suppliers', 'id')->where('store_id', $store_id)],
            'discount' => ['nullable', 'numeric'],
            'discount_type' => ['nullable', 'string', 'max:255'],
            'paid_amount' => ['required', 'numeric'],
            'note' => ['nullable', 'string', 'max:255'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.item_id' => ['required', 'integer', Rule::exists('items', 'id')->where('store_id', $store_id)],
            'items.*.quantity' => ['required', 'numeric', 'min:1'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
            'items.*.discount' => ['nullable', 'numeric'],
            'items.*.discount_type' => ['nullable', 'in:cash,percent'],
        ];

        $validator = Validator::make($data, $rules, [
            'code.unique' => 'Mã phiếu này đã được sử dụng',
            'supplier_id.exists' => 'Nhà cung cấp không tồn tại',
            'supplier_id.required' => 'Nhà cung cấp không được để trống',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => 'Thông tin không hợp lệ','error' => $this->formatValidationError($validator->errors())], 400);
        }

        // calculate total for each item
        $items = $data['items'];
        foreach($items as &$item) {
            $final_price = $item['price'];
            $discount = $item['discount'] ?? 0;
            $discount_type = $item['discount_type'] ?? 'percent';
            if ($discount_type == 'percent') {
                $final_price = $final_price * (1 - $discount / 100);
            } else {
                $final_price = $final_price - $discount;
            }
            $item['total'] = $item['quantity'] * $final_price;
        }

        // calculate total of purchase sheet
        $total = 0;
        foreach ($items as $item) {
            $total += $item['total'];
        }
        if (isset($data['discount'])) {
            if ($data['discount_type'] == 'percent') {
                $total = $total - ($total * $data['discount'] / 100);
            } else {
                $total = $total - $data['discount'];
            }
        }

        // if perchase sheet code is not set, generate one
        if (!isset($data['code'])) {
            // calculate purchase sheet code using perchase sheet count
            $purchase_sheet_count = PurchaseSheet::where('branch_id', $branch_id)->count();
            $code = 'PS' . str_pad($purchase_sheet_count + 1, 6, '0', STR_PAD_LEFT);
            // ensure code is unique
            while (PurchaseSheet::where('code', $code)->where('branch_id', $branch_id)->count() > 0) {
                $purchase_sheet_count++;
                $code = 'PS' . str_pad($purchase_sheet_count, 6, '0', STR_PAD_LEFT);
            }
            $data['code'] = $code;
        }


        // create purchase sheet
        $purchase_sheet = PurchaseSheet::create([
            'code' => $code,
            'employee_id' => $employee_id,
            'branch_id' => $branch_id,
            'supplier_id' => $data['supplier_id'],
            'discount' => $data['discount'] ?? 0,
            'discount_type' => $data['discount_type'] ?? 'cash',
            'total' => $total,
            'paid_amount' => $data['paid_amount'],
            'note' => $data['note'] ?? '',
        ]);

        // create purchase sheet items data
        $itemsData = [];
        foreach ($items as $item) {
            $itemsData[] = [
                'purchase_sheet_id' => $purchase_sheet->id,
                'item_id' => $item['item_id'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
                'discount' => $item['discount'] ?? 0,
                'discount_type' => $item['discount_type'] ?? 'cash',
                'total' => $item['total'],
            ];

            // update item base price and quantity
            $item_property = ItemProperty::where('item_id', $item['item_id'])->where('branch_id', $branch_id)->first();
            if (!$item_property) {
                $item_property = ItemProperty::create([
                    'item_id' => $item['item_id'],
                    'branch_id' => $branch_id,
                    'base_price' => $item['price'],
                    'last_purchase_price' => $item['price'],
                    'quantity' => $item['quantity'],
                ]);
            } else {

                $new_base_price = ($item_property->base_price * $item_property->quantity + $item['total'])/ ($item_property->quantity + $item['quantity']);
                $item_property->update([
                    'base_price' => $new_base_price,
                    'quantity' => $item_property->quantity + $item['quantity'],
                ]);
            }
        }
        PurchaseSheetItem::insert($itemsData);

        return response()->json([
            'message' => 'Purchase sheet created successfully',
        ]);
    }

    public function getPurchaseSheets(Request $request) {
        $branch_id = Auth::user()->employment->branch_id;

        [$search, $from, $to, $order_by, $order_type] = $this->getQuery($request);

        $purchase_sheets = PurchaseSheet::with(['supplier', 'employee'])->where('branch_id', $branch_id)
            ->where('code', 'like', '%' . $search . '%')
            ->orWhereHas('supplier', function($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%');
            })
            ->orWherehas('employee', function($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%');
            })
            ->orderBy($order_by, $order_type)
            ->offset($from)
            ->limit($to - $from)
            ->get();

        return response()->json($purchase_sheets);
    }

    public function getPurchaseSheet(Request $request, $id) {
        $branch_id = Auth::user()->employment->branch_id;
        $purchase_sheet = PurchaseSheet::with(['purchaseSheetItems.item', 'employee', 'branch', 'supplier'])->where('branch_id', $branch_id)->find($id);
        return response()->json($purchase_sheet);
    }

    public function update(Request $request, $id) {
        $store_id = Auth::user()->store_id;
        $branch_id = Auth::user()->employment->branch_id;
        $data = $request->all();
        $data['id'] = $id;
        $rules = [
            'id' => ['required', 'integer', Rule::exists('purchase_sheets', 'id')->where('branch_id', $branch_id)->where('employee_id', Auth::user()->id)],
            'discount' => ['nullable', 'numeric'],
            'discount_type' => ['nullable', 'in:cash,percent', 'max:255'],
            'paid_amount' => ['required', 'numeric'],
            'note' => ['nullable', 'string', 'max:255'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.item_id' => ['required', 'integer', Rule::exists('items', 'id')->where('store_id', $store_id)],
            'items.*.quantity' => ['required', 'numeric', 'min:1'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
            'items.*.discount' => ['nullable', 'numeric'],
            'items.*.discount_type' => ['nullable', 'in:cash,percent'],
        ];

        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $purchase_sheet = PurchaseSheet::find($id);

        $total = 0;
        // calculate total of each item
        $items = $data['items'];
        foreach ($items as &$item) {
            $final_price = $item['price'];
            if (isset($item['discount'])) {
                if ($item['discount_type'] == 'percent') {
                    $final_price = $final_price - ($final_price * $item['discount'] / 100);
                } else {
                    $final_price = $final_price - $item['discount'];
                }
            }
            $item['total'] = $item['quantity'] * $final_price;
        }

        // calculate total of purchase sheet
        foreach ($items as $item) {
            $total += $item['total'];
        }

        $discount = $data['discount'] ?? $purchase_sheet->discount;
        $discount_type = $data['discount_type'] ?? $purchase_sheet->discount_type;
        if (isset($discount)) {
            if ($discount_type == 'percent') {
                $total = $total - ($total * $discount / 100);
            } else {
                $total = $total - $discount;
            }
        }


        // update purchase sheet
        $purchase_sheet->update([
            'discount' => $data['discount'] ?? $purchase_sheet->discount,
            'discount_type' => $data['discount_type'] ?? $purchase_sheet->discount_type,
            'paid_amount' => $data['paid_amount'] ?? $purchase_sheet->paid_amount,
            'total' => $total,
            'note' => $data['note'] ?? $purchase_sheet->note,
        ]);


        // check if this is the lastest purchase sheet
        $is_latest = PurchaseSheet::where('branch_id', $branch_id)->where('id', '>', $id)->count() == 0;

        // update purchase sheet items
        $itemsData = [];
        foreach ($items as $item) {
            $itemsData[] = [
                'purchase_sheet_id' => $purchase_sheet->id,
                'item_id' => $item['item_id'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
                'discount' => $item['discount'] ?? 0,
                'discount_type' => $item['discount_type'] ?? 'cash',
                'total' => $item['total'],
            ];

            $item_property = ItemProperty::where('item_id', $item['item_id'])->where('branch_id', $branch_id)->first();
            // find previous purchase sheet item
            $purchase_sheet_item = PurchaseSheetItem::where('purchase_sheet_id', $purchase_sheet->id)->where('item_id', $item['item_id'])->first();
            // if found, calculate base price without that item
            if ($purchase_sheet_item) {
                $previous_base_price = ($item_property->base_price * $item_property->quantity - $purchase_sheet_item->total)/ ($item_property->quantity - $purchase_sheet_item->quantity);
                $previous_quantity = $item_property->quantity - $purchase_sheet_item->quantity;
                $item_property->update([
                    'base_price' => $previous_base_price,
                    'last_purchase_price' => $is_latest ? $item['price'] : $item_property->last_purchase_price,
                    'quantity' => $previous_quantity,
                ]);
            }
            // calculate new base price and quantity
            $new_base_price = ($item_property->base_price * $item_property->quantity + $item['total'])/ ($item_property->quantity + $item['quantity']);
            $item_property->update([
                'base_price' => $new_base_price,
                'last_purchase_price' => $is_latest ? $item['price'] : $item_property->last_purchase_price,
                'quantity' => $item_property->quantity + $item['quantity'],
            ]);
        }

        // delete old purchase sheet items
        $purchase_sheet->purchaseSheetItems()->delete();

        // update purchase sheet items data to database
        $items = PurchaseSheetItem::insert($itemsData);

        $purchase_sheet['items'] = $items;

        return response()->json($purchase_sheet);
    }

    public function updateNote(Request $request, $id) {
        $branch_id = Auth::user()->employment->branch_id;
        $data = $request->all();
        $data['id'] = $id;
        $rules = [
            'id' => ['required', 'integer', Rule::exists('purchase_sheets', 'id')->where('branch_id', $branch_id)->where('employee_id', Auth::user()->id)],
            'note' => ['required', 'string', 'max:255'],
        ];

        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $purchase_sheet = PurchaseSheet::find($id);
        $purchase_sheet->update([
            'note' => $data['note'] ?? $purchase_sheet->note,
        ]);

        return response()->json($purchase_sheet);
    }

    public function delete(Request $request, $id) {
        $branch_id = Auth::user()->employment->branch_id;

        $purchase_sheet = PurchaseSheet::where('branch_id', $branch_id)->where('employee_id', Auth::user()->id)->find($id);
        if (!$purchase_sheet) {
            return response()->json(['error' => 'Purchase sheet not found'], 404);
        }

        // recalculate base price and quantity for each purchase sheet item
        foreach ($purchase_sheet->purchaseSheetItems as $purchase_sheet_item) {
            $item_property = ItemProperty::where('item_id', $purchase_sheet_item->item_id)->where('branch_id', $branch_id)->first();
            $previous_quantity = $item_property->quantity - $purchase_sheet_item->quantity;
            $previous_base_price = ($item_property->base_price * $item_property->quantity - $purchase_sheet_item->total) / $previous_quantity;
            $item_property->update([
                'base_price' => $previous_base_price,
                'quantity' => $previous_quantity,
            ]);
        }

        // delete all purchase sheet items
        PurchaseSheetItem::where('purchase_sheet_id', $id)->delete();

        // delete purchase sheet
        $purchase_sheet->delete();

        return response()->json(['success' => 'Purchase sheet deleted']);
    }


}
