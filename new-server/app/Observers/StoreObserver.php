<?php

namespace App\Observers;

use App\Models\Permission;
use App\Models\Store;
use Exception;

class StoreObserver
{
    /**
     * Handle the store "created" event.
     *
     * @return void
     */
    public function created(Store $store)
    {
        $store->categories()->createMany($this->default_categories);

        $store->roles()->createMany($this->default_roles);

        foreach ($this->default_permission_roles as $permission_role) {
            $this->createPermissionRoles($store, $permission_role["slug"], $permission_role["roles"]);
        }
    }

    private function createPermissionRoles(Store $store, $action_slug, $role_names)
    {
        $permission = Permission::where("action_slug", $action_slug)->first();

        if (!$permission) {
            throw new Exception("Permission not found: " . $action_slug);
        }

        $input = [];
        foreach ($role_names as $name) {
            $input[] = [
                "store_id" => $store->id,
                "role_id" => $store
                    ->roles()
                    ->where("name", $name)
                    ->first()->id,
            ];
        }

        $permission->roles()->createMany($input);
    }

    private $default_roles = [["name" => "Purchase"], ["name" => "Sale"], ["name" => "Manage"]];

    private $default_permission_roles = [
        ["slug" => "create-supplier", "roles" => ["Purchase"]],
        ["slug" => "update-supplier", "roles" => ["Purchase"]],
        ["slug" => "delete-supplier", "roles" => ["Manage"]],
        ["slug" => "view-supplier", "roles" => ["Purchase", "Manage"]],

        ["slug" => "create-category", "roles" => ["Purchase", "Sale"]],
        ["slug" => "update-category", "roles" => ["Purchase", "Sale"]],
        ["slug" => "delete-category", "roles" => ["Manage"]],
        ["slug" => "view-category", "roles" => ["Purchase", "Sale", "Manage"]],

        ["slug" => "create-shift", "roles" => ["Manage"]],
        ["slug" => "update-shift", "roles" => ["Manage"]],
        ["slug" => "delete-shift", "roles" => ["Manage"]],
        ["slug" => "view-shift", "roles" => ["Purchase", "Sale", "Manage"]],

        ["slug" => "create-work-schedule", "roles" => ["Manage"]],
        ["slug" => "update-work-schedule", "roles" => ["Manage"]],
        ["slug" => "delete-work-schedule", "roles" => ["Manage"]],
        ["slug" => "view-work-schedule", "roles" => ["Purchase", "Sale", "Manage"]],

        ["slug" => "create-customer", "roles" => ["Sale"]],
        ["slug" => "update-customer", "roles" => ["Sale"]],
        ["slug" => "delete-customer", "roles" => ["Manage"]],
        ["slug" => "view-customer", "roles" => ["Sale", "Manage"]],

        ["slug" => "create-item", "roles" => ["Purchase"]],
        ["slug" => "update-item", "roles" => ["Purchase"]],
        ["slug" => "delete-item", "roles" => ["Manage"]],
        ["slug" => "view-item", "roles" => ["Purchase", "Sale", "Manage"]],

        ["slug" => "create-purchase-sheet", "roles" => ["Purchase"]],
        ["slug" => "delete-purchase-sheet", "roles" => ["Manage"]],
        ["slug" => "update-purchase-sheet", "roles" => ["Purchase"]],
        ["slug" => "view-purchase-sheet", "roles" => ["Purchase", "Manage", "Sale"]],

        ["slug" => "create-quantity-checking-sheet", "roles" => ["Purchase"]],
        ["slug" => "delete-quantity-checking-sheet", "roles" => ["Manage"]],
        ["slug" => "update-quantity-checking-sheet", "roles" => ["Purchase"]],
        ["slug" => "view-quantity-checking-sheet", "roles" => ["Purchase", "Manage", "Sale"]],
    ];

    private $default_categories = [
        ["name" => "ĐỒ UỐNG CÁC LOẠI"],
        ["name" => "SỮA UỐNG CÁC LOẠI"],
        ["name" => "BÁNH KẸO CÁC LOẠI"],
        ["name" => "MÌ, CHÁO, PHỞ, BÚN"],
        ["name" => "DẦU ĂN, GIA VỊ"],
        ["name" => "GẠO, BỘT, ĐỒ KHÔ"],
        ["name" => "ĐỒ MÁT, ĐÔNG LẠNH"],
        ["name" => "TÃ, ĐỒ CHO BÉ"],
        ["name" => "CHĂM SÓC CÁ NHÂN"],
        ["name" => "VỆ SINH NHÀ CỬA"],
        ["name" => "ĐỒ DÙNG GIA ĐÌNH"],
        ["name" => "VĂN PHÒNG PHẨM"],
        ["name" => "THUỐC VÀ THỰC PHẨM CHỨC NĂNG"],
    ];
}
