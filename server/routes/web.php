<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
//Login
Route::get('/', function () {
    return view('login');
})->name('login');
Route::post('/', [AuthenticationController::class, 'login'])->name('logout');

//Guest
Route::get('/guest/{branch_id}/item-list', [GuestController::class, 'itemList']);
Route::get('/guest/qr-scanner', [GuestController::class, 'QRScanner']);
//User
Route::prefix('store/{store_id}/branch/{branch_id}')->middleware(['auth', 'user.confirm'])->group(function () {
    Route::prefix('/inventory')->group(function () {
        Route::get('/item', [InventoryController::class, 'item'])->name('inventory.item');

        Route::get('/item/create', [InventoryController::class, 'itemCreate'])->name('inventory.itemCreate');
        Route::post('/item/create', [InventoryController::class, 'itemCreate'])->name('inventory.itemCreate');
        
        Route::get('/item/{item_id}', [InventoryController::class, 'itemDetail'])->name('inventory.itemDetail');

        Route::get('/item/{item_id}/price-history', [InventoryController::class, 'itemPriceHistory'])->name('inventory.itemPriceHistory');

        Route::get('/item/{item_id}/quantity-history', [InventoryController::class, 'itemQuantityHistory'])->name('inventory.itemQuantityHistory');

        Route::get('/item/{item_id}/edit', [InventoryController::class, 'itemEdit'])->name('inventory.itemEdit');
        Route::post('/item/{item_id}/edit', [InventoryController::class, 'itemEdit'])->name('inventory.itemEdit');
        
        Route::get('/item/{item_id}/edit/change-quantity', [InventoryController::class, 'itemChangeQuantity'])->name('inventory.itemChangeQuantity');
        Route::post('/item/{item_id}/edit/change-quantity', [InventoryController::class, 'itemChangeQuantity'])->name('inventory.itemChangeQuantity');
        
        Route::post('/item/{item_id}/delete', [InventoryController::class, 'itemDelete'])->name('inventory.itemDelete');

        Route::get('/item/create/excel', [InventoryController::class, 'itemCreateByExcel'])->name('inventory.itemCreateByExcel');
    });
    
    Route::prefix('/category')->group(function () {
        Route::get('/', [CategoryController::class, 'listCategory'])->name('category.list');

        Route::get('/create', [CategoryController::class, 'createCategory'])->name('category.create');
        Route::post('/create', [CategoryController::class, 'createCategory'])->name('category.create');

        Route::get('/{category_id}/edit', [CategoryController::class, 'editCategory'])->name('category.edit');
        Route::post('/{category_id}/edit', [CategoryController::class, 'editCategory'])->name('category.edit');

        Route::post('/{category_id}/delete', [CategoryController::class, 'deleteCategory'])->name('category.delete');
    });

    Route::prefix('/purchased-sheet')->group(function () {
        Route::get('/', [PurchasedSheetController::class, 'listPurchasedSheet'])->name('purchased_sheet.list');

        Route::get('/{purchased_sheet_id}', [PurchasedSheetController::class, 'detailPurchasedSheet'])->name('purchased_sheet.detail');
    });

    Route::prefix('/return-purchased-sheet')->group(function () {
        Route::get('/', [ReturnPurchasedSheetController::class, 'listReturnPurchasedSheet'])->name('return_purchased_sheet.list');

        Route::get('/{return_purchased_sheet_id}', [ReturnPurchasedSheetController::class, 'detailReturnPurchasedSheet'])->name('return_purchased_sheet.detail');
    });
    
    Route::prefix('/supplier')->group(function () {
        Route::get('/', [SupplierController::class, 'listSupplier'])->name('supplier.list');

        Route::get('/create', [SupplierController::class, 'listSupplier'])->name('supplier.create');

        Route::post('/{supplier_id}/delete', [SupplierController::class, 'deleteSupplier'])->name('supplier.delete');

        Route::get('/{supplier_id}/edit', [SupplierController::class, 'editSupplier'])->name('supplier.edit');
        Route::post('/{supplier_id}/edit', [SupplierController::class, 'editSupplier'])->name('supplier.edit');
        
        Route::get('/create', [SupplierController::class, 'createSupplier'])->name('supplier.create');
        Route::post('/create', [SupplierController::class, 'createSupplier'])->name('supplier.create');
    });

    
    Route::prefix('/import')->group(function () {
        Route::get('/', [ImportController::class, 'index'])->name('import.index');
        Route::post('/', [ImportController::class, 'createPurchasedSheet'])->name('import.create');
        Route::get('/supplier', [ImportController::class, 'supplierList'])->name('import.supplier');
    });
    Route::get('logout', [AuthenticationController::class, 'logout']);
});
Route::get('/main',function () {
    return view('main');
});
Route::get('/privacy-policy',function () {
    return view('policy.privacy');
});
Route::get('/test',function () {
    return view('test');
})->middleware('auth');