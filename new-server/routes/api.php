<?php

namespace App\Http\Controllers;

use App\Http\Middleware\Role\AdminOrSaleOrPurchaser;
use App\Http\Middleware\Role\Authenticated;
use App\Http\Middleware\Role\HavePurchaseRole;
use App\Http\Middleware\Role\NotEmployee;
use App\Http\Middleware\Role\NotStoreAdmin;
use App\Http\Middleware\Role\OnlyEmployee;
use App\Http\Middleware\Role\OnlyStoreAdmin;
use App\Request\StoreEmailVerificationRequest;
use Illuminate\Support\Facades\Route;

Route::get("/email/verify/{id}/{hash}", function (StoreEmailVerificationRequest $request) {
    $request->fulfill();

    return response()->json(["message" => "Email verified successfully"], 200);
})
    ->middleware(["signed", Authenticated::class])
    ->name("verification.verify");

Route::prefix("/store")->group(function () {
    Route::middleware([NotStoreAdmin::class])->group(function () {
        // POST /store/register - register a new store
        Route::post("/register", [StoreController::class, "register"]);
        // POST /store/login - login as store admin
        Route::post("/login", [StoreController::class, "login"]);
    });
    Route::middleware([OnlyStoreAdmin::class])->group(function () {
        // GET /store/logout - logout as store admin
        Route::post("/logout", [StoreController::class, "logout"]);
        // GET /store/me - get store info
        Route::get("/me", [StoreController::class, "getStore"]);
    });
});

// GET /guard - get guard info
Route::get("/guard", [StoreController::class, "getGuard"]);

Route::middleware([OnlyStoreAdmin::class])->group(function () {
    Route::prefix("/branch")->group(function () {
        // POST /branch - create a new branch
        Route::post("/", [BranchController::class, "create"]);
        // GET /branch - get all branches
        Route::get("/", [BranchController::class, "getBranches"]);
        // GET /branch/{image_key} - get branch image by image key
        Route::get("/image/{image_key}", [BranchController::class, "getBranchImage"]);
        // POST /branch/image - update branch image
        Route::put("{branch_id}/image", [BranchController::class, "updateBranchImage"]);
        // GET /branch/{branch_id} - get a branch by id
        Route::get("/{branch_id}", [BranchController::class, "getBranch"]);
        // POST /branch/{branch_id} - update a branch by id
        Route::put("/{branch_id}", [BranchController::class, "update"]);
        // DELETE /branch/{branch_id} - delete a branch by id
        Route::delete("/{branch_id}", [BranchController::class, "delete"]);
    });

    Route::prefix("/role")->group(function () {
        // POST /role - create a new role
        Route::post("/", [RoleController::class, "create"]);
        // GET /role - get all roles
        Route::get("/", [RoleController::class, "getRoles"]);
        // GET /role/{role_id} - get a role by id
        Route::get("/{role_id}", [RoleController::class, "getRole"]);
        // PUT /role/{role_id} - update a role by id
        Route::put("/{role_id}", [RoleController::class, "update"]);
        // DELETE /role/{role_id} - delete a role by id
        Route::delete("/{role_id}", [RoleController::class, "delete"]);
    });

    Route::prefix("/permission")->group(function () {
        // PUT /permission/{permission_id} - update a permission by id
        Route::put("/{permission_id}", [PermissionController::class, "update"]);
    });
});

