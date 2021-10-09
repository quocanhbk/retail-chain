@extends('main')
@section('breadcrumb')
{{ Breadcrumbs::render('category.edit', request()->store_id, request()->branch_id, request()->category_id) }}
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
    <form method="post" class="pb-2" novalidate enctype="multipart/form-data" id="change-quant-form">
        <div class="d-flex justify-content-center mb-5">
            <h1>Chỉnh sửa danh mục #{{$item->id}}</h1>            
        </div>
        @csrf
        <div class="form-group row">
            <label for="category_name" class="col-sm-3 col-form-label">Tên danh mục <span style="color: red">*</span></label>
            <div class="col-sm-9">
                <input type="text" class="form-control" id="category_name" name="category_name" value="{{ $item->name }}" required>
                <div class="invalid-feedback">
                    Vui lòng điền trường này
                </div>
            </div>
        </div>
        <div class="form-group row">
            <label for="point_ratio" class="col-sm-3 col-form-label">Tỉ lệ tích điểm (%) <span style="color: red">*</span></label>
            <div class="col-sm-9">
                <input type="number" class="form-control" id="point_ratio" name="point_ratio" value="{{ $item->point_ratio }}" required>
                <div class="invalid-feedback">
                    Vui lòng điền trường này
                </div>
            </div>
        </div>
        <div class="d-flex justify-content-center">
            <button class="btn btn-success mx-5 col-3" type="submit">Hoàn thành</button>
        </div>
    </form>
</div>
<script>
    //prevent some key
    var invalid_key = ["+","-","e"];
    $('#point_ratio').keydown(function (event){
        if (invalid_key.includes(event.key)) {
            event.preventDefault();
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