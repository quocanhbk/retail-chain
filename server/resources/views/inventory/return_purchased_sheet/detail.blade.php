@extends('main')
@section('breadcrumb')
{{ Breadcrumbs::render('return_purchased_sheet.detail', request()->store_id, request()->branch_id, request()->return_purchased_sheet_id) }}
@endsection
@section('main-content')
<style>
    #detail-container{
        width: 70%;
    }
    table thead th{
        text-align: center;
    }
    table{
        /* height: 50vh;
        overflow-y: scroll;
        display: block; */
    }
    table tbody tr td{
        height: 60px;
    }
    .img-size{
        width: 60px;
        height: 60px;
    }
    .no-underline{
        text-decoration: none !important;
    }
</style>
<div class="container d-flex flex-column align-items-center">
    <h1 class="mb-2">Đơn trả hàng nhập #{{$item->return_purchased_sheet_id}}</h1>
    <h3 class="mb-5">(Trả hàng cho đơn nhập #{{$item->purchased_sheet_id}})</h3>
    <div class="pb-2" id="detail-container">
        <div class="form-group row">
            <label class="col-sm-3 col-form-label">Tên nhà cung cấp </label>
            <div class="col-sm-9">
                <input type="text" class="form-control" value="{{ $item->supplier_name? $item->supplier_name : 'Nhà cung cấp lẻ'}}" readonly>
            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-3 col-form-label">Người thực hiện </label>
            <div class="col-sm-9">
                <input type="text" class="form-control" value="{{ $item->returner_name}}" readonly>
            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-3 col-form-label">Tổng tiền trả </label>
            <div class="col-sm-9">
                <input type="text" class="form-control" value="{{ $item->total_return_money}}" readonly>
            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-3 col-form-label">Ngày trả </label>
            <div class="col-sm-9">
                <input type="text" class="form-control" value="{{ $item->created_datetime}}" readonly>
            </div>
        </div>
    </div>
    <h3>Danh sách sản phẩm nhập</h3>
    <table class="table table-sm table-bordered">
        <thead>
          <tr class="table-primary">
            <th style="width: calc(100% / 12 * 2);">Ảnh</th>
            <th style="width: calc(100% / 12 * 3);">Tên sản phẩm</th>
            <th style="width: calc(100% / 12 * 3);">Số lượng</th>
            <th style="width: calc(100% / 12 * 3);">Giá nhập</th>
          </tr>
        </thead>
        <tbody>
            @foreach ($list_item as $ele)
                <tr>
                    <td>
                        <div class="d-flex justify-content-center">
                            <img src="{{ $ele->image_url? asset($ele->image_url) : asset('img/no-image.jpg') }}" class="img-size">
                        </div>
                    </td>
                    <td>{{$ele->name}}</td>
                    <td class="text-center">{{$ele->old_quantity}}</td>
                    <td class="text-center">{{number_format($ele->old_purchased_price)."đ"}}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection