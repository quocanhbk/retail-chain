<div class="form-inline mb-2">
    @if (!empty($search))
        <input class="form-control col-3" type="search" value="{{ $search }}" name="search">
    @else
        <input class="form-control col-3" type="search" placeholder="Tìm kiếm theo tên, mã vạch" name="search">
    @endif
    <button class="btn btn-primary mx-1" onclick="getPosts();">
        <i class="fas fa-search"></i> Tìm kiếm
    </button>
</div>
<table class="table table-sm table-bordered">
    <thead>
        <tr class="table-primary">
        <th style="width: calc(100% / 12 * 2);">Ảnh</th>
        <th style="width: calc(100% / 12 * 3);">Tên sản phẩm</th>
        <th style="width: calc(100% / 12 * 3);">Mã vạch</th>
        <th style="width: calc(100% / 12 * 3);">Thao tác</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($items as $ele)
            <tr>
                <td>
                    <div class="d-flex justify-content-center">
                        <img src="{{ $ele->image_url? asset($ele->image_url) : asset('img/no-image.jpg') }}" class="img-size">
                    </div>
                </td>
                <td>{{$ele->name}}</td>
                <td class="text-center">{{$ele->bar_code}}</td>
                <td><button type="button" class="btn btn-primary" onclick="addItem(this)"><i class="fas fa-plus"></i> Thêm hàng hóa</button></td>
                <input type="hidden" name="item_id" value="{{$ele->item_id}}">
                <input type="hidden" name="name" value="{{$ele->name}}">
                <input type="hidden" name="image_url" value="{{$ele->image_url}}">
                <input type="hidden" name="purchase_price" value="{{$ele->purchase_price}}">
            </tr>
        @endforeach
    </tbody>
</table>
<div class="d-flex justify-content-center">
    {!! $items->links() !!}
</div>
<script>
    var base_url = {!! json_encode(url('/')) !!};
    var page = 0;
    function addItem (element) {
        row = $(element).parent().parent();
        item_id = row.children('input[name="item_id"]').val();
        name = row.children('input[name="name"]').val();
        purchase_price = row.children('input[name="purchase_price"]').val();
        image_url = row.children('input[name="image_url"]').val();
        image_url = image_url? base_url + "/" + image_url: base_url + "/" +"img/no-image.jpg";

        add_item_table = $('#add_item_table tbody');

        html_code = `
        <tr>
            <td>
                <div class="d-flex justify-content-center">
                    <img src="`+image_url+`" class="img-size">
                </div>
            </td>
            <td>`+name+`</td>
            <td><input class="form-control" type="number" name="quantity" value="1" min="1"></td>
            <td><input class="form-control" type="number" name="purchase_price" value = "`+purchase_price+`" min="0"></td>
            <td><button type="button" class="btn btn-danger" onclick="deletePurchasedItem(this)"><i class="fas fa-trash-alt"></i></button></td>
            <input type="hidden" name="item_id" value = "`+item_id+`">
        </tr>
        `;
        check_existed_item = add_item_table.find("input[type='hidden'][value="+item_id+"]");
        if(check_existed_item.length == 0){
            add_item_table.prepend(html_code);

            new_total_price = parseInt($('#total_price').val()) + parseInt(purchase_price);
            $('#total_price').val(new_total_price);
            new_total_price_after_discount = parseInt($('#total_price').val()) - parseInt($('#discount').val());
            $('#total_price_after_discount').val(new_total_price_after_discount);
        }
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
            url:`?page=` + page + `&search=` + search,
            method: "GET",
            success: function (response){
                tbody = $('#add_item .modal-body');
                tbody.html(response);
            }
        });
    }

    function deletePurchasedItem(element){
        table_row = $(element).parent().parent()
        quantity = parseInt(table_row.find("input[name='quantity'").val());
        purchase_price = parseInt(table_row.find("input[name='purchase_price'").val());
        new_total_price = parseInt($('#total_price').val()) - purchase_price * quantity;
        $('#total_price').val(new_total_price);
        new_total_price_after_discount = parseInt($('#total_price').val()) - parseInt($('#discount').val());
        $('#total_price_after_discount').val(new_total_price_after_discount);
        table_row = $(element).parent().parent().remove();
    }
</script>