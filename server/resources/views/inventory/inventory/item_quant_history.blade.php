@extends('main')
@section('breadcrumb')
{{ Breadcrumbs::render('inventory.itemQuantityHistory', request()->store_id, request()->branch_id, request()->item_id) }}
@endsection
@section('main-content')
<style>
    table thead th{
        text-align: center;
    }
</style>
<table class="table">
    <thead>
      <tr class="table-primary">
        <th style="width: calc(100% / 12 * 1);">#</th>
        <th style="width: calc(100% / 12 * 2);">Thời gian</th>
        <th style="width: calc(100% / 12 * 1);">Số lượng cũ</th>
        <th style="width: calc(100% / 12 * 1);">Số lượng mới</th>
        <th style="width: calc(100% / 12 * 1);">Thay đổi</th>
        <th style="width: calc(100% / 12 * 2);">Điều chỉnh bởi</th>
        <th style="width: calc(100% / 12 * 4);">Lý do</th>
      </tr>
    </thead>
    <tbody>
        @php
            $count = request()->input('page')? (request()->input('page') - 1) * $per_page + 1 : 1;
        @endphp
        
        @foreach ($items as $item)
            <tr>
                <td class="text-center">{{$count}}</td>
                <td class="text-center">{{$item->created_datetime}}</td>
                <td class="text-center">{{$item->old_quant}}</td>
                <td class="text-center">{{$item->new_quant}}</td>
                <td class="text-center">{{$item->changes}}</td>
                <td class="text-center">{{$item->checker_name}}</td>
                <td class="text-left">{{$item->reason? $item->reason : "Không" }}</td>
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
@endsection