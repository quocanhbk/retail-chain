<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\Employment;
use App\Models\EmploymentRole;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\ItemProperty;
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
        Branch::truncate();
        ItemCategory::truncate();
        EmploymentRole::truncate();
        Employment::truncate();
        Employee::truncate();
        Supplier::truncate();
        Item::truncate();
        ItemProperty::truncate();
        Shift::truncate();
        WorkSchedule::truncate();

        $store = Store::factory()->create([
            "name" => "My Store",
            "email" => "hexagon@gmail.com",
            "password" => Hash::make("hexagon"),
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

        $categories = ItemCategory::factory()
            ->count(5)
            ->for($store)
            ->has(
                Item::factory()
                    ->has(ItemProperty::factory()->for($branch))
                    ->count(5)
            )
            ->create();

        $employee = Employee::factory()
            ->for($store)
            ->create([
                "avatar" => "test/avatar.jpeg",
            ]);

        Employment::factory()
            ->count(1)
            ->for($branch)
            ->for($employee)
            ->hasRoles(1, ["role" => "purchase"])
            ->hasRoles(1, ["role" => "sale"])
            ->hasRoles(1, ["role" => "manage"])
            ->create();

        Shift::factory()
            ->count(3)
            ->for($branch)
            ->hasWorkSchedules(1, ["date" => date("Y-m-d", strtotime("+1 day"))])
            ->hasWorkSchedules(1, ["date" => date("Y-m-d", strtotime("-1 days"))])
            ->create();

        Supplier::factory()
            ->count(5)
            ->for($store)
            ->create();

        // $store
        //     ->categories()
        //     ->createMany([
        //         ["name" => "????? U???NG C??C LO???I"],
        //         ["name" => "S???A U???NG C??C LO???I"],
        //         ["name" => "B??NH K???O C??C LO???I"],
        //         ["name" => "M??, CH??O, PH???, B??N"],
        //         ["name" => "D???U ??N, GIA V???"],
        //         ["name" => "G???O, B???T, ????? KH??"],
        //         ["name" => "????? M??T, ????NG L???NH"],
        //         ["name" => "T??, ????? CHO B??"],
        //         ["name" => "CH??M S??C C?? NH??N"],
        //         ["name" => "V??? SINH NH?? C???A"],
        //         ["name" => "????? D??NG GIA ????NH"],
        //         ["name" => "V??N PH??NG PH???M"],
        //         ["name" => "THU???C V?? TH???C PH???M CH???C N??NG"],
        //     ]);

        // // create purchase sheet
        // $purchase_sheet = PurchaseSheet::create([
        //     "code" => "PS000001",
        //     "employee_id" => $employee->id,
        //     "branch_id" => $branch->id,
        //     "supplier_id" => $supplier->id,
        //     "discount" => 0,
        //     "discount_type" => "cash",
        //     "total" => 2750000,
        //     "paid_amount" => 2750000,
        //     "note" => "",
        // ]);

        // // create purchase sheet items
        // PurchaseSheetItem::create([
        //     "purchase_sheet_id" => $purchase_sheet->id,
        //     "item_id" => $item_1->id,
        //     "quantity" => 50,
        //     "price" => 15000,
        //     "discount" => 0,
        //     "discount_type" => "cash",
        //     "total" => 750000,
        // ]);

        // PurchaseSheetItem::create([
        //     "purchase_sheet_id" => $purchase_sheet->id,
        //     "item_id" => $item_2->id,
        //     "quantity" => 100,
        //     "price" => 20000,
        //     "discount" => 0,
        //     "discount_type" => "cash",
        //     "total" => 2000000,
        // ]);
    }
}
