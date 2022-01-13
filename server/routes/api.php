<?php

namespace App\Http\Controllers\New;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::middleware(['jwt.confirm'])->group(function () {
    Route::get('/me', [User::class, 'getStaffInfo']);
    Route::get('/me/admin', [User::class, 'getAdminInfo']);
    Route::patch('/me', [User::class, 'updateCurrentUserInfo']);
    Route::patch('/me/password', [User::class, 'changePassword']);

    Route::middleware(['only-owner'])->group(function() {
        Route::post('/employee', [Employee::class, 'create']);
    });

    Route::prefix('/store/{store_id}/branch/{branch_id}')->group(function () {
        Route::middleware(['branch.confirm', 'work.confirm'])->group(function () {
            Route::middleware(['owner.confirm'])->group(function () {
                Route::post('/employee/create', [EmployeeController::class, 'createEmployee']);
                // Route::post('/employee/edit-role', [EmployeeController::class, 'changeEmployeeRole']);
                Route::post('/employee/reset-password', [EmployeeController::class, 'resetEmployeePassword']);
                Route::post('/employee/edit-info', [EmployeeController::class, 'changeEmployeeInfo']);
                Route::post('/employee/change-status', [EmployeeController::class, 'changeEmployeeStatus']);
                Route::get('/history', [EmployeeController::class, 'getActionHistory']);

                Route::post('/store/edit', [StoreController::class, 'editStore']);

                Route::post('/branch/edit', [BranchController::class, 'editBranch']);

                Route::post('/shift/create', [ShiftController::class, 'createShift']);
                Route::post('/shift/edit', [ShiftController::class, 'editShift']);

            });

            Route::get('dashboard/get',[DashboardController::class, 'getDashboard']);

            Route::post('customer/create',[CustomerController::class, 'createCustomer']);
            Route::post('customer/edit',[CustomerController::class, 'editCustomer']);
            Route::get('customer/get',[CustomerController::class, 'getCustomer']);

            Route::post('supplier/create',[SupplierController::class, 'createSupplier']);
            Route::post('supplier/edit',[SupplierController::class, 'editSupplier']);
            Route::get('supplier/get',[SupplierController::class, 'getSupplier']);

            Route::post('category/create',[ItemCategoryController::class, 'createCategory']);
            Route::post('category/edit',[ItemCategoryController::class, 'editCategory']);
            Route::get('category/get',[ItemCategoryController::class, 'getCategory']);

            Route::post('item/create',[ItemController::class, 'createItem']);
            Route::post('item/edit',[ItemController::class, 'editItem']);
            Route::post('item/delete',[ItemController::class, 'deleteItem']);
            Route::get('item/get',[ItemController::class, 'getItem']);
            Route::get('item/search',[ItemController::class, 'searchItemByBarcode']);
            Route::get('item/price-history',[ItemPriceController::class, 'getItemPriceHistory']);
            Route::get('item/quantity-history',[QuantityCheckingSheetController::class, 'getQuantChangeHistoryOfItem']);
            Route::get('item/check-purchase-price',[ItemController::class, 'checkItemWithZeroOrNullPurchasePrice']);

            Route::get('defaultitem/search',[DefaultItemController::class, 'searchItemByBarcode']);

            Route::post('invoice/create',[InvoiceController::class, 'createInvoice']);
            Route::get('invoice/get',[InvoiceController::class, 'getInvoice']);
            Route::get('invoice/detail',[InvoiceController::class, 'getInvoiceDetail']);

            Route::post('refund-sheet/create',[RefundSheetController::class, 'createRefundSheet']);
            Route::get('refund-sheet/get',[RefundSheetController::class, 'getRefundSheet']);
            Route::get('refund-sheet/detail',[RefundSheetController::class, 'getRefundSheetDetail']);


            Route::post('purchased-sheet/create',[PurchasedSheetController::class, 'createPurchasedSheet']);
            Route::get('purchased-sheet/get',[PurchasedSheetController::class, 'getPurchasedSheet']);
            Route::get('purchased-sheet/detail',[PurchasedSheetController::class, 'getPurchasedSheetDetail']);

            Route::post('return-purchased-sheet/create',[ReturnPurchasedSheetController::class, 'createReturnPurchasedSheet']);
            Route::get('return-purchased-sheet/get',[ReturnPurchasedSheetController::class, 'getReturnPurchasedSheet']);
            Route::get('return-purchased-sheet/detail',[ReturnPurchasedSheetController::class, 'getReturnPurchasedSheetDetail']);

            Route::post('quantity-checking-sheet/create',[QuantityCheckingSheetController::class, 'createQuantCheckingSheet']);


            Route::get('/shift/get', [ShiftController::class, 'getShift']);

            Route::post('/schedule/create', [ScheduleController::class, 'createSchedule']);
            Route::get('/schedule/get', [ScheduleController::class, 'getEmployeeSchedule']);
            Route::post('/schedule/delete', [ScheduleController::class, 'deleteSchedule']);

            Route::post('/attendance/create', [AttendanceController::class, 'createAttendance']);
            Route::get('/attendance/get', [AttendanceController::class, 'getEmployeeToCheckList']);
            Route::get('/attendance/detail', [AttendanceController::class, 'getEmployeeAttendanceList']);

            Route::get('/employee/get', [EmployeeController::class, 'getEmployee']);

            Route::get('/user/get', [UserController::class, 'getCurrentUserInfo']);
            Route::post('/user/edit', [UserController::class, 'updateCurrentUserInfo']);
            Route::post('/user/change-pass', [UserController::class, 'changePassword']);

            Route::get('/report/revenue', [ReportController::class, 'getRevenue']);
            Route::get('/report/item', [ReportController::class, 'getReportItems']);
            Route::get('/report/category', [ReportController::class, 'getReportCategories']);
            Route::get('/report/customer', [ReportController::class, 'getReportCustomer']);
            Route::get('/report/supplier', [ReportController::class, 'getReportSupplier']);
            Route::get('/role/get', [RoleController::class, 'getRole']);

        });
    });
});
