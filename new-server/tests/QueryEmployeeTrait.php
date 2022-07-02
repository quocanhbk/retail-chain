<?php

namespace Tests;

use App\Models\Employee;
use App\Models\PermissionRole;

trait QueryEmployeeTrait
{
    public function getEmployeeWithPermission($store_id, $action_slug)
    {
        $employee = Employee::where("store_id", $store_id)
            ->whereHas("employment.roles.role", function ($query) use ($store_id, $action_slug) {
                $query->whereIn(
                    "id",
                    PermissionRole::where("store_id", $store_id)
                        ->whereHas("permission", function ($query) use ($action_slug) {
                            $query->where("action_slug", $action_slug);
                        })
                        ->pluck("role_id")
                );
            })
            ->first();

        return $employee;
    }

    public function getEmployeeWithoutPermission($store_id, $action_slug)
    {
        $employee = Employee::where("store_id", $store_id)
            ->whereDoesntHave("employment.roles.role", function ($query) use ($store_id, $action_slug) {
                $query->whereIn(
                    "id",
                    PermissionRole::where("store_id", $store_id)
                        ->whereHas("permission", function ($query) use ($action_slug) {
                            $query->where("action_slug", $action_slug);
                        })
                        ->pluck("role_id")
                );
            })
            ->first();

        return $employee;
    }
}
