<?php

namespace App\Http\Controllers;

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
        Route::put("{id}/image", [BranchController::class, "updateBranchImage"]);
        // GET /branch/{id} - get a branch by id
        Route::get("/{id}", [BranchController::class, "getBranch"]);
        // POST /branch/{id} - update a branch by id
        Route::put("/{id}", [BranchController::class, "update"]);
        // DELETE /branch/{id} - delete a branch by id
        Route::delete("/{id}", [BranchController::class, "delete"]);
    });

    Route::prefix("/role")->group(function () {
        // POST /role - create a new role
        Route::post("/", [RoleController::class, "create"]);
        // GET /role - get all roles
        Route::get("/", [RoleController::class, "getRoles"]);
        // GET /role/{id} - get a role by id
        Route::get("/{id}", [RoleController::class, "getRole"]);
        // PUT /role/{id} - update a role by id
        Route::put("/{id}", [RoleController::class, "update"]);
        // DELETE /role/{id} - delete a role by id
        Route::delete("/{id}", [RoleController::class, "delete"]);
    });

    Route::prefix("/permission")->group(function () {
        // PUT /permission/{id} - update a permission by id
        Route::put("/{id}", [PermissionController::class, "update"]);
    });
});

Route::prefix("/employee")->group(function () {
    // POST /employee/login - login as employee
    Route::post("/login", [EmployeeController::class, "login"])->middleware([NotStoreAdmin::class, NotEmployee::class]);
    // POST /employee/logout - logout as employee
    Route::post("/logout", [EmployeeController::class, "logout"])->middleware([OnlyEmployee::class]);

    Route::middleware(["authenticated"])->group(function () {
        // GET /employee/avatar/{avatar_key} - get employee avatar by avatar key
        Route::get("/avatar/{avatar_key}", [EmployeeController::class, "getAvatar"]);
    });

    Route::middleware([OnlyEmployee::class])->group(function () {
        // GET /employee/me - get employee info
        Route::get("/me", [EmployeeController::class, "me"]);
        // PUT /employee/password - update employee password
        Route::put("/password", [EmployeeController::class, "changePassword"]);
    });

    Route::middleware([OnlyStoreAdmin::class])->group(function () {
        // POST /employee - create a new employee
        Route::post("/", [EmployeeController::class, "create"]);
        // GET /employee/deleted - get deleted employees
        Route::get("/deleted", [EmployeeController::class, "getDeleted"]);
        // PUT /employee/{id} - update an employee by id
        Route::put("/{id}", [EmployeeController::class, "update"]);
        // GET /employee - get all employees
        Route::get("/", [EmployeeController::class, "getMany"]);
        // GET /employee/{id} - get an employee by id
        Route::get("/{id}", [EmployeeController::class, "getOne"]);
        // POST /employee/transfer - transfer an employee to another branch
        Route::post("/transfer", [EmployeeController::class, "transfer"]);
        // PUT /employee/{id} - update an employee by id
        Route::put("/{id}", [EmployeeController::class, "update"]);
        // DELETE /employee/{id} - delete an employee by id
        Route::delete("/{id}", [EmployeeController::class, "delete"]);
        // POST /employee/{id}/restore - restore an employee by id
        Route::post("/{id}/restore", [EmployeeController::class, "restore"]);
        // DELETE /employee/{id}/force - force delete an employee by id
        Route::delete("/{id}/force", [EmployeeController::class, "forceDelete"]);
    });
});

Route::prefix("/shift")
    ->middleware("authenticated")
    ->group(function () {
        // POST /shift - create a new shift
        Route::post("/", [ShiftController::class, "create"])->middleware("have_permission:create-shift");
        // GET /shift - get all shifts
        Route::get("/", [ShiftController::class, "getShifts"])->middleware("have_permission:view-shift");
        // GET /shift/{id} - get a shift by id
        Route::get("/{id}", [ShiftController::class, "getShift"])->middleware("have_permission:view-shift");
        // PUT /shift/{id} - update a shift by id
        Route::put("/{id}", [ShiftController::class, "update"])->middleware("have_permission:update-shift");
        // DELETE /shift/{id} - delete a shift by id
        Route::delete("/{id}", [ShiftController::class, "delete"])->middleware("have_permission:delete-shift");
    });

