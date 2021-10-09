@extends('main')
@section('breadcrumb')  
{{ Breadcrumbs::render('inventory.item', request()->store_id, request()->branch_id) }}
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
<form method="GET" class="form-inline mb-2" id="form_search">
    @if (!empty($_GET['search']))
        <input class="form-control col-2" type="search" value="{{ $_GET['search'] }}" name="search">
    @else
        <input class="form-control col-2" type="search" placeholder="Tìm kiếm theo tên, mã vạch" name="search">
    @endif
    <button class="btn btn-primary mx-1" onclick="formSubmit();">
        <i class="fas fa-search"></i> Tìm kiếm
    </button>

    <!-- Button trigger modal -->
    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#category_modal">
        <i class="fas fa-filter"></i> Danh mục
    </button>

    <!-- Category modal -->
    <div class="modal fade" id="category_modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Lọc danh mục</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body px-3">
                    @php
                        $category_str = request()->get('category_string');
                        $category_str_array = explode("_", $category_str);
                    @endphp
                    @foreach ($category_list as $category)
                        <div class="form-check" style="justify-content: left">

                            @if (!empty($category_str_array[0]))
                                @if (in_array($category->id, $category_str_array))
                                    <input class="form-check-input" type="checkbox" value="{{ $category->id }}" id="category_id_{{ $category->id }}" checked>
                                @else
                                    <input class="form-check-input" type="checkbox" value="{{ $category->id }}" id="category_id_{{ $category->id }}">
                                @endif
                            @else
                                <input class="form-check-input" type="checkbox" value="{{ $category->id }}" id="category_id_{{ $category->id }}" checked>
                            @endif

                            <label class="form-check-label" for="category_id_{{ $category->id }}">
                                {{ $category->name }}
                            </label>
                        </div>
                    @endforeach
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                    <button type="button" class="btn btn-primary" onclick="formSubmit();">Lưu</button>
                </div>
            </div>
        </div>
    </div>

    <a type="button" class="btn btn-primary ml-auto" href="{{ route('inventory.itemCreate', ['store_id' => request()->store_id, 'branch_id' => request()->branch_id]) }}"><i class="fas fa-plus"></i> Thêm sản phẩm</a>
</form>
<table class="table table-sm table-bordered">
    <thead>
      <tr class="table-primary">
        {{-- <th style="width: calc(100% / 12 * 0.4);">#</th> --}}
        <th style="width: calc(100% / 12 * 1);">Ảnh</th>
        <th style="width: calc(100% / 12 * 2); cursor: pointer;" onclick="orderBy('items.name')">Tên sản phẩm <i class="fas fa-sort-alt"></i></th>
        <th style="width: calc(100% / 12 * 1); cursor: pointer;" onclick="orderBy('items.bar_code')">Mã vạch <i class="fas fa-sort-alt"></i></th>
        <th style="width: calc(100% / 12 * 1); cursor: pointer;" onclick="orderBy('item_quantities.quantity')">Số lượng <i class="fas fa-sort-alt"></i></th>
        <th style="width: calc(100% / 12 * 1); cursor: pointer;" onclick="orderBy('item_prices.sell_price')">Giá bán <i class="fas fa-sort-alt"></i></th>
        <th style="width: calc(100% / 12 * 1); cursor: pointer;" onclick="orderBy('purchase_price_info.purchase_price')">Giá nhập <i class="fas fa-sort-alt"></i></th>
        <th style="width: calc(100% / 12 * 1);">Tỉ lệ tích điểm</th>
        <th style="width: calc(100% / 12 * 1); cursor: pointer;" onclick="orderBy('items.created_datetime')">Ngày tạo <i class="fas fa-sort-alt"></i></th>
        <th style="width: calc(100% / 12 * 2;)">Thao tác</th>
      </tr>
    </thead>
    <tbody>
        @php
            $count = request()->input('page')? (request()->input('page') - 1) * $per_page + 1 : 1;
        @endphp
        @foreach ($items as $item)
            <tr>
                {{-- <td class="text-center">{{$count}}</td> --}}
                <td>
                    <div class="d-flex justify-content-center">
                        <img src="{{ $item->image_url? asset($item->image_url) : asset('img/no-image.jpg') }}" class="img-size">
                    </div>
                </td>
                <td>{{$item->name}}</td>
                <td class="text-center">{{$item->bar_code}}</td>
                <td class="text-center">{{$item->quantity}}</td>
                <td class="text-center">
                    @php
                        echo number_format($item->sell_price)."đ";
                    @endphp
                </td>
                <td class="text-center">
                    @php
                        echo number_format($item->purchase_price)."đ";
                    @endphp
                </td>
                <td class="text-center">{{round($item->point_ratio, 2)."%"}}</td>
                <td>{{$item->created_datetime}}</td>
                <td>
                    <a class="no-underline" href="{{ route('inventory.itemDetail', ['store_id' => request()->store_id, 'branch_id' => request()->branch_id, 'item_id' => $item->item_id]) }}">
                        <button class="btn btn-info">
                            <i class="fas fa-eye"></i> Chi tiết
                        </button>
                    </a>

                    <button class="btn btn-danger" id="delete_{{$item->item_id}}" data-toggle="modal" data-target="#delete_confirm_modal" data-item-name="{{ $item->name }}" data-item-id="{{ $item->item_id }}">
                        <i class="fas fa-trash-alt"></i> Xóa
                    </button>
                </td>
            </tr>
            @php
                $count++;
            @endphp
        @endforeach
    </tbody>
</table>
<div class="d-flex justify-content-center">
    {{$items->links()}}
</div>
{{-- Delete confirmation modal --}}
<div class="modal fade" id="delete_confirm_modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Xác nhận xóa sản phẩm</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
            
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
          <button type="button" class="btn btn-primary" id="confirm_delete_btn">Đồng ý</button>
        </div>
      </div>
    </div>
  </div>

<script>
    function formSubmit(){
        let category_id_str = "";
        $("input:checkbox:checked").each(function(){
            category_id_str += $(this).val() + "_";
        });
        category_id_str = category_id_str.substring(0, category_id_str.length - 1);

        $('<input>').attr({
            type: 'hidden',
            value: category_id_str,
            name: 'category_string'
        }).appendTo('#form_search');

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

        let category_id_str = "";
        $("input:checkbox:checked").each(function(){
            category_id_str += $(this).val() + "_";
        });
        category_id_str = category_id_str.substring(0, category_id_str.length - 1);

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

        $('<input>').attr({
            type: 'hidden',
            value: category_id_str,
            name: 'category_string'
        }).appendTo('#form_search');

        $('#form_search').submit();
    }
    //fill modal data
    var delete_button;
    var delete_item_id = null;
    $('#delete_confirm_modal').on('show.bs.modal', function (event) {
        delete_button = $(event.relatedTarget); 
        delete_button.prop('disabled', true);
        let item_name = delete_button.data('item-name'); 
        let confirm_text = "Bạn có thật sự muốn xóa sản phẩm: "+item_name+" ?";
        let modal = $(this);
        modal.find('.modal-body').html(confirm_text);
        delete_item_id = delete_button.data('item-id');
    })
    //enable button
    $('#delete_confirm_modal').on('hidden.bs.modal', function (event) { 
        delete_button.prop('disabled', false);
        delete_item_id = null;
    });
    $('#confirm_delete_btn').click(function (){
        $.ajax({
            url:`./item/${delete_item_id}/delete`,
            method: "POST",
            success: function (response){
                location.reload();
            }
        });
    });
</script>
@endsection