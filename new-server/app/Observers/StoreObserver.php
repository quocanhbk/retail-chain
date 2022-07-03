<?php

namespace App\Observers;

use App\Models\Permission;
use App\Models\PermissionRole;
use App\Models\Store;
use Exception;
use Illuminate\Support\Str;

class StoreObserver
{
    /**
     * Handle the store "created" event.
     *
     * @param  \App\Models\Store  $store
     * @return void
     */
    public function created(Store $store)
    {
        $store
            ->categories()
            ->createMany([
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
            ]);

        $store->roles()->createMany([["name" => "Purchase"], ["name" => "Sale"], ["name" => "Manage"]]);

        // default permission roles
        $this->createPermissionRoles($store, "create-supplier", ["Purchase"]);
        $this->createPermissionRoles($store, "update-supplier", ["Purchase"]);
        $this->createPermissionRoles($store, "delete-supplier", ["Purchase"]);
        $this->createPermissionRoles($store, "view-supplier", ["Purchase", "Manage"]);

        $this->createPermissionRoles($store, "create-category", ["Purchase", "Sale"]);
        $this->createPermissionRoles($store, "update-category", ["Purchase", "Sale"]);
        $this->createPermissionRoles($store, "delete-category", ["Purchase", "Sale"]);
        $this->createPermissionRoles($store, "view-category", ["Purchase", "Sale", "Manage"]);

        $this->createPermissionRoles($store, "create-shift", ["Manage"]);
        $this->createPermissionRoles($store, "update-shift", ["Manage"]);
        $this->createPermissionRoles($store, "delete-shift", ["Manage"]);
        $this->createPermissionRoles($store, "view-shift", ["Purchase", "Sale", "Manage"]);

        $this->createPermissionRoles($store, "create-work-schedule", ["Manage"]);
        $this->createPermissionRoles($store, "update-work-schedule", ["Manage"]);
        $this->createPermissionRoles($store, "delete-work-schedule", ["Manage"]);
        $this->createPermissionRoles($store, "view-work-schedule", ["Purchase", "Sale", "Manage"]);

        $this->createPermissionRoles($store, "create-customer", ["Sale", "Manage"]);
        $this->createPermissionRoles($store, "update-customer", ["Sale", "Manage"]);
        $this->createPermissionRoles($store, "delete-customer", ["Sale", "Manage"]);
        $this->createPermissionRoles($store, "view-customer", ["Sale", "Manage"]);

        $this->createPermissionRoles($store, "create-item", ["Purchase", "Manage"]);
        $this->createPermissionRoles($store, "update-item", ["Purchase", "Manage"]);
        $this->createPermissionRoles($store, "delete-item", ["Manage"]);
        $this->createPermissionRoles($store, "view-item", ["Purchase", "Sale", "Manage"]);
    }

    private function createPermissionRoles(Store $store, $action_slug, $role_names)
    {
        $permission = Permission::where("action_slug", $action_slug)->first();

        if (!$permission) {
            throw new Exception("Permission not found");
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
}
