<?php

namespace App\Http\Controllers;

use App\Models\InvoiceItem;
use App\Models\ItemProperty;
use App\Models\RefundSheet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class RefundController extends Controller
{
    public function create(Request $request)
    {
        $branch_id = Auth::user()->employment->branch_id;
        $employee_id = Auth::user()->id;

        $data = $request->all();
        $rules = [
            "invoice_id" => ["required", Rule::exists("invoices", "id")->where("branch_id", $branch_id)],
            "code" => ["nullable", "string", "max:255"],
            "reason" => ["nullable", "string", "max:255"],

            "items" => ["required", "array", "min:1"],
            "items.*.return_item_id" => [
                "required",
                "integer",
                Rule::exists("return_items", "id")->where("branch_id", $branch_id),
            ],
            "items.*.quantity" => ["required", "integer", "min:1"],
            "items.*.resellable" => ["nullable", "boolean"],
        ];

        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return response()->json(
                [
                    "status" => "error",
                    "errors" => $validator->errors(),
                ],
                400
            );
        }

        // if code is not given, generate one
        if (!isset($data["code"])) {
            $refund_sheets_count = RefundSheet::where("branch_id", $branch_id)->count();
            $code = "REF" . str_pad($refund_sheets_count + 1, 6, "0", STR_PAD_LEFT);

            // ensure code is unique
            while (
                RefundSheet::where("code", $code)
                    ->where("branch_id", $branch_id)
                    ->exists()
            ) {
                ++$refund_sheets_count;
                $code = "REF" . str_pad($refund_sheets_count + 1, 6, "0", STR_PAD_LEFT);
            }

            $data["code"] = $code;
        }

        // calculate total
        $total = 0;
        foreach ($data["items"] as $item) {
            $invoice_item = InvoiceItem::find($item["return_item_id"]);
            $total += $invoice_item->price * $item["quantity"];

            // check if item is resellable, increase quantity if it is
            if (isset($item["resellable"]) && $item["resellable"]) {
                $item_property = ItemProperty::where("item_id", $invoice_item->item_id)
                    ->where("branch_id", $branch_id)
                    ->first();
                $item_property->quantity += $item["quantity"];
            }
        }

        $refund_sheet = RefundSheet::create([
            "code" => $data["code"],
            "branch_id" => $branch_id,
            "employee_id" => $employee_id,
            "invoice_id" => $data["invoice_id"],
            "total" => $total,
            "reason" => $data["reason"] ?? null,
        ]);

        $invoice_items_data = [];
        foreach ($data["items"] as $item) {
            $invoice_items_data[] = [
                "refund_sheet_id" => $refund_sheet->id,
                "return_item_id" => $item["return_item_id"],
                "quantity" => $item["quantity"],
                "resellable" => isset($item["resellable"]) ? $item["resellable"] : false,
            ];
        }

        InvoiceItem::insert($invoice_items_data);

        return response()->json([
            "message" => "Refund sheet created successfully",
        ]);
    }

    public function getRefundSheets(Request $request)
    {
        $branch_id = Auth::user()->employment->branch_id;

        [$search, $from, $to, $order_by, $order_type] = $this->getQuery($request);

        $refund_sheets = RefundSheet::where("branch_id", $branch_id)
            ->where("code", "iLike", "%" . $search . "%")
            ->orderBy($order_by, $order_type)
            ->skip($from)
            ->take($to - $from)
            ->get();

        return response()->json($refund_sheets);
    }

    public function getRefundSheet(Request $request, $id)
    {
        $branch_id = Auth::user()->employment->branch_id;

        $refund_sheet = RefundSheet::with(["branch", "employee", "invoice", "refundItems"])
            ->where("branch_id", $branch_id)
            ->where("id", $id)
            ->first();

        if (!$refund_sheet) {
            return response()->json(
                [
                    "message" => "Refund sheet not found",
                ],
                404
            );
        }

        return response()->json($refund_sheet);
    }
}