Route::prefix("/work-schedule")
    ->middleware("authenticated")
    ->group(function () {
        // POST /work-schedule - create a new work schedule
        Route::post("/", [WorkScheduleController::class, "create"])->middleware("have_permission:create-work-schedule");
        // GET /work-schedule - get all work schedules
        Route::get("/", [WorkScheduleController::class, "getMany"])->middleware("have_permission:view-work-schedule");
        // PATCH /work-schedule/{id} - update a work schedule by id
        Route::put("/{id}", [WorkScheduleController::class, "update"])->middleware(
            "have_permission:update-work-schedule"
        );
        // DELETE /work-schedule/{id} - delete a work schedule by id
        Route::delete("/{id}", [WorkScheduleController::class, "delete"])->middleware(
            "have_permission:delete-work-schedule"
        );
    });

Route::prefix("/supplier")
    ->middleware("authenticated")
    ->group(function () {
        Route::middleware(OnlyStoreAdmin::class)->group(function () {
            // GET /supplier/deleted - get deleted suppliers
            Route::get("/deleted", [SupplierController::class, "getDeleted"]);
            // POST /supplier/{id}/restore - restore a supplier by id
            Route::post("/{id}/restore", [SupplierController::class, "restore"]);
            // DELETE /supplier/{id}/force - force delete a supplier by id
            Route::delete("/{id}/force", [SupplierController::class, "forceDelete"]);
        });
        // POST /supplier - create a new supplier
        Route::post("/", [SupplierController::class, "create"])->middleware("have_permission:create-supplier");
        // GET /supplier - get all suppliers
        Route::get("/", [SupplierController::class, "getMany"])->middleware("have_permission:view-supplier");
        // GET /supplier/{id} - get a supplier by id
        Route::get("/{id}", [SupplierController::class, "getOne"])->middleware("have_permission:view-supplier");
        // PUT /supplier/{id} - update a supplier by id
        Route::put("/{id}", [SupplierController::class, "update"])->middleware("have_permission:update-supplier");
        // DELETE /supplier/{id} - delete a supplier by id
        Route::delete("/{id}", [SupplierController::class, "delete"])->middleware("have_permission:delete-supplier");
    });

Route::prefix("/category")
    ->middleware("authenticated")
    ->group(function () {
        Route::middleware(OnlyStoreAdmin::class)->group(function () {
            // GET /category/deleted - get deleted item categories
            Route::get("/deleted", [CategoryController::class, "getDeleted"]);
            // POST /category/{id}/restore - restore an item category by id
            Route::post("/{id}/restore", [CategoryController::class, "restore"]);
            // DELETE /category/{id}/force - force delete an item category by id
            Route::delete("/{id}/force", [CategoryController::class, "forceDelete"]);
        });
        // POST /category - create a new item category
        Route::post("/", [CategoryController::class, "create"])->middleware("have_permission:create-category");
        // GET /category - get all item categories
        Route::get("/", [CategoryController::class, "getMany"])->middleware("have_permission:view-category");
        // GET /category/{id} - get an item category by id
        Route::get("/{id}", [CategoryController::class, "getOne"])->middleware("have_permission:view-category");
        // PUT /category/{id} - update an item category by id
        Route::put("/{id}", [CategoryController::class, "update"])->middleware("have_permission:update-category");
        // DELETE /category/{id} - delete an item category by id
        Route::delete("/{id}", [CategoryController::class, "delete"])->middleware("have_permission:delete-category");
    });

Route::prefix("/item")
    ->middleware("authenticated")
    ->group(function () {
        Route::middleware(OnlyStoreAdmin::class)->group(function () {
            // GET /item/deleted - get deleted items
            Route::get("/deleted", [ItemController::class, "getDeleted"]);
            // POST /item/{item_id}/restore - restore an item by id
            Route::post("/{id}/restore", [ItemController::class, "restore"]);
            // DELETE /item/{item_id}/force - force delete an item by id
            Route::delete("/{id}/force", [ItemController::class, "forceDelete"]);
        });
        // POST /item - create a new item
        Route::post("/", [ItemController::class, "create"])->middleware("have_permission:create-item");
        // GET /item/one - get item by id or barcode
        Route::get("/one", [ItemController::class, "getOne"])->middleware("have_permission:view-item");
        // GET /item - get all items
        Route::get("/", [ItemController::class, "getMany"])->middleware("have_permission:view-item");
        // GET /item/selling - get all items that are selling
        Route::get("/selling", [ItemController::class, "getSelling"])->middleware("have_permission:view-item");
        // POST /item/move - move an item from default to current
        Route::post("/move", [ItemController::class, "moveItem"])->middleware("have_permission:create-item");
        // PUT /item/{id} - update an item by id
        Route::put("/{id}", [ItemController::class, "update"])->middleware("have_permission:update-item");
        // DELETE /item/{id} - delete an item by id
        Route::delete("/{id}", [ItemController::class, "delete"])->middleware("have_permission:delete-item");
    });

