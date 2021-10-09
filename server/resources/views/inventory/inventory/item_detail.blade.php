@extends('main')
@section('breadcrumb')
{{ Breadcrumbs::render('inventory.itemDetail', request()->store_id, request()->branch_id, request()->item_id) }}
@endsection
@section('main-content')
<style>
    form{
        width: 70%;
    }
    .product-img{
        width: 200px;
        height: 200px;
    }
</style>
<div class="container d-flex flex-column align-items-center">
    <form method="post" class="pb-2" novalidate enctype="multipart/form-data">
        @csrf
        <div class="form-group d-flex flex-column align-items-center">
            <img src="{{ $item->image_url? asset($item->image_url) : asset('img/no-image.jpg') }}" class="img-thumbnail product-img" id="img_preview"><br>
        </div>
        <div class="form-group row">
            <label for="item_name" class="col-sm-3 col-form-label">Tên sản phẩm <span style="color: red">*</span></label>
            <div class="col-sm-9">
                <input type="text" class="form-control" id="item_name" name="item_name" value="{{ $item->name }}" readonly>
            </div>
        </div>
        <div class="form-group row">
            <label for="category_id" class="col-sm-3 col-form-label">Danh mục <span style="color: red">*</span></label>
            <div class="col-sm-9">
                <select class="form-control" id="category_id" name="category_id" disabled>
                    @foreach ($category_list as $category)
                        @if ($category->id == $item->category_id)
                            <option value="{{ $category->id }}" selected>{{ $category->name }}</option>
                        @else
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endif
                    @endforeach
                </select>
            </div>
        </div>
        <div class="form-group row">
            <label for="sell_price" class="col-sm-3 col-form-label number">Đơn giá <span style="color: red">*</span></label>
            <div class="col-sm-9">
                <input type="number" class="form-control" id="sell_price" name="sell_price" value="{{ $item->sell_price }}" readonly>
            </div>
        </div>
        <div class="form-group row">
            <label for="quantity" class="col-sm-3 col-form-label">Số lượng hàng <span style="color: red">*</span></label>
            <div class="col-sm-9">
                <input type="number" class="form-control" id="quantity" name="quantity" value="{{ $item->quantity }}" readonly>
            </div>
        </div>
        <div class="form-group row">
            <label for="purchase_price" class="col-sm-3 col-form-label">Vốn <span style="color: red">*</span></label>
            <div class="col-sm-9">
                <input type="number" class="form-control" id="purchase_price" name="purchase_price" value="{{ $item->purchase_price }}" readonly>
            </div>
        </div>
        <div class="form-group row">
            <label for="bar_code" class="col-sm-3 col-form-label">Mã vạch</label>
            <div class="col-sm-9">
                <input type="number" class="form-control" id="bar_code" name="bar_code"  value="{{ $item->bar_code }}" readonly>
            </div>
        </div>
        <div class="form-group row">
            <label for="point_ratio" class="col-sm-3 col-form-label">Tỉ lệ tích điểm</label>
            <div class="col-sm-9">
                <input type="number" class="form-control" id="point_ratio" name="point_ratio"  value="{{ round($item->point_ratio,2) }}" readonly>
            </div>
        </div>
        <div class="d-flex justify-content-center">
            <a class="btn btn-info mx-5 col-3" href="{{ route('inventory.itemEdit', ['store_id' => request()->store_id, 'branch_id' => request()->branch_id, 'item_id' => request()->item_id]) }}">
                Chỉnh sửa
            </a>
            <a class="btn btn-danger mx-5 col-3" href="#">Xóa</a>
        </div>
        <div class="d-flex justify-content-center mt-4">
            <a class="btn btn-primary mx-5 col-3" href="{{ route('inventory.itemPriceHistory', ['store_id' => request()->store_id, 'branch_id' => request()->branch_id, 'item_id' => request()->item_id]) }}">
                Lịch sử giá
            </a>
            <a class="btn btn-primary mx-5 col-3" href="{{ route('inventory.itemQuantityHistory', ['store_id' => request()->store_id, 'branch_id' => request()->branch_id, 'item_id' => request()->item_id]) }}">
                Lịch sử kiểm kê
            </a>
        </div>
    </form>
</div>
<script>
</script>
@endsection