Route::prefix("/employee")->group(function () {
    Route::post("/login", [EmployeeController::class, "login"])->middleware([NotStoreAdmin::class, NotEmployee::class]);

    Route::post("/logout", [EmployeeController::class, "logout"])->middleware([OnlyEmployee::class]);

    Route::middleware(["authenticated"])->group(function () {
        Route::get("/avatar/{avatar_key}", [EmployeeController::class, "getAvatar"]);
    });

    Route::middleware([OnlyEmployee::class])->group(function () {
        Route::get("/me", [EmployeeController::class, "me"]);
        Route::put("/password", [EmployeeController::class, "changePassword"]);
    });

    Route::middleware([OnlyStoreAdmin::class])->group(function () {
        // POST /employee - create a new employee
        Route::post("/", [EmployeeController::class, "create"]);
        // GET /employee/deleted - get deleted employees
        Route::get("/deleted", [EmployeeController::class, "getDeleted"]);
        // PUT /employee/{employee_id} - update an employee by id
        Route::put("/{employee_id}", [EmployeeController::class, "update"]);
        // GET /employee - get all employees
        Route::get("/", [EmployeeController::class, "getmany"]);
        // GET /employee/branch/{branch_id} - get employees by branch id
        Route::get("/branch/{branch_id}", [EmployeeController::class, "getEmployeesByBranchId"]);
        // GET /employee/{employee_id} - get an employee by id
        Route::get("/{employee_id}", [EmployeeController::class, "getOne"]);
        // POST /employee/transfer - transfer an employee to another branch
        Route::post("/transfer", [EmployeeController::class, "transfer"]);
        // POST /employee/transfer/many - transfer many employees to another branch
        Route::post("/transfer/many", [EmployeeController::class, "transferMany"]);
        // PATCH /employee/{employee_id} - update an employee by id
        Route::put("/{employee_id}", [EmployeeController::class, "update"]);
        // DELETE /employee/{employee_id} - delete an employee by id
        Route::delete("/{employee_id}", [EmployeeController::class, "delete"]);
        // POST /employee/{employee_id}/restore - restore an employee by id
        Route::post("/{employee_id}/restore", [EmployeeController::class, "restore"]);
        // DELETE /employee/{employee_id}/force - force delete an employee by id
        Route::delete("/{employee_id}/force", [EmployeeController::class, "forceDelete"]);
    });
});

Route::prefix("/shift")
    ->middleware("authenticated")
    ->group(function () {
        Route::post("/", [ShiftController::class, "create"])->middleware("have_permission:create-shift");

        Route::put("/{shift_id}", [ShiftController::class, "update"])->middleware("have_permission:update-shift");

        Route::delete("/{shift_id}", [ShiftController::class, "delete"])->middleware("have_permission:delete-shift");

        Route::get("/", [ShiftController::class, "getShifts"])->middleware("have_permission:view-shift");

        Route::get("/{shift_id}", [ShiftController::class, "getShift"])->middleware("have_permission:view-shift");
    });

Route::prefix("/work-schedule")
    ->middleware("authenticated")
    ->group(function () {
        // POST /work-schedule - create a new work schedule
        Route::post("/", [WorkScheduleController::class, "create"])->middleware("have_permission:create-work-schedule");
        // GET /work-schedule - get all work schedules
        Route::get("/", [WorkScheduleController::class, "getMany"])->middleware("have_permission:view-work-schedule");
        // PATCH /work-schedule/{work_schedule_id} - update a work schedule by id
        Route::put("/{work_schedule_id}", [WorkScheduleController::class, "update"])->middleware(
            "have_permission:update-work-schedule"
        );
        // DELETE /work-schedule/{work_schedule_id} - delete a work schedule by id
        Route::delete("/{work_schedule_id}", [WorkScheduleController::class, "delete"])->middleware(
            "have_permission:delete-work-schedule"
        );
    });

Route::prefix("/supplier")
    ->middleware("authenticated")
    ->group(function () {
        Route::middleware(OnlyStoreAdmin::class)->group(function () {
            Route::get("/deleted", [SupplierController::class, "getDeleted"]);

            Route::post("/{supplier_id}/restore", [SupplierController::class, "restore"]);

            Route::delete("/{supplier_id}/force", [SupplierController::class, "forceDelete"]);
        });

        Route::post("/", [SupplierController::class, "create"])->middleware("have_permission:create-supplier");

        Route::get("/", [SupplierController::class, "getMany"])->middleware("have_permission:view-supplier");

        Route::get("/{supplier_id}", [SupplierController::class, "getOne"])->middleware(
            "have_permission:view-supplier"
        );

        Route::put("/{supplier_id}", [SupplierController::class, "update"])->middleware(
            "have_permission:update-supplier"
        );

        Route::delete("/{supplier_id}", [SupplierController::class, "delete"])->middleware(
            "have_permission:delete-supplier"
        );
    });

