<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Item;
use App\Models\ItemProperty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class InvoiceController extends Controller
{
    public function create(Request $request) {
        $store_id = Auth::user()->store_id;
        $branch_id = Auth::user()->employment->branch_id;
        $employee_id = Auth::user()->id;

        $data = $request->all();
        $rules = [
            'customer_id' => ['nullable', Rule::exists('customers')->where('store_id', $store_id)],
            'code' => ['nullable', 'string', 'max:255', Rule::unique('invoices')->where('branch_id', $branch_id)],
            'used_point' => ['nullable', 'integer', 'min:0'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required', 'string', 'max:255', Rule::exists('items')->where('store_id', $store_id)],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ];

        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 400);
        }

        $customer = isset($data['customer_id']) ? Customer::find($data['customer_id']) : null;

        // if customer is set, check if point if valid
        if ($customer && $customer->point < $data['used_point']) {
            return response()->json([
                'status' => 'error',
                'errors' => [
                    'used_point' => ['Not enough point']
                ]
            ], 400);
        }

        // if code is not set, generate one
        if (!isset($data['code'])) {
            $invoice_count = Invoice::where('branch_id', $branch_id)->count();
            $code = 'INV' . str_pad($invoice_count + 1, 6, '0', STR_PAD_LEFT);

            // ensure code is unique
            while (Invoice::where('code', $code)->where('branch_id', $branch_id)->exists()) {
                $invoice_count++;
                $code = 'INV' . str_pad($invoice_count + 1, 6, '0', STR_PAD_LEFT);
            }

            $data['code'] = $code;
        }

        // calculate total
        $total = 0;
        foreach ($data['items'] as $item) {
            $total += $item['price'] * $item['quantity'];
        }

        $added_point = floor($total / 100);

        if ($customer) {
            $customer->point += $added_point;

            // if there is used point, deduct it
            if ($data['used_point']) {
                $customer->point -= $data['used_point'];
            }

            $customer->save();
        }

        $invoice = Invoice::create([
            'code' => $data['code'],
            'branch_id' => $branch_id,
            'employee_id' => $employee_id,
            'customer_id' => $data['customer_id'] ?? null,
            'total' => $total,
            'point_used' => $data['used_point'] ?? 0,
            'point_added' => $added_point
        ]);

        $items_data = [];
        foreach ($data['items'] as $item) {
            $data = Item::find($item['id']);
            $property = ItemProperty::where('item_id', $item['id'])->where('branch_id', $branch_id)->first();

            $items_data[] = [
                'invoice_id' => $invoice->id,
                'barcode' => $data->barcode,
                'name' => $data->name,
                'price' => $property->sell_price,
                'quantity' => $item['quantity']
            ];

            // decreate stock
            $property->quantity -= $item['quantity'];
        }

        Item::insert($items_data);

        return response()->json([
            'message' => 'Tạo hóa đơn thành công',
        ]);
    }

    public function getInvoices(Request $request) {
        $branch_id = Auth::user()->employment->branch_id;

        [$search, $from, $to, $order_by, $order_type] = $this->getQuery($request);

        $invoices = Invoice::where('branch_id', $branch_id)
            ->where('code', 'like', '%' . $search . '%')
            ->orderBy($order_by, $order_type)
            ->skip($from)
            ->take($to - $from)
            ->get();

        return response()->json($invoices);
    }

    public function getInvoice(Request $request, $id) {
        $branch_id = Auth::user()->employment->branch_id;

        $invoice = Invoice::where('branch_id', $branch_id)
            ->where('id', $id)
            ->first();

        if (!$invoice) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không tìm thấy hóa đơn'
            ], 404);
        }

        return response()->json($invoice);
    }
}
