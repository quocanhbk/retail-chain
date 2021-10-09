@extends('main')
@section('breadcrumb')
{{ Breadcrumbs::render('inventory.itemChangeQuantity', request()->store_id, request()->branch_id, request()->item_id) }}
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
            <h1>Kiểm kê số lượng hàng</h1>            
        </div>
        @csrf
        <div class="form-group row">
            <label for="old_quant" class="col-sm-3 col-form-label">Số lượng cũ</label>
            <div class="col-sm-9">
                <input type="number" class="form-control" id="old_quant" name="old_quant" value="{{ $item->quantity }}" required readonly>
            </div>
        </div>
        <div class="form-group row">
            <label for="new_quant" class="col-sm-3 col-form-label">Số lượng mới <span style="color: red">*</span></label>
            <div class="col-sm-9">
                <input type="number" class="form-control" id="new_quant" name="new_quant" required>
                <div class="invalid-feedback">
                    Vui lòng điền trường này
                </div>
            </div>
        </div>
        <div class="form-group row">
            <label for="reason" class="col-sm-3 col-form-label">Lý do</label>
            <div class="col-sm-9">
                <textarea class="form-control" id="reason" name="reason" rows="3"></textarea>
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
    $('#new_quant').keydown(function (event){
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