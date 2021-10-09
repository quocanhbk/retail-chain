<?php // routes/breadcrumbs.php

// Note: Laravel will automatically resolve `Breadcrumbs::` without
// this import. This is nice for IDE syntax and refactoring.
use Diglactic\Breadcrumbs\Breadcrumbs;

// This import is also not required, and you could replace `BreadcrumbTrail $trail`
//  with `$trail`. This is nice for IDE type checking and completion.
use Diglactic\Breadcrumbs\Generator as BreadcrumbTrail;
use Request as Request; 

// Kho hàng
Breadcrumbs::for('inventory.item', function (BreadcrumbTrail $trail, $store_id, $branch_id) {
    $url_param = [
        'store_id' => $store_id,
        'branch_id' => $branch_id,
    ];
    $trail->push('Kho hàng', route('inventory.item', $url_param));
});

// Kho hàng > Thông tin chi tiết sản phẩm
Breadcrumbs::for('inventory.itemDetail', function (BreadcrumbTrail $trail, $store_id, $branch_id, $item_id) {
    $url_param = [
        'store_id' => $store_id,
        'branch_id' => $branch_id,
        'item_id' => $item_id,
    ];
    $trail->parent('inventory.item', $store_id, $branch_id);
    $trail->push('Thông tin chi tiết sản phẩm', route('inventory.itemDetail', $url_param));
});

// Kho hàng > Thông tin chi tiết sản phẩm > Chỉnh sửa sản phẩm
Breadcrumbs::for('inventory.itemEdit', function (BreadcrumbTrail $trail, $store_id, $branch_id, $item_id) {
    $url_param = [
        'store_id' => $store_id,
        'branch_id' => $branch_id,
        'item_id' => $item_id,
    ];
    $trail->parent('inventory.itemDetail', $store_id, $branch_id, $item_id);
    $trail->push('Chỉnh sửa sản phẩm', route('inventory.itemEdit', $url_param));
});

// Kho hàng > Thông tin chi tiết sản phẩm > Lịch sử giá
Breadcrumbs::for('inventory.itemPriceHistory', function (BreadcrumbTrail $trail, $store_id, $branch_id, $item_id) {
    $url_param = [
        'store_id' => $store_id,
        'branch_id' => $branch_id,
        'item_id' => $item_id,
    ];
    $trail->parent('inventory.itemDetail', $store_id, $branch_id, $item_id);
    $trail->push('Lịch sử giá', route('inventory.itemPriceHistory', $url_param));
});

// Kho hàng > Thông tin chi tiết sản phẩm > Lịch sử kiểm kê
Breadcrumbs::for('inventory.itemQuantityHistory', function (BreadcrumbTrail $trail, $store_id, $branch_id, $item_id) {
    $url_param = [
        'store_id' => $store_id,
        'branch_id' => $branch_id,
        'item_id' => $item_id,
    ];
    $trail->parent('inventory.itemDetail', $store_id, $branch_id, $item_id);
    $trail->push('Lịch sử kiểm kê', route('inventory.itemQuantityHistory', $url_param));
});

// Kho hàng > Thông tin chi tiết sản phẩm > Chỉnh sửa sản phẩm > Kiểm kê số lượng hàng
Breadcrumbs::for('inventory.itemChangeQuantity', function (BreadcrumbTrail $trail, $store_id, $branch_id, $item_id) {
    $url_param = [
        'store_id' => $store_id,
        'branch_id' => $branch_id,
        'item_id' => $item_id,
    ];
    $trail->parent('inventory.itemEdit', $store_id, $branch_id, $item_id);
    $trail->push('Kiểm kê số lượng hàng', route('inventory.itemChangeQuantity', $url_param));
});

// Kho hàng > Thêm sản phẩm
Breadcrumbs::for('inventory.itemCreate', function (BreadcrumbTrail $trail, $store_id, $branch_id) {
    $url_param = [
        'store_id' => $store_id,
        'branch_id' => $branch_id,
    ];
    $trail->parent('inventory.item', $store_id, $branch_id);
    $trail->push('Thêm sản phẩm', route('inventory.itemCreate', $url_param));
});

// Kho hàng > Thêm sản phẩm > Thêm sản phẩm bằng File Excel
Breadcrumbs::for('inventory.itemCreateByExcel', function (BreadcrumbTrail $trail, $store_id, $branch_id) {
    $url_param = [
        'store_id' => $store_id,
        'branch_id' => $branch_id,
    ];
    $trail->parent('inventory.itemCreate', $store_id, $branch_id);
    $trail->push('Thêm sản phẩm bằng File Excel', route('inventory.itemCreateByExcel', $url_param));
});

