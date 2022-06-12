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

        $other_store = Store::factory()->create([
            "name" => "Other Store",
            "email" => "other@gmail.com",
            "password" => Hash::make("other"),
        ]);

        $branch = Branch::factory()
            ->for($store)
            ->create([
                "image" => "test/branch.jpeg",
            ]);

        Branch::factory()
            ->for($store)
            ->create([
                "image" => "test/branch.jpeg",
                "name" => "Abandoned Branch",
            ]);

        ItemCategory::factory()
            ->count(5)
            ->for($store)
            ->has(
                Item::factory()
                    ->has(ItemProperty::factory()->for($branch))
                    ->count(5)
            )
            ->create();

        ItemCategory::factory()->for($store)->create();

        ItemCategory::factory()
            ->count(5)
            ->for($other_store)->create();

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

        Customer::factory()
            ->count(5)
            ->for($store)
            ->create();

        Customer::factory()
            ->count(5)
            ->for($other_store)
            ->create();

        // $store
        //     ->categories()
        //     ->createMany([
        //         ["name" => "ĐỒ UỐNG CÁC LOẠI"],
        //         ["name" => "SỮA UỐNG CÁC LOẠI"],
        //         ["name" => "BÁNH KẸO CÁC LOẠI"],
        //         ["name" => "MÌ, CHÁO, PHỞ, BÚN"],
        //         ["name" => "DẦU ĂN, GIA VỊ"],
        //         ["name" => "GẠO, BỘT, ĐỒ KHÔ"],
        //         ["name" => "ĐỒ MÁT, ĐÔNG LẠNH"],
        //         ["name" => "TÃ, ĐỒ CHO BÉ"],
        //         ["name" => "CHĂM SÓC CÁ NHÂN"],
        //         ["name" => "VỆ SINH NHÀ CỬA"],
        //         ["name" => "ĐỒ DÙNG GIA ĐÌNH"],
        //         ["name" => "VĂN PHÒNG PHẨM"],
        //         ["name" => "THUỐC VÀ THỰC PHẨM CHỨC NĂNG"],
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
