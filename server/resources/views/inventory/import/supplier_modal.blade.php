<div class="form-inline mb-2">
    @if (!empty($search))
        <input class="form-control col-3" type="search" value="{{ $search }}" name="search">
    @else
        <input class="form-control col-3" type="search" placeholder="Tìm kiếm theo tên, SĐT" name="search">
    @endif
    <button class="btn btn-primary mx-1" onclick="getPosts();">
        <i class="fas fa-search"></i> Tìm kiếm
    </button>
</div>
<table class="table table-sm table-bordered">
    <thead>
      <tr class="table-primary">
        <th style="width: calc(100% / 12 * 0.5);">#</th>
        <th style="width: calc(100% / 12 * 3);">Tên nhà cung cấp <i class="fas fa-sort-alt"></i></th>
        <th style="width: calc(100% / 12 * 2);">Số điện thoại <i class="fas fa-sort-alt"></i></th>
        <th style="width: calc(100% / 12 * 2);">Địa chỉ <i class="fas fa-sort-alt"></i></th>
        <th style="width: calc(100% / 12 * 2);">Email <i class="fas fa-sort-alt"></i></th>
        <th style="width: calc(100% / 12 * 2.5);">Thao tác</th>
      </tr>
    </thead>
    <tbody>
        @foreach ($items as $item)
            <tr>
                <td class="text-center supplier-id">{{$item->id}}</td>
                <td>{{$item->name}}</td>
                <td >{{$item->phone}}</td>
                <td >{{$item->address}}</td>
                <td >{{$item->email}}</td>
                <td>
                    <button class="btn btn-success" onclick="addSupplier(this)"><i class="fas fa-check"></i> Chọn</button>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
<div class="d-flex justify-content-center">
    {!! $items->links() !!}
</div>
<script>
    var page = 0 ;
    function addSupplier(element){
        supplier_id = $(element).parent().parent().find('td:nth-child(1)').text();
        supplier_name = $(element).parent().parent().find('td:nth-child(2)').text();
        $('#supplier_name').html(supplier_name);
        $('#supplier_id').val(supplier_id);
    }
    
    $('.pagination a').unbind('click').on('click', function(e) {
        e.preventDefault();
        page = $(this).attr('href').split('page=')[1];
        getPosts();
    });
    
    function getPosts()
    {
        search = $("input[name='search']").val();
        $.ajax({
            url:`./import/supplier?page=` + page + `&search=` + search,
            method: "GET",
            success: function (response){
                supplier_modal.html(response);
            }
        });
    }
</script>