@extends('main')
@section('breadcrumb')  
{{ Breadcrumbs::render('purchased_sheet.list', request()->store_id, request()->branch_id) }}
@endsection

@section('main-content')
<style>
    table thead th{
        text-align: center;
    }
    table{
        /* height: 50vh;
        overflow-y: scroll;
        display: block; */
    }
    .no-underline{
        text-decoration: none !important;
    }
</style>
<form method="GET" class="form-inline mb-2" id="form_search">
    @if (!empty($_GET['search']))
        <input class="form-control col-2" type="search" value="{{ $_GET['search'] }}" name="search">
    @else
        <input class="form-control col-2" type="search" placeholder="Tìm kiếm theo nhà cung cấp" name="search">
    @endif
    <button class="btn btn-primary mx-1" onclick="formSubmit();">
        <i class="fas fa-search"></i> Tìm kiếm
    </button>
</form>
<table class="table table-sm table-bordered">
    <thead>
      <tr class="table-primary">
        <th style="width: calc(100% / 12 * 0.4);">#</th>
        <th style="width: calc(100% / 12 * 2); cursor: pointer;" onclick="orderBy('suppliers.name')">Nhà cung cấp <i class="fas fa-sort-alt"></i></th>
        <th style="width: calc(100% / 12 * 2); cursor: pointer;" onclick="orderBy('purchased_sheets.total_purchase_price')">Tổng tiền hàng <i class="fas fa-sort-alt"></i></th>
        <th style="width: calc(100% / 12 * 2); cursor: pointer;" onclick="orderBy('purchased_sheets.discount')">Giảm giá <i class="fas fa-sort-alt"></i></th>
        <th style="width: calc(100% / 12 * 2); cursor: pointer;" onclick="orderBy('purchased_sheets.delivery_datetime')">Ngày nhập <i class="fas fa-sort-alt"></i></th>
        <th style="width: calc(100% / 12 * 4);">Thao tác</th>
      </tr>
    </thead>
    <tbody>
        @foreach ($items as $item)
            <tr>
                <td class="text-center">{{$item->purchased_sheet_id}}</td>
                <td>{{$item->supplier_name? $item->supplier_name : "Nhà cung cấp lẻ"}}</td>
                <td class="text-center">{{number_format($item->total_purchase_price)."đ"}}</td>
                <td class="text-center">{{$item->discount}}</td>
                <td class="text-center">{{$item->delivery_datetime}}</td>
                <td>
                    <a class="no-underline" href="{{ route('purchased_sheet.detail', ['store_id' => request()->store_id, 'branch_id' => request()->branch_id, 'purchased_sheet_id' => $item->purchased_sheet_id]) }}">
                        <button class="btn btn-info">
                            <i class="fas fa-eye"></i> Chi tiết
                        </button>
                    </a>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
<div class="d-flex justify-content-center">
    {{$items->links()}}
</div>
<script>
    function formSubmit(){
        $('#form_search').submit();
    }

    function orderBy(order_by){
        let url_string = window.location.href;
        let url = new URL(url_string);
        let order = url.searchParams.get("order");
        let url_order_by = url.searchParams.get("order_by");
        if (order && order_by == url_order_by){
            order = order == "asc"? "desc":"asc";
        } else {
            order = "asc";
        }

        $('<input>').attr({
            type: 'hidden',
            value: order,
            name: 'order'
        }).appendTo('#form_search');

        $('<input>').attr({
            type: 'hidden',
            value: order_by,
            name: 'order_by'
        }).appendTo('#form_search');

        $('#form_search').submit();
    }
</script>
@endsection