@extends('main')
@section('breadcrumb')
{{ Breadcrumbs::render('inventory.itemPriceHistory', request()->store_id, request()->branch_id, request()->item_id) }}
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
        <th style="width: calc(100% / 12 * 3);">Từ ngày</th>
        <th style="width: calc(100% / 12 * 3);">Đến ngày</th>
        <th style="width: calc(100% / 12 * 2);">Giá bán</th>
        <th style="width: calc(100% / 12 * 3);">Điều chỉnh bởi</th>
      </tr>
    </thead>
    <tbody>
        @php
            $count = request()->input('page')? (request()->input('page') - 1) * $per_page + 1 : 1;
        @endphp
        
        @foreach ($items as $item)
            <tr>
                <td class="text-center">{{$count}}</td>
                <td class="text-center">{{$item->start_date}}</td>
                <td class="text-center">{{$item->end_date? $item->end_date : "Hiện tại" }}</td>
                <td class="text-center">
                    @php
                        echo number_format($item->sell_price)."đ";
                    @endphp
                </td>
                <td class="text-center">{{$item->change_by}}</td>
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