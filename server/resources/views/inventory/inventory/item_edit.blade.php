@extends('main')
@section('breadcrumb')
{{ Breadcrumbs::render('inventory.itemEdit', request()->store_id, request()->branch_id, request()->item_id) }}
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
            <input type="file" id="image" name="image" accept="image/*">
        </div>
        <div class="form-group row">
            <label for="item_name" class="col-sm-3 col-form-label">Tên sản phẩm <span style="color: red">*</span></label>
            <div class="col-sm-9">
                <input type="text" class="form-control" id="item_name" name="item_name" value="{{ $item->name }}" required>
                <div class="invalid-feedback">
                    Vui lòng điền trường này
                </div>
            </div>
        </div>
        <div class="form-group row">
            <label for="category_id" class="col-sm-3 col-form-label">Danh mục <span style="color: red">*</span></label>
            <div class="col-sm-9">
                <select class="form-control" id="category_id" name="category_id">
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
                <input type="number" class="form-control" id="sell_price" name="sell_price" value="{{ $item->sell_price }}" required>
                <div class="invalid-feedback">
                    Vui lòng điền trường này
                </div>
            </div>
        </div>
        <div class="form-group row">
            <label for="quantity" class="col-sm-3 col-form-label">Số lượng hàng <span style="color: red">*</span></label>
            <div class="col-sm-9">
                <input type="number" class="form-control" id="quantity" name="quantity" value="{{ $item->quantity }}" required readonly>
                <div class="invalid-feedback">
                    Vui lòng điền trường này
                </div>
            </div>
        </div>
        <div class="form-group row">
            <label for="purchase_price" class="col-sm-3 col-form-label">Vốn <span style="color: red">*</span></label>
            <div class="col-sm-9">
                <input type="number" class="form-control" id="purchase_price" name="purchase_price" value="{{ $item->purchase_price }}" required>
                <div class="invalid-feedback">
                    Vui lòng điền trường này
                </div>
            </div>
        </div>
        <div class="form-group row">
            <label for="bar_code" class="col-sm-3 col-form-label">Mã vạch</label>
            <div class="col-sm-9">
                <input type="number" class="form-control" id="bar_code" name="bar_code"  value="{{ $item->bar_code }}">
            </div>
        </div>
        <div class="form-group row">
            <label for="point_ratio" class="col-sm-3 col-form-label">Tỉ lệ tích điểm</label>
            <div class="col-sm-9">
                <input type="number" class="form-control" id="point_ratio" name="point_ratio"  value="{{ round($item->point_ratio,2) }}">
            </div>
        </div>
        <div class="d-flex justify-content-center">
            <button class="btn btn-success mx-5 col-3" type="submit">Hoàn thành</button>
            <a class="btn btn-info mx-5 col-3" href="{{ route('inventory.itemChangeQuantity', ['store_id' => request()->store_id, 'branch_id' => request()->branch_id, 'item_id' => request()->item_id]) }}">Chỉnh sửa số lượng</a>
        </div>
    </form>
</div>
<script>
    //prevent some key
    var invalid_key = ["+","-","e"];
    $('#sell_price, #quantity, #purcharse_price').keydown(function (event){
        if (invalid_key.includes(event.key)) {
            event.preventDefault();
        }
    });
    //preview img
    $('#product_image').change(function () {
        img_file = $('#product_image')[0].files[0];
        if (img_file) {
            $('#img_preview').attr("src", URL.createObjectURL(img_file));
        }
        
    });

    // Example starter JavaScript for disabling form submissions if there are invalid fields
    (function() {
        'use strict';
        window.addEventListener('load', function() {
            // Fetch all the forms we want to apply custom Bootstrap validation styles to
            var forms = $('form');
            // Loop over them and prevent submission
            var validation = Array.prototype.filter.call(forms, function(form) {
            form.addEventListener('submit', function(event) {
                if (form.checkValidity() === false) {
                event.preventDefault();
                event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
            });
        }, false);
    })();
</script>
@endsection