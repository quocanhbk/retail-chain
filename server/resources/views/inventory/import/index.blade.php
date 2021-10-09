@extends('main')
@section('breadcrumb')
{{ Breadcrumbs::render('import.index', request()->store_id, request()->branch_id) }}
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
    table thead th{
        text-align: center;
    }
    .img-size{
        width: 60px;
        height: 60px;
    }
</style>
<div class="container d-flex flex-column align-items-center">
    <div class="d-flex justify-content-center mb-5">
        <h1>Tạo đơn nhập hàng</h1>         
    </div>
    <form method="post" novalidate id="purchased_sheet_form">
        @csrf
        <div class="form-group row">
            <label for="supplier_name" class="col-sm-3 col-form-label">Nhà cung cấp</label>
            <div class="col-sm-9 form-inline">
                <h4><span class="badge badge-primary" id="supplier_name">Nhà cung cấp lẻ</span></h4>
                <input type="hidden" name="supplier_id" id="supplier_id">
                <button type="button" class="btn btn-info mx-2" data-toggle="modal" data-target="#supplier_modal" id="supplier_modal_btn"><i class="fas fa-edit"></i> Chỉnh sửa</button>
                <button type="button" class="btn btn-danger" onclick="deleteSupplier()"><i class="fas fa-trash-alt"></i> Xóa</button>
            </div>
        </div>
        <div class="form-group row">
            <label for="purchaser_name" class="col-sm-3 col-form-label">Người nhập hàng</label>
            <div class="col-sm-9">
                <input type="text" class="form-control" id="purchaser_name" name="purchaser_name" value="{{Auth::user()->name}}" readonly>
            </div>
        </div>
        <div class="form-group row">
            <label for="deliver_name" class="col-sm-3 col-form-label">Người giao hàng</label>
            <div class="col-sm-9">
                <input type="text" class="form-control" id="deliver_name" name="deliver_name">
            </div>
        </div>
        <div class="form-group row">
            <label for="total_price" class="col-sm-3 col-form-label">Tổng tiền hàng</label>
            <div class="col-sm-9">
                <input type="text" class="form-control" id="total_price" name="total_price" readonly value="0">
            </div>
        </div>
        <div class="form-group row">
            <label for="discount" class="col-sm-3 col-form-label">Số tiền được giảm</label>
            <div class="col-sm-9">
                <input type="text" class="form-control" id="discount" name="discount" value="0">
            </div>
        </div>
        <div class="form-group row">
            <label for="total_price_after_discount" class="col-sm-3 col-form-label">Cần trả</label>
            <div class="col-sm-9">
                <input type="text" class="form-control" id="total_price_after_discount" name="total_price_after_discount" readonly value="0">
            </div>
        </div>
    </form>
    <h3>Danh sách sản phẩm nhập</h3>
    <div class="alert alert-danger" role="alert" id="alert_purchased_items">
        Danh sách sản phẩm nhập không thể bỏ trống
    </div>
    <table class="table table-sm table-bordered" id="add_item_table">
        <thead>
            <tr class="table-primary">
                <th style="width: calc(100% / 12 * 2);">Ảnh</th>
                <th style="width: calc(100% / 12 * 3);">Tên sản phẩm</th>
                <th style="width: calc(100% / 12 * 3);">Số lượng</th>
                <th style="width: calc(100% / 12 * 3);">Giá nhập</th>
                <th style="width: calc(100% / 12 * 1);">Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="5">
                    <div class="d-flex justify-content-center">
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#add_item" id="add_item_btn"><i class="fas fa-plus"></i> Thêm hàng hóa</button>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
    <button class="btn btn-success col-3 mt-2" onclick="createPurchasesSheet()">Hoàn thành</button>
</div>
<!-- Extra large modal -->
<div class="modal fade" tabindex="-1" role="dialog" id="add_item">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Thêm hàng hóa</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body container">
            
        </div>
    </div>
  </div>
</div>
<div class="modal fade" tabindex="-1" role="dialog" id="supplier_modal">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
          <div class="modal-header">
              <h5 class="modal-title">Danh sách nhà cung cấp</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
              </button>
          </div>
          <div class="modal-body container">
              
          </div>
      </div>
    </div>
  </div>
<script>
    var purchased_items = [];
    var tbody = $('#add_item .modal-body');
    var supplier_modal = $('#supplier_modal .modal-body');

    $('#alert_purchased_items').hide();
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

    $('#add_item_btn').click(function (){
        $.ajax({
            url:`#`,
            method: "GET",
            success: function (response){
                tbody.html(response);
            }
        });
    });
    $('#supplier_modal_btn').click(function (){
        $.ajax({
            url:`./import/supplier`,
            method: "GET",
            success: function (response){
                supplier_modal.html(response);
            }
        });
    });
    $('#add_item').on('hidden.bs.modal', function (e) {
        $("#add_item_table input[name='quantity'], #add_item_table input[name='purchase_price']").change(function () {
            row = $('#add_item_table tbody').find('tr:not(:last-child)');
            new_total_price = 0;
            row.each(function (){
                quantity = $(this).find("input[name='quantity']").val();
                purchase_price = $(this).find("input[name='purchase_price']").val();
                new_total_price += parseInt(quantity) * parseInt(purchase_price);
            })
            $('#total_price').val(new_total_price);
            new_total_price_after_discount = parseInt($('#total_price').val()) - parseInt($('#discount').val());
            $('#total_price_after_discount').val(new_total_price_after_discount);
        })
    })

    $('#discount').change(function (){
        new_total_price_after_discount = parseInt($('#total_price').val()) - parseInt($('#discount').val());
        $('#total_price_after_discount').val(new_total_price_after_discount);
    })

    function deleteSupplier(){
        $('#supplier_name').html("Nhà cung cấp lẻ");
        $('#supplier_id').val("");
    }

    function createPurchasesSheet(){
        row = $('#add_item_table tbody').find('tr:not(:last-child)');
        row.each(function (index ){
            quantity = $(this).find("input[name='quantity']").val();
            purchase_price = $(this).find("input[name='purchase_price']").val();
            item_id = $(this).find("input[name='item_id']").val();
            
            $('<input>').attr({
                type: 'hidden',
                name: 'purchased_items['+index +'][quantity]',
                value: quantity,
            }).appendTo('#purchased_sheet_form');

            $('<input>').attr({
                type: 'hidden',
                name: 'purchased_items['+index +'][purchase_price]',
                value: purchase_price,
            }).appendTo('#purchased_sheet_form');

            $('<input>').attr({
                type: 'hidden',
                name: 'purchased_items['+index +'][item_id]',
                value: item_id,
            }).appendTo('#purchased_sheet_form');
        })
        if(row.length > 0){
            $('#purchased_sheet_form').submit();
        } else {
            $('#alert_purchased_items').show();
        }
    }
</script>
@endsection