<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionSeeder extends Seeder
{
    protected $permissions = [
        ["action" => "Create Supplier", "action_slug" => "create-supplier"],
        ["action" => "View Supplier", "action_slug" => "view-supplier"],
        ["action" => "Update Supplier", "action_slug" => "update-supplier"],
        ["action" => "Delete Supplier", "action_slug" => "delete-supplier"],

        ["action" => "Create Category", "action_slug" => "create-category"],
        ["action" => "View Category", "action_slug" => "view-category"],
        ["action" => "Update Category", "action_slug" => "update-category"],
        ["action" => "Delete Category", "action_slug" => "delete-category"],

        ["action" => "Create Shift", "action_slug" => "create-shift"],
        ["action" => "View Shift", "action_slug" => "view-shift"],
        ["action" => "Update Shift", "action_slug" => "update-shift"],
        ["action" => "Delete Shift", "action_slug" => "delete-shift"],

        ["action" => "Create Work Schedule", "action_slug" => "create-work-schedule"],
        ["action" => "View Work Schedule", "action_slug" => "view-work-schedule"],
        ["action" => "Update Work Schedule", "action_slug" => "update-work-schedule"],
        ["action" => "Delete Work Schedule", "action_slug" => "delete-work-schedule"],

        ["action" => "Create Item", "action_slug" => "create-item"],
        ["action" => "View Item", "action_slug" => "view-item"],
        ["action" => "Update Item", "action_slug" => "update-item"],
        ["action" => "Delete Item", "action_slug" => "delete-item"],

        ["action" => "Create Customer", "action_slug" => "create-customer"],
        ["action" => "View Customer", "action_slug" => "view-customer"],
        ["action" => "Update Customer", "action_slug" => "update-customer"],
        ["action" => "Delete Customer", "action_slug" => "delete-customer"],

        ["action" => "Create Purchase Sheet", "action_slug" => "create-purchase-sheet"],
        ["action" => "View Purchase Sheet", "action_slug" => "view-purchase-sheet"],
        ["action" => "Update Purchase Sheet", "action_slug" => "update-purchase-sheet"],
        ["action" => "Delete Purchase Sheet", "action_slug" => "delete-purchase-sheet"],

        ["action" => "Create Quantity Checking Sheet", "action_slug" => "create-quantity-checking-sheet"],
        ["action" => "View Quantity Checking Sheet", "action_slug" => "view-quantity-checking-sheet"],
        ["action" => "Update Quantity Checking Sheet", "action_slug" => "update-quantity-checking-sheet"],
        ["action" => "Delete Quantity Checking Sheet", "action_slug" => "delete-quantity-checking-sheet"],
    ];

    public function run()
    {
        DB::table("permissions")->insert($this->permissions);
    }
}
