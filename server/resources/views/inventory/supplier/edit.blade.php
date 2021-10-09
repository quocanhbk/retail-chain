@extends('main')
@section('breadcrumb')
{{ Breadcrumbs::render('supplier.edit', request()->store_id, request()->branch_id, request()->supplier_id) }}
@endsection
@section('main-content')
<style>
    form{
        width: 70%;
    }
</style>
<div class="container d-flex flex-column align-items-center">
    <form method="post" class="pb-2" novalidate enctype="multipart/form-data" id="change-quant-form">
        <div class="d-flex justify-content-center mb-5">
            <h1>Chỉnh sửa nhà cung cấp #{{$item->id}}</h1>            
        </div>
        @csrf
        <div class="form-group row">
            <label for="name" class="col-sm-3 col-form-label">Tên nhà cung cấp <span style="color: red">*</span></label>
            <div class="col-sm-9">
                <input type="text" class="form-control" id="name" name="name" value="{{ $item->name }}" required>
                <div class="invalid-feedback">
                    Vui lòng điền trường này
                </div>
            </div>
        </div>
        <div class="form-group row">
            <label for="phone" class="col-sm-3 col-form-label">Số điện thoại <span style="color: red">*</span></label>
            <div class="col-sm-9">
                <input type="number" class="form-control" id="phone" name="phone" value="{{ $item->phone }}" required>
                <div class="invalid-feedback">
                    Vui lòng điền trường này
                </div>
            </div>
        </div>
        <div class="form-group row">
            <label for="address" class="col-sm-3 col-form-label">Địa chỉ</label>
            <div class="col-sm-9">
                <input type="text" class="form-control" id="address" name="address" value="{{ $item->address }}">
            </div>
        </div>
        <div class="form-group row">
            <label for="email" class="col-sm-3 col-form-label">Email</label>
            <div class="col-sm-9">
                <input type="text" class="form-control" id="email" name="email" value="{{ $item->email }}">
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
    $('#phone').keydown(function (event){
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