Route::prefix("/item-category")
    ->middleware("authenticated")
    ->group(function () {
        Route::middleware(OnlyStoreAdmin::class)->group(function () {
            Route::get("/deleted", [ItemCategoryController::class, "getDeleted"]);

            Route::post("/{item_category_id}/restore", [ItemCategoryController::class, "restore"]);

            Route::delete("/{item_category_id}/force", [ItemCategoryController::class, "forceDelete"]);
        });

        Route::post("/", [ItemCategoryController::class, "create"])->middleware("have_permission:create-category");

        Route::get("/", [ItemCategoryController::class, "getMany"])->middleware("have_permission:view-category");

        Route::get("/{item_category_id}", [ItemCategoryController::class, "getOne"])->middleware(
            "have_permission:view-category"
        );

        Route::put("/{item_category_id}", [ItemCategoryController::class, "update"])->middleware(
            "have_permission:update-category"
        );

        Route::delete("/{item_category_id}", [ItemCategoryController::class, "delete"])->middleware(
            "have_permission:delete-category"
        );
    });

Route::prefix("/item")->group(function () {
    // GET /last-purchase-price/{item_id} - get the last purchase price of an item
    Route::get("/last-purchase-price/{item_id}", [ItemController::class, "getLastPurchasePrice"])->middleware([
        OnlyEmployee::class,
        HavePurchaseRole::class,
    ]);

    Route::middleware([AdminOrSaleOrPurchaser::class])->group(function () {
        // POST /item - create a new item
        Route::post("/", [ItemController::class, "create"]);
        // PATCH /item/{item_id} - update an item by id
        Route::patch("/{item_id}", [ItemController::class, "update"]);
        // DELETE /item/{item_id} - delete an item by id
        Route::delete("/{item_id}", [ItemController::class, "delete"]);
    });
    Route::middleware([OnlyEmployee::class])->group(function () {
        // POST /item/move/{bar_code} - move an item from default to current
        Route::post("/move", [ItemController::class, "moveItem"]);
        // GET /item/search - search items
        Route::get("/search", [ItemController::class, "getItemsBySearch"]);
        // GET /item/bar_code/{bar_code} - get an item by bar code
        Route::get("/bar_code/{bar_code}", [ItemController::class, "getItemByBarCode"]);
        // GET /item - get all items
        Route::get("/", [ItemController::class, "getSellingItems"]);
        // GET /item/{item_id}/price_history - get price history of an item
        Route::get("/{item_id}/price_history", [ItemController::class, "getPriceHistory"]);
        // GET /item/{item_id} - get an item by id
        Route::get("/{item_id}", [ItemController::class, "getOne"]);
        // GET /item/purchase-sheet/{purchase_sheet_id} - get items from a purchase sheet
        Route::get("/purchase-sheet/{purchase_sheet_id}", [ItemController::class, "getItemsFromPurchaseSheet"]);
    });

    Route::get("/{item_id}/stock", [ItemController::class, "getStock"])->middleware([OnlyEmployee::class]);
});

