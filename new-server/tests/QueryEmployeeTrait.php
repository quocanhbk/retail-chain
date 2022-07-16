<?php

namespace Tests;

use App\Models\Employee;
use App\Models\PermissionRole;

trait QueryEmployeeTrait
{
    public function getEmployeeWithPermission($store_id, $action_slug)
    {
        $employee = Employee::where("store_id", $store_id)
            ->whereHas(
                "employment.roles.role",
                fn($query) => $query->whereIn(
                    "id",
                    PermissionRole::where("store_id", $store_id)
                        ->whereRelation("permission", "action_slug", $action_slug)
                        ->pluck("role_id")
                )
            )
            ->first();

        return $employee;
    }

    public function getEmployeeWithoutPermission($store_id, $action_slug)
    {
        $employee = Employee::where("store_id", $store_id)
            ->whereDoesntHave(
                "employment.roles.role",
                fn($query) => $query->whereIn(
                    "id",
                    PermissionRole::where("store_id", $store_id)
                        ->whereRelation("permission", "action_slug", $action_slug)
                        ->pluck("role_id")
                )
            )
            ->first();

        return $employee;
    }
}
