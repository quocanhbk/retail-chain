@extends('main')
@section('breadcrumb')  
{{ Breadcrumbs::render('category.list', request()->store_id, request()->branch_id) }}
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
        <input class="form-control col-2" type="search" placeholder="Tìm kiếm theo tên" name="search">
    @endif
    <button class="btn btn-primary mx-1" onclick="formSubmit();">
        <i class="fas fa-search"></i> Tìm kiếm
    </button>


    <a type="button" class="btn btn-primary ml-auto" href="{{ route('category.create', ['store_id' => request()->store_id, 'branch_id' => request()->branch_id]) }}"><i class="fas fa-plus"></i> Thêm danh mục</a>
</form>
<table class="table table-sm table-bordered">
    <thead>
      <tr class="table-primary">
        <th style="width: calc(100% / 12 * 0.4);">#</th>
        <th style="width: calc(100% / 12 * 3); cursor: pointer;" onclick="orderBy('item_categories.name')">Tên danh mục <i class="fas fa-sort-alt"></i></th>
        <th style="width: calc(100% / 12 * 2); cursor: pointer;" onclick="orderBy('item_categories.point_ratio')">Tỉ lệ tích điểm <i class="fas fa-sort-alt"></i></th>
        <th style="width: calc(100% / 12 * 6);">Thao tác</th>
      </tr>
    </thead>
    <tbody>
        @foreach ($items as $item)
            <tr>
                <td class="text-center">{{$item->id}}</td>
                <td>{{$item->name}}</td>
                <td class="text-center">{{$item->point_ratio."%"}}</td>
                <td>
                    <a class="no-underline" href="{{ route('category.edit', ['store_id' => request()->store_id, 'branch_id' => request()->branch_id , 'category_id' => $item->id]) }}">
                        <button class="btn btn-info">
                            <i class="fas fa-edit"></i> Chỉnh sửa
                        </button>
                    </a>

                    <button class="btn btn-danger" id="delete_{{$item->id}}" data-toggle="modal" data-target="#delete_confirm_modal" data-item-name="{{ $item->name }}" data-item-id="{{ $item->id }}">
                        <i class="fas fa-trash-alt"></i> Xóa
                    </button>
                </td>
            </tr>
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
          <h5 class="modal-title">Xác nhận xóa danh mục</h5>
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
    //fill modal data
    var delete_button;
    var delete_item_id = null;
    $('#delete_confirm_modal').on('show.bs.modal', function (event) {
        delete_button = $(event.relatedTarget); 
        delete_button.prop('disabled', true);
        let item_name = delete_button.data('item-name'); 
        let confirm_text = "Bạn có thật sự muốn xóa danh mục: "+item_name+" ?";
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
            url:`./category/${delete_item_id}/delete`,
            method: "POST",
            success: function (response){
                location.reload();
            }
        });
    });
</script>
@endsection