<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\DefaultItem;
use App\Models\Employee;
use App\Models\Employment;
use App\Models\EmploymentRole;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\ItemProperty;
use App\Models\PurchaseSheet;
use App\Models\PurchaseSheetItem;
use App\Models\Store;
use App\Models\Supplier;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {

        // Create store
        $store = Store::create([
            'name' => 'Store 1',
            'email' => 'hexagon@gmail.com',
            'password' => Hash::make('hexagon'),
        ]);

        $store->categories()->createMany([
            ['name' => 'ĐỒ UỐNG CÁC LOẠI'],
            ['name' => 'SỮA UỐNG CÁC LOẠI'],
            ['name' => 'BÁNH KẸO CÁC LOẠI'],
            ['name' => 'MÌ, CHÁO, PHỞ, BÚN'],
            ['name' => 'DẦU ĂN, GIA VỊ'],
            ['name' => 'GẠO, BỘT, ĐỒ KHÔ'],
            ['name' => 'ĐỒ MÁT, ĐÔNG LẠNH'],
            ['name' => 'TÃ, ĐỒ CHO BÉ'],
            ['name' => 'CHĂM SÓC CÁ NHÂN'],
            ['name' => 'VỆ SINH NHÀ CỬA'],
            ['name' => 'ĐỒ DÙNG GIA ĐÌNH'],
            ['name' => 'VĂN PHÒNG PHẨM'],
            ['name' => 'THUỐC VÀ THỰC PHẨM CHỨC NĂNG'],
        ]);

        // Create branch
        $branch = Branch::create([
            'name' => 'Branch 1',
            'address' => 'Branch address 1',
            'store_id' => $store->id,
            'image' => 'branches/default.jpg',
            'image_key' => Str::uuid(),
        ]);

        // Create employee
        $employee = Employee::create([
            'store_id' => $store->id,
            'name' => 'Employee 1',
            'email' => 'employee1@gmail.com',
            'password' => Hash::make('employee1'),
            'phone' => null,
            'birthday' => null,
            'avatar' => null,
            'avatar_key' => Str::uuid(),
            'gender' => null
        ]);

        $employment = Employment::create([
            'employee_id' => $employee->id,
            'branch_id' => $branch->id,
            'from' => date("Y/m/d")
        ]);

        // create employment roles
        foreach (['sale', 'purchase', 'manage'] as $role) {
            EmploymentRole::create([
                'employment_id' => $employment->id,
                'role' => $role
            ]);
        }

        // Create supplier
        $supplier = Supplier::create([
            'store_id' => $store->id,
            'name' => 'Supplier 1',
            'code' => 'SUP000001',
            'address' => null,
            'phone' => '0384415336',
            'email' => null,
            'tax' => null,
            'note' => null,
        ]);

        $default_item_1 = DefaultItem::where('bar_code', '8935049500445')->first();
        $default_item_2 = DefaultItem::where('bar_code', '8936036020373')->first();

        $category_1 = ItemCategory::where('store_id', $store->id)->where('name', 'like', '%' . $default_item_1->category->name . '%')->first();
        $category_2 = ItemCategory::where('store_id', $store->id)->where('name', 'like', '%' . $default_item_2->category->name . '%')->first();

        $item_1 = Item::create([
            'store_id' => $store->id,
            'barcode' => $default_item_1->bar_code,
            'code' => 'IT000001',
            'name' => $default_item_1->product_name,
            'category_id' => $category_1 ? $category_1->id : null,
            'image' => "https://149.28.148.73/merged-db/" . $default_item_1->image_url
        ]);

        $item_2 = Item::create([
            'store_id' => $store->id,
            'barcode' => $default_item_2->bar_code,
            'code' => 'IT000002',
            'name' => $default_item_2->product_name,
            'category_id' => $category_2 ? $category_2->id : null,
            'image' => "https://149.28.148.73/merged-db/" . $default_item_2->image_url
        ]);

        ItemProperty::create([
            'item_id' => $item_1->id,
            'branch_id' => $branch->id,
            'quantity' => 50,
            'sell_price' => 20000,
            'base_price' => 15000,
            'last_purchase_price' => 15000,
        ]);

        ItemProperty::create([
            'item_id' => $item_2->id,
            'branch_id' => $branch->id,
            'quantity' => 100,
            'sell_price' => 40000,
            'base_price' => 20000,
            'last_purchase_price' => 20000,
        ]);

        // create purchase sheet
        $purchase_sheet = PurchaseSheet::create([
            'code' => 'PS000001',
            'employee_id' => $employee->id,
            'branch_id' => $branch->id,
            'supplier_id' => $supplier->id,
            'discount' => 0,
            'discount_type' => 'cash',
            'total' => 2750000,
            'paid_amount' => 2750000,
            'note' => '',
        ]);

        // create purchase sheet items
        PurchaseSheetItem::create([
            'purchase_sheet_id' => $purchase_sheet->id,
            'item_id' => $item_1->id,
            'quantity' => 50,
            'price' => 15000,
            'discount' => 0,
            'discount_type' => 'cash',
            'total' => 750000,
        ]);

        PurchaseSheetItem::create([
            'purchase_sheet_id' => $purchase_sheet->id,
            'item_id' => $item_2->id,
            'quantity' => 100,
            'price' => 20000,
            'discount' => 0,
            'discount_type' => 'cash',
            'total' => 2000000,
        ]);

    }
}
