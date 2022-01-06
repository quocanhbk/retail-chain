<?php

use App\Http\Controllers\BranchController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\StoreController;
use App\Http\Middleware\Employee\NotEmployee;
use App\Http\Middleware\Employee\OnlyEmployee;
use App\Http\Middleware\Store\NotStoreAdmin;
use App\Http\Middleware\Store\OnlyStoreAdmin;
use Illuminate\Support\Facades\Route;

Route::prefix('/store')->group(function () {
    Route::post('/register', [StoreController::class, 'register'])->middleware([NotStoreAdmin::class]);
    Route::post('/login', [StoreController::class, 'login'])->middleware([NotStoreAdmin::class]);
    Route::post('/logout', [StoreController::class, 'logout'])->middleware([OnlyStoreAdmin::class]);
    Route::get('/me', [StoreController::class, 'getStore'])->middleware([OnlyStoreAdmin::class]);
});

Route::get('/auth', [StoreController::class, 'getGuard']);

Route::prefix('/branch')->middleware([OnlyStoreAdmin::class])->group(function () {
    Route::post('/', [BranchController::class, 'create']);
    Route::get('/', [BranchController::class, 'getBranches']);
    Route::get('/{branch_id}', [BranchController::class, 'getBranch']);
    Route::get('/image/{filePath}', [BranchController::class, 'getBranchImage'])
        ->where(['filePath' => '^([A-z0-9-_+]+\/)*([A-z0-9-\.]+)(\?\d+)?$'])
        ->middleware([OnlyStoreAdmin::class]);
});

Route::prefix('/employee')->group(function () {
    Route::post('/', [EmployeeController::class, 'create'])->middleware([OnlyStoreAdmin::class]);
    Route::get('/', [EmployeeController::class, 'getEmployees'])->middleware([OnlyStoreAdmin::class]);
    Route::get('/me', [EmployeeController::class, 'me'])->middleware([OnlyEmployee::class]);
    Route::post('/login', [EmployeeController::class, 'login'])->middleware([NotStoreAdmin::class, NotEmployee::class]);
    Route::post('/logout', [EmployeeController::class, 'logout'])->middleware([OnlyStoreAdmin::class]);
    Route::get('/{employee_id}', [EmployeeController::class, 'getEmployee'])->middleware([OnlyStoreAdmin::class]);
});

Route::prefix('/shift')->middleware([OnlyStoreAdmin::class])->group(function () {
    Route::post('/', [ShiftController::class, 'create']);
    Route::get('/', [ShiftController::class, 'getShiftes']);
    Route::get('/{name}', [ShiftController::class, 'getShift']);
});
