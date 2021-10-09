@extends('main')
@section('breadcrumb')
{{ Breadcrumbs::render('inventory.itemCreate', request()->store_id, request()->branch_id) }}
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
    <form method="post" class="border-bottom pb-2" novalidate enctype="multipart/form-data">
        @csrf
        <div class="form-group d-flex flex-column align-items-center">
            <img src="{{ asset('img/no-image.jpg') }}" class="img-thumbnail product-img" id="img_preview"><br>
            <input type="file" id="image" name="image" accept="image/*">
        </div>
        <div class="form-group row">
            <label for="item_name" class="col-sm-3 col-form-label">Tên sản phẩm <span style="color: red">*</span></label>
            <div class="col-sm-9">
                <input type="text" class="form-control" id="item_name" name="item_name" required>
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
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="form-group row">
            <label for="sell_price" class="col-sm-3 col-form-label number">Đơn giá <span style="color: red">*</span></label>
            <div class="col-sm-9">
                <input type="number" class="form-control" id="sell_price" name="sell_price" required>
                <div class="invalid-feedback">
                    Vui lòng điền trường này
                </div>
            </div>
        </div>
        <div class="form-group row">
            <label for="quantity" class="col-sm-3 col-form-label">Số lượng hàng <span style="color: red">*</span></label>
            <div class="col-sm-9">
                <input type="number" class="form-control" id="quantity" name="quantity" required>
                <div class="invalid-feedback">
                    Vui lòng điền trường này
                </div>
            </div>
        </div>
        <div class="form-group row">
            <label for="purchase_price" class="col-sm-3 col-form-label">Vốn <span style="color: red">*</span></label>
            <div class="col-sm-9">
                <input type="number" class="form-control" id="purchase_price" name="purchase_price" required>
                <div class="invalid-feedback">
                    Vui lòng điền trường này
                </div>
            </div>
        </div>
        <div class="form-group row">
            <label for="bar_code" class="col-sm-3 col-form-label">Mã vạch</label>
            <div class="col-sm-6">
                <input type="number" class="form-control" id="bar_code" name="bar_code">
            </div>
            <div class="col-sm-3 alert alert-warning">
                Mã vạch sẽ được tạo tự động nếu bỏ trống
            </div>
        </div>
        <div class="d-flex justify-content-center">
            <button class="btn btn-success mx-5 col-3" type="submit">Hoàn thành</button>
            <a class="btn btn-danger mx-5 col-3" href="{{ route('inventory.item', ['store_id' => request()->store_id, 'branch_id' => request()->branch_id]) }}">Hủy</a>
        </div>
    </form>
    {{-- <div class="w-100 text-center">Hoặc</div>
    <a class="btn btn-primary mt-2 col-3" href="{{ route('inventory.itemCreateByExcel', ['store_id' => request()->store_id, 'branch_id' => request()->branch_id]) }}">Thêm sản phẩm bằng File Excel</a> --}}
</div>
<script>
    //prevent some key
    var invalid_key = ["+","-","e"];
    var number_format = new Intl.NumberFormat('en-US', {style: 'decimal'});
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