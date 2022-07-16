<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Employment;
use App\Models\EmploymentRole;
use App\Models\Item;
use App\Models\ItemProperty;
use App\Models\Permission;
use App\Models\PermissionRole;
use App\Models\PurchaseSheet;
use App\Models\PurchaseSheetItem;
use App\Models\QuantityCheckingItem;
use App\Models\QuantityCheckingSheet;
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
        $this->truncate();

        $this->call(PermissionSeeder::class);

        $store = Store::factory()->create([
            "name" => "My Store",
            "email" => "hexagon@gmail.com",
            "password" => Hash::make("hexagon"),
        ]);

        [$branch, $abandoned_branch] = $this->seedBranch($store);

        // unused role
        Role::factory()->create([
            "store_id" => $store->id,
            "name" => "Unused Role",
            "description" => "This role is for test purpose.",
        ]);

        $other_store = Store::factory()->create([
            "name" => "Other Store",
            "email" => "other@gmail.com",
            "password" => Hash::make("other"),
        ]);

        $this->seedBranch($other_store);

        $this->seedItemAndCategory($store, $branch);
        $this->seedItemAndCategory($store, $abandoned_branch);

        $this->seedEmployeeAndShiftAndWorkSchedule($store, $branch);
        $this->seedEmployeeAndShiftAndWorkSchedule($store, $abandoned_branch);

        $this->seedCustomers($store);
        $this->seedCustomers($other_store);

        $this->seedSuppliers($store);
        $this->seedSuppliers($other_store);

        $this->seedPurchaseSheet($branch);
        $this->seedPurchaseSheet($abandoned_branch);

        $this->seedQuantityCheckingSheet($branch);
        $this->seedQuantityCheckingSheet($abandoned_branch);
    }

    private function seedEmployee(Store $store, Branch $branch, string $role)
    {
        return Employee::factory()
            ->for($store)
            ->has(
                Employment::factory()
                    ->count(1)
                    ->for($branch)
                    ->hasRoles(1, [
                        "role_id" => Role::where("store_id", $store->id)
                            ->where("name", $role)
                            ->first()->id,
                    ])
            )
            ->create([
                "avatar" => "test/avatar.jpeg",
            ]);
    }

    private function seedEmployeeAndShiftAndWorkSchedule(Store $store, Branch $branch)
    {
        $staff_1 = $this->seedEmployee($store, $branch, "Purchase");
        $staff_2 = $this->seedEmployee($store, $branch, "Sale");
        $staff_3 = $this->seedEmployee($store, $branch, "Manage");

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

    private function seedPurchaseSheet(Branch $branch)
    {
        $employee = Employee::whereRelation("employment", "branch_id", $branch->id)->first();

        PurchaseSheet::factory()
            ->count(5)
            ->has(
                PurchaseSheetItem::factory()
                    ->count(5)
                    ->state([
                        "item_id" => fn() => Item::where("store_id", $employee->store_id)
                            ->inRandomOrder()
                            ->first()->id,
                    ]),
                "items"
            )
            ->create([
                "branch_id" => $branch->id,
                "employee_id" => $employee->id,
                "supplier_id" => Supplier::where("store_id", $employee->store_id)
                    ->inRandomOrder()
                    ->first()->id,
            ]);
    }

    private function seedQuantityCheckingSheet(Branch $branch)
    {
        $employee = Employee::whereRelation("employment", "branch_id", $branch->id)->first();

        QuantityCheckingSheet::factory()
            ->count(5)
            ->has(
                QuantityCheckingItem::factory()
                    ->count(5)
                    ->state([
                        "item_id" => fn() => Item::where("store_id", $employee->store_id)
                            ->inRandomOrder()
                            ->first()->id,
                    ]),
                "items"
            )
            ->create([
                "branch_id" => $branch->id,
                "employee_id" => $employee->id
            ]);
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

    private function seedItemAndCategory(Store $store, Branch $branch)
    {
        return Category::factory()
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
    }

    private function seedBranch(Store $store)
    {
        $branch1 = Branch::factory()
            ->for($store)
            ->create([
                "image" => "test/branch.jpeg",
            ]);

        $branch2 = Branch::factory()
            ->for($store)
            ->create([
                "image" => "test/branch.jpeg",
            ]);

        return [$branch1, $branch2];
    }

    private function truncate()
    {
        Store::truncate();
        Employee::truncate();
        Branch::truncate();
        Item::truncate();
        Category::truncate();
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
        PurchaseSheet::truncate();
        PurchaseSheetItem::truncate();
    }
}
