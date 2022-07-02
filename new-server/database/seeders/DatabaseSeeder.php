<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Employment;
use App\Models\EmploymentRole;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\ItemProperty;
use App\Models\Permission;
use App\Models\PermissionRole;
use App\Models\Role;
use App\Models\Shift;
use App\Models\Store;
use App\Models\Supplier;
use App\Models\WorkSchedule;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        Store::truncate();
        Employee::truncate();
        Branch::truncate();
        Item::truncate();
        ItemCategory::truncate();
        ItemProperty::truncate();
        Supplier::truncate();
        Customer::truncate();
        WorkSchedule::truncate();
        Shift::truncate();
        Employment::truncate();
        EmploymentRole::truncate();
        Permission::truncate();
        PermissionRole::truncate();
        Role::truncate();

        Permission::create(["action" => "Create Supplier", "action_slug" => "create-supplier"]);
        Permission::create(["action" => "View Supplier", "action_slug" => "view-supplier"]);
        Permission::create(["action" => "Update Supplier", "action_slug" => "update-supplier"]);
        Permission::create(["action" => "Delete Supplier", "action_slug" => "delete-supplier"]);

        Permission::create(["action" => "Create Category", "action_slug" => "create-category"]);
        Permission::create(["action" => "View Category", "action_slug" => "view-category"]);
        Permission::create(["action" => "Update Category", "action_slug" => "update-category"]);
        Permission::create(["action" => "Delete Category", "action_slug" => "delete-category"]);

        Permission::create(["action" => "Create Shift", "action_slug" => "create-shift"]);
        Permission::create(["action" => "View Shift", "action_slug" => "view-shift"]);
        Permission::create(["action" => "Update Shift", "action_slug" => "update-shift"]);
        Permission::create(["action" => "Delete Shift", "action_slug" => "delete-shift"]);

        Permission::create(["action" => "Create Work Schedule", "action_slug" => "create-work-schedule"]);
        Permission::create(["action" => "View Work Schedule", "action_slug" => "view-work-schedule"]);
        Permission::create(["action" => "Update Work Schedule", "action_slug" => "update-work-schedule"]);
        Permission::create(["action" => "Delete Work Schedule", "action_slug" => "delete-work-schedule"]);

        Permission::create(["action" => "Create Item", "action_slug" => "create-item"]);
        Permission::create(["action" => "View Item", "action_slug" => "view-item"]);
        Permission::create(["action" => "Update Item", "action_slug" => "update-item"]);
        Permission::create(["action" => "Delete Item", "action_slug" => "delete-item"]);

        Permission::create(["action" => "Create Customer", "action_slug" => "create-customer"]);
        Permission::create(["action" => "View Customer", "action_slug" => "view-customer"]);
        Permission::create(["action" => "Update Customer", "action_slug" => "update-customer"]);
        Permission::create(["action" => "Delete Customer", "action_slug" => "delete-customer"]);

        $store = Store::factory()->create([
            "name" => "My Store",
            "email" => "hexagon@gmail.com",
            "password" => Hash::make("hexagon"),
        ]);

        // unused role
        Role::factory()->create([
            "store_id" => $store->id,
            "name" => "Unused Role",
            "description" => "This role is for test purpose.",
        ]);

        $other_store = Store::factory()
            ->hasBranches(2)
            ->create([
                "name" => "Other Store",
                "email" => "other@gmail.com",
                "password" => Hash::make("other"),
            ]);

        $branch = Branch::factory()
            ->for($store)
            ->create([
                "image" => "test/branch.jpeg",
            ]);

        $abandoned_branch = Branch::factory()
            ->for($store)
            ->create([
                "image" => "test/branch.jpeg",
                "name" => "Abandoned Branch",
            ]);

        // seed item category with item
        ItemCategory::factory()
            ->count(5)
            ->for($store)
            ->has(
                Item::factory()
                    ->hasProperties(1, [
                        "branch_id" => $branch->id,
                    ])
                    ->count(5)
                    ->state(["store_id" => $store->id])
            )
            ->create();

        $this->seedEmployeeAndShiftAndWorkSchedule($store, $branch);

        $this->seedEmployeeAndShiftAndWorkSchedule($store, $abandoned_branch);

        $this->seedCustomers($store);

        $this->seedCustomers($other_store);

        $this->seedSuppliers($store);

        $this->seedSuppliers($other_store);
    }

    private function seedEmployeeAndShiftAndWorkSchedule(Store $store, Branch $branch)
    {
        // seed purchase staff
        $staff_1 = Employee::factory()
            ->for($store)
            ->has(
                Employment::factory()
                    ->count(1)
                    ->for($branch)
                    ->hasRoles(1, [
                        "role_id" => Role::where("store_id", $store->id)
                            ->where("name", "Purchase")
                            ->first()->id,
                    ])
            )
            ->create([
                "avatar" => "test/avatar.jpeg",
            ]);

        // seed sales staff
        $staff_2 = Employee::factory()
            ->for($store)
            ->has(
                Employment::factory()
                    ->count(1)
                    ->for($branch)
                    ->hasRoles(1, [
                        "role_id" => Role::where("store_id", $store->id)
                            ->where("name", "Sale")
                            ->first()->id,
                    ])
            )
            ->create([
                "avatar" => "test/avatar.jpeg",
            ]);

        // seed manager staff
        $staff_3 = Employee::factory()
            ->for($store)
            ->has(
                Employment::factory()
                    ->count(1)
                    ->for($branch)
                    ->hasRoles(1, [
                        "role_id" => Role::where("store_id", $store->id)
                            ->where("name", "Manage")
                            ->first()->id,
                    ])
            )
            ->create([
                "avatar" => "test/avatar.jpeg",
            ]);

        Shift::factory()
            ->for($branch)
            ->count(3)
            ->hasWorkSchedules(1, ["date" => date("Y-m-d", strtotime("+1 day")), "employee_id" => $staff_1->id])
            ->hasWorkSchedules(1, ["date" => date("Y-m-d", strtotime("-1 day")), "employee_id" => $staff_1->id])
            ->hasWorkSchedules(1, ["date" => date("Y-m-d", strtotime("+1 day")), "employee_id" => $staff_2->id])
            ->hasWorkSchedules(1, ["date" => date("Y-m-d", strtotime("-1 day")), "employee_id" => $staff_2->id])
            ->hasWorkSchedules(1, ["date" => date("Y-m-d", strtotime("+1 day")), "employee_id" => $staff_3->id])
            ->hasWorkSchedules(1, ["date" => date("Y-m-d", strtotime("-1 day")), "employee_id" => $staff_3->id])
            ->create();
    }

    private function seedCustomers(Store $store)
    {
        $customers = Customer::factory()
            ->count(5)
            ->for($store)
            ->create();

        return $customers;
    }

    private function seedSuppliers(Store $store)
    {
        $suppliers = Supplier::factory()
            ->count(5)
            ->for($store)
            ->create();

        return $suppliers;
    }
}