Route::prefix("/purchase-sheet")
    ->middleware(OnlyEmployee::class)
    ->group(function () {
        // POST /purchase-sheet - create a new purchase sheet
        Route::post("/", [PurchaseSheetController::class, "create"])->middleware(
            "have_permission:create-purchase-sheet"
        );
        // GET /purchase-sheet - get all purchase sheets
        Route::get("/", [PurchaseSheetController::class, "getMany"])->middleware("have_permission:view-purchase-sheet");
        // GET /purchase-sheet/{id} - get a purchase sheet by id
        Route::get("/{id}", [PurchaseSheetController::class, "getOne"])->middleware(
            "have_permission:view-purchase-sheet"
        );
        // PUT /purchase-sheet/{id}/note - update a purchase sheet note by id
        Route::put("/{id}/note", [PurchaseSheetController::class, "updateNote"])->middleware(
            "have_permission:update-purchase-sheet"
        );
        // DELETE /purchase-sheet/{id} - delete a purchase sheet by id
        Route::delete("/{id}", [PurchaseSheetController::class, "delete"])->middleware(
            "have_permission:delete-purchase-sheet"
        );
    });

Route::prefix("/quantity-checking-sheet")
    ->middleware(OnlyEmployee::class)
    ->group(function () {
        // POST /quantity-checking-sheet - create a new quantity checking sheet
        Route::post("/", [QuantityCheckingSheetController::class, "create"])->middleware(
            "have_permission:create-quantity-checking-sheet"
        );
        // GET /quantity-checking-sheet - get all quantity checking sheets
        Route::get("/", [QuantityCheckingSheetController::class, "getMany"])->middleware(
            "have_permission:view-quantity-checking-sheet"
        );
        // GET /quantity-checking-sheet/{id} - get a quantity checking sheet by id
        Route::get("/{id}", [QuantityCheckingSheetController::class, "getOne"])->middleware(
            "have_permission:view-quantity-checking-sheet"
        );
        // PUT /quantity-checking-sheet/{id}/note - update a quantity checking sheet note by id
        Route::put("/{id}/note", [QuantityCheckingSheetController::class, "updateNote"])->middleware(
            "have_permission:update-quantity-checking-sheet"
        );
        // DELETE /quantity-checking-sheet/{id} - delete a quantity checking sheet by id
        Route::delete("/{id}", [QuantityCheckingSheetController::class, "delete"])->middleware(
            "have_permission:delete-quantity-checking-sheet"
        );
    });

Route::prefix("/return-purchase-sheet")
    ->middleware([OnlyEmployee::class, HavePurchaseRole::class])
    ->group(function () {
        // POST /return-purchase-sheet - create a new return purchase sheet
        Route::post("/", [ReturnPurchaseSheetController::class, "create"]);
        // GET /return-purchase-sheet - get all return purchase sheets
        Route::get("/", [ReturnPurchaseSheetController::class, "getReturnPurchaseSheets"]);
        // GET /return-purchase-sheet/returnable/{id} - get a returnable purchase sheet by id
        Route::get("/returnable/{id}", [ReturnPurchaseSheetController::class, "getReturnableItems"]);
        // GET /return-purchase-sheet/{id} - get a return purchase sheet by id
        Route::get("/{id}", [ReturnPurchaseSheetController::class, "getReturnPurchaseSheet"]);
        // PATCH /return-purchase-sheet/{id} - update a return purchase sheet by id
        Route::patch("/{id}", [ReturnPurchaseSheetController::class, "update"]);
        // PATCH /return-purchase-sheet/{id}/note - update a return purchase sheet note by id
        Route::patch("/{id}/note", [ReturnPurchaseSheetController::class, "updateNote"]);
        // DELETE /return-purchase-sheet/{id} - delete a return purchase sheet by id
        Route::delete("/{id}", [ReturnPurchaseSheetController::class, "delete"]);
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
        // PATCH /customer/{id} - update a customer by id
        Route::put("/{id}", [CustomerController::class, "update"])->middleware("have_permission:update-customer");
        // POST /customer/add-point/{id} - add point to a customer by id
        Route::post("/add-point/{id}", [CustomerController::class, "addPoint"])->middleware(
            "have_permission:update-customer"
        );
        // POST /customer/use-point/{id} - use point from a customer by id
        Route::post("/use-point/{id}", [CustomerController::class, "usePoint"])->middleware(
            "have_permission:update-customer"
        );
    });

Route::prefix("/default-item")->group(function () {
    // GET /default-item - get all default items
    Route::get("/", [DefaultItemController::class, "getItems"]);
    // GET /default-item/barcode/{barcode} - get a default item by barcode
    Route::get("/one", [DefaultItemController::class, "getItem"]);
});