// Danh mục
Breadcrumbs::for('category.list', function (BreadcrumbTrail $trail, $store_id, $branch_id) {
    $url_param = [
        'store_id' => $store_id,
        'branch_id' => $branch_id,
    ];
    $trail->push('Danh mục', route('category.list', $url_param));
});

// Danh mục > Chỉnh sửa danh mục
Breadcrumbs::for('category.edit', function (BreadcrumbTrail $trail, $store_id, $branch_id, $category_id) {
    $url_param = [
        'store_id' => $store_id,
        'branch_id' => $branch_id,
        'category_id' => $category_id,
    ];
    $trail->parent('category.list', $store_id, $branch_id);
    $trail->push('Chỉnh sửa danh mục', route('category.edit', $url_param));
});

// Danh mục > Thêm danh mục
Breadcrumbs::for('category.create', function (BreadcrumbTrail $trail, $store_id, $branch_id) {
    $url_param = [
        'store_id' => $store_id,
        'branch_id' => $branch_id,
    ];
    $trail->parent('category.list', $store_id, $branch_id);
    $trail->push('Thêm danh mục', route('category.create', $url_param));
});

// Đơn nhập hàng
Breadcrumbs::for('purchased_sheet.list', function (BreadcrumbTrail $trail, $store_id, $branch_id) {
    $url_param = [
        'store_id' => $store_id,
        'branch_id' => $branch_id,
    ];
    $trail->push('Đơn nhập hàng', route('purchased_sheet.list', $url_param));
});

// Đơn nhập hàng > Chi tiết đơn nhập hàng
Breadcrumbs::for('purchased_sheet.detail', function (BreadcrumbTrail $trail, $store_id, $branch_id, $purchased_sheet_id) {
    $url_param = [
        'store_id' => $store_id,
        'branch_id' => $branch_id,
        'purchased_sheet_id' => $purchased_sheet_id,
    ];
    $trail->parent('purchased_sheet.list', $store_id, $branch_id);
    $trail->push('Chi tiết đơn nhập hàng', route('purchased_sheet.detail', $url_param));
});

// Đơn nhập hàng
Breadcrumbs::for('return_purchased_sheet.list', function (BreadcrumbTrail $trail, $store_id, $branch_id) {
    $url_param = [
        'store_id' => $store_id,
        'branch_id' => $branch_id,
    ];
    $trail->push('Đơn trả hàng nhập', route('return_purchased_sheet.list', $url_param));
});

// Đơn nhập hàng > Chi tiết đơn nhập hàng
Breadcrumbs::for('return_purchased_sheet.detail', function (BreadcrumbTrail $trail, $store_id, $branch_id, $return_purchased_sheet_id) {
    $url_param = [
        'store_id' => $store_id,
        'branch_id' => $branch_id,
        'return_purchased_sheet_id' => $return_purchased_sheet_id,
    ];
    $trail->parent('return_purchased_sheet.list', $store_id, $branch_id);
    $trail->push('Chi tiết đơn trả hàng nhập', route('return_purchased_sheet.detail', $url_param));
});

// Nhà cung cấp
Breadcrumbs::for('supplier.list', function (BreadcrumbTrail $trail, $store_id, $branch_id) {
    $url_param = [
        'store_id' => $store_id,
        'branch_id' => $branch_id,
    ];
    $trail->push('Nhà cung cấp', route('supplier.list', $url_param));
});

// Nhà cung cấp > Chỉnh sửa nhà cung cấp
Breadcrumbs::for('supplier.edit', function (BreadcrumbTrail $trail, $store_id, $branch_id, $supplier_id) {
    $url_param = [
        'store_id' => $store_id,
        'branch_id' => $branch_id,
        'supplier_id' => $supplier_id,
    ];
    $trail->parent('supplier.list', $store_id, $branch_id);
    $trail->push('Chỉnh sửa nhà cung cấp', route('supplier.edit', $url_param));
});

// Nhà cung cấp > Thêm nhà cung cấp mới
Breadcrumbs::for('supplier.create', function (BreadcrumbTrail $trail, $store_id, $branch_id) {
    $url_param = [
        'store_id' => $store_id,
        'branch_id' => $branch_id,
    ];
    $trail->parent('supplier.list', $store_id, $branch_id);
    $trail->push('Thêm nhà cung cấp mới', route('supplier.create', $url_param));
});

// Nhập hàng
Breadcrumbs::for('import.index', function (BreadcrumbTrail $trail, $store_id, $branch_id) {
    $url_param = [
        'store_id' => $store_id,
        'branch_id' => $branch_id,
    ];
    $trail->push('Nhập hàng', route('import.index', $url_param));
});
// Home > Blog > [Category]
// Breadcrumbs::for('category', function (BreadcrumbTrail $trail, $category) {
//     $trail->parent('blog');
//     $trail->push($category->title, route('category', $category));
// });
