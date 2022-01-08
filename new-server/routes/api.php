<?php

namespace App\Http\Controllers;

use App\Http\Middleware\AdminOrPurchaser;
use App\Http\Middleware\Employee\HaveManageRole;
use App\Http\Middleware\Employee\NotEmployee;
use App\Http\Middleware\Employee\OnlyEmployee;
use App\Http\Middleware\Store\NotStoreAdmin;
use App\Http\Middleware\Store\OnlyStoreAdmin;
use Illuminate\Support\Facades\Route;

Route::prefix('/store')->group(function () {
    // POST /store/register - register a new store
    Route::post('/register', [StoreController::class, 'register'])->middleware([NotStoreAdmin::class]);
    // POST /store/login - login as store admin
    Route::post('/login', [StoreController::class, 'login'])->middleware([NotStoreAdmin::class]);
    // GET /store/logout - logout as store admin
    Route::post('/logout', [StoreController::class, 'logout'])->middleware([OnlyStoreAdmin::class]);
    // GET /store/me - get store info
    Route::get('/me', [StoreController::class, 'getStore'])->middleware([OnlyStoreAdmin::class]);
});

// GET /auth - get auth info
Route::get('/auth', [StoreController::class, 'getGuard']);

Route::prefix('/branch')->middleware([OnlyStoreAdmin::class])->group(function () {
    // POST /branch - create a new branch
    Route::post('/', [BranchController::class, 'create']);
    // GET /branch - get all branches
    Route::get('/', [BranchController::class, 'getBranches']);
    // GET /branch/{branch_id} - get a branch by id
    Route::get('/{branch_id}', [BranchController::class, 'getBranch']);
    // PATCH /branch/{branch_id} - update a branch by id
    Route::patch('/{branch_id}', [BranchController::class, 'update']);
    // GET /branch/image/{filePath} - get a branch image
    Route::get('/image/{filePath}', [BranchController::class, 'getBranchImage'])
        ->where(['filePath' => '^([A-z0-9-_+]+\/)*([A-z0-9-\.]+)(\?\d+)?$']);
});

Route::prefix('/employee')->group(function () {
    // POST /employee - create a new employee
    Route::post('/', [EmployeeController::class, 'create'])->middleware([OnlyStoreAdmin::class]);
    // GET /employee - get all employees
    Route::get('/', [EmployeeController::class, 'getEmployees'])->middleware([OnlyStoreAdmin::class]);
    // GET /employee/me - get current employee info
    Route::get('/me', [EmployeeController::class, 'me'])->middleware([OnlyEmployee::class]);
    // POST /employee/login - login as employee
    Route::post('/login', [EmployeeController::class, 'login'])->middleware([NotStoreAdmin::class, NotEmployee::class]);
    // POST /employee/logout - logout as employee
    Route::post('/logout', [EmployeeController::class, 'logout'])->middleware([OnlyStoreAdmin::class]);
    // GET /employee/{employee_id} - get an employee by id
    Route::get('/{employee_id}', [EmployeeController::class, 'getEmployee'])->middleware([OnlyStoreAdmin::class]);
    // POST /employee/transfer - transfer an employee to another branch
    Route::post('/transfer', [EmployeeController::class, 'transfer'])->middleware([OnlyStoreAdmin::class]);
});

Route::prefix('/shift')->middleware([HaveManageRole::class])->group(function () {
    // POST /shift - create a new shift
    Route::post('/', [ShiftController::class, 'create']);
    // GET /shift - get all shifts
    Route::get('/', [ShiftController::class, 'getShifts']);
    // GET /shift/{shift_id} - get a shift by id
    Route::get('/{shift_id}', [ShiftController::class, 'getShift']);
    // POST /shift/deactivate/{shift_id} - deactivate a shift by id
    Route::post('/deactivate/{shift_id}', [ShiftController::class, 'deactivate']);
});

Route::prefix('/work-schedule')->middleware([HaveManageRole::class])->group(function () {
    // POST /work-schedule - create a new work schedule
    Route::post('/', [WorkScheduleController::class, 'create']);
    // GET /work-schedule - get all work schedules
    Route::get('/', [WorkScheduleController::class, 'getWorkSchedules']);
    // GET /work-schedule/{date} - get all work schedules in a day
    Route::get('/{date}', [WorkScheduleController::class, 'getWorkSchedulesByDate']);
    // PATCH /work-schedule/{work_schedule_id} - update a work schedule by id
    Route::patch('/{work_schedule_id}', [WorkScheduleController::class, 'update']);
    // DELETE /work-schedule/{work_schedule_id} - delete a work schedule by id
    Route::delete('/{work_schedule_id}', [WorkScheduleController::class, 'delete']);
});

Route::prefix('/supplier')->middleware([AdminOrPurchaser::class])->group(function () {
    // POST /supplier - create a new supplier
    Route::post('/', [SupplierController::class, 'create'])->middleware([OnlyStoreAdmin::class]);
    // GET /supplier - get all suppliers
    Route::get('/', [SupplierController::class, 'getSuppliers']);
    // GET /supplier/{supplier_id} - get a supplier by id
    Route::get('/{supplier_id}', [SupplierController::class, 'getSupplier']);
    // PATCH /supplier/{supplier_id} - update a supplier by id
    Route::patch('/{supplier_id}', [SupplierController::class, 'update']);
    // DELETE /supplier/{supplier_id} - delete a supplier by id
    Route::delete('/{supplier_id}', [SupplierController::class, 'delete']);
});