Route::prefix("/purchase-sheet")
    ->middleware([OnlyEmployee::class, HavePurchaseRole::class])
    ->group(function () {
        // POST /purchase-sheet - create a new purchase sheet
        Route::post("/", [PurchaseSheetController::class, "create"]);
        // GET /purchase-sheet - get all purchase sheets
        Route::get("/", [PurchaseSheetController::class, "getPurchaseSheets"]);
        // GET /purchase-sheet/{purchase_sheet_id} - get a purchase sheet by id
        Route::get("/{purchase_sheet_id}", [PurchaseSheetController::class, "getPurchaseSheet"]);
        // PATCH /purchase-sheet/{purchase_sheet_id} - update a purchase sheet by id
        Route::patch("/{purchase_sheet_id}", [PurchaseSheetController::class, "update"]);
        // PATCH /purchase-sheet/{purchase_sheet_id}/note - update a purchase sheet note by id
        Route::patch("/{purchase_sheet_id}/note", [PurchaseSheetController::class, "updateNote"]);
        // DELETE /purchase-sheet/{purchase_sheet_id} - delete a purchase sheet by id
        Route::delete("/{purchase_sheet_id}", [PurchaseSheetController::class, "delete"]);
    });

Route::prefix("/return-purchase-sheet")
    ->middleware([OnlyEmployee::class, HavePurchaseRole::class])
    ->group(function () {
        // POST /return-purchase-sheet - create a new return purchase sheet
        Route::post("/", [ReturnPurchaseSheetController::class, "create"]);
        // GET /return-purchase-sheet - get all return purchase sheets
        Route::get("/", [ReturnPurchaseSheetController::class, "getReturnPurchaseSheets"]);
        // GET /return-purchase-sheet/returnable/{purchase_sheet_id} - get a returnable purchase sheet by id
        Route::get("/returnable/{purchase_sheet_id}", [ReturnPurchaseSheetController::class, "getReturnableItems"]);
        // GET /return-purchase-sheet/{return_purchase_sheet_id} - get a return purchase sheet by id
        Route::get("/{return_purchase_sheet_id}", [ReturnPurchaseSheetController::class, "getReturnPurchaseSheet"]);
        // PATCH /return-purchase-sheet/{return_purchase_sheet_id} - update a return purchase sheet by id
        Route::patch("/{return_purchase_sheet_id}", [ReturnPurchaseSheetController::class, "update"]);
        // PATCH /return-purchase-sheet/{return_purchase_sheet_id}/note - update a return purchase sheet note by id
        Route::patch("/{return_purchase_sheet_id}/note", [ReturnPurchaseSheetController::class, "updateNote"]);
        // DELETE /return-purchase-sheet/{return_purchase_sheet_id} - delete a return purchase sheet by id
        Route::delete("/{return_purchase_sheet_id}", [ReturnPurchaseSheetController::class, "delete"]);
    });

Route::prefix("/customer")
    ->middleware(["authenticated"])
    ->group(function () {
        // POST /customer - create a new customer
        Route::post("/", [CustomerController::class, "create"])->middleware("have_permission:create-customer");
        // GET /customer - get all customers
        Route::get("/", [CustomerController::class, "getCustomers"])->middleware("have_permission:view-customer");
        // GET /customer/one - get a customer by id or code
        Route::get("/one", [CustomerController::class, "getCustomer"])->middleware("have_permission:view-customer");
        // PATCH /customer/{customer_id} - update a customer by id
        Route::put("/{customer_id}", [CustomerController::class, "update"])->middleware(
            "have_permission:update-customer"
        );
        // POST /customer/add-point/{customer_id} - add point to a customer by id
        Route::post("/add-point/{customer_id}", [CustomerController::class, "addPoint"])->middleware(
            "have_permission:update-customer"
        );
        // POST /customer/use-point/{customer_id} - use point from a customer by id
        Route::post("/use-point/{customer_id}", [CustomerController::class, "usePoint"])->middleware(
            "have_permission:update-customer"
        );
    });

Route::prefix("/default-item")->group(function () {
    // GET /default-item - get all default items
    Route::get("/", [DefaultItemController::class, "getItems"]);
    // GET /default-item/barcode/{barcode} - get a default item by barcode
    Route::get("/one", [DefaultItemController::class, "getItem"]);
});
