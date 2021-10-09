<style>
    .badge-header{
        padding: .2rem 1.25rem;
        color: white
    }
    .span-modify{
        height: inherit;
        width: 10px;
        background-color: #d6d8db;
    }
    .span-modify.span-active{
        background-color: #007bff!important
    }
    .list-group-item-action-modify{
    }
    .list-group-item-action-modify:hover{
        text-decoration: none;
        color: #383d41;
    }
    .hover-effect:hover a{
        text-decoration: none;
        color: inherit;
        background-color: #c8cbcf;
    }
    .hover-effect:hover span{
        background-color: #c8cbcf;
    }
    .hover-effect a{
        width: 100%;
    }
    .sidebar-border-color{
        border-color: #c5c5c5!important;
    }
</style>
<div class="shadow bg-dark" id="sidebar-wrapper" style="font-weight: 500;">
    <div class="sidebar-heading bg-primary text-light">{{ $branch_name }}</div>
    <div class="list-group list-group-flush">
        <span class="badge-header">Kho hàng</span>
        <div class="d-flex hover-effect border-bottom sidebar-border-color" id="sidebar_import">
            <span class="span-modify"></span>
            <a class="list-group-item list-group-item-secondary border-0" href="{{ route('import.index', ['store_id' => request()->store_id, 'branch_id' => request()->branch_id]) }}"><i class="fas fa-box-open"></i> Nhập hàng</a>
        </div>
        <div class="d-flex hover-effect border-bottom sidebar-border-color" id="sidebar_inventory">
            <span class="span-modify" ></span>
            <a class="list-group-item list-group-item-secondary border-0" href="{{ route('inventory.item', ['store_id' => request()->store_id, 'branch_id' => request()->branch_id]) }}"><i class="fas fa-inventory"></i> Kho hàng</a>
        </div>
        <div class="d-flex hover-effect border-bottom sidebar-border-color" id="sidebar_purchased_sheet">
            <span class="span-modify" ></span>
            <a class="list-group-item list-group-item-secondary border-0" href="{{ route('purchased_sheet.list', ['store_id' => request()->store_id, 'branch_id' => request()->branch_id]) }}"><i class="fas fa-list"></i> Đơn nhập hàng</a>
        </div>
        <div class="d-flex hover-effect border-bottom sidebar-border-color" id="sidebar_return_purchased_sheet">
            <span class="span-modify" ></span>
            <a class="list-group-item list-group-item-secondary border-0" href="{{ route('return_purchased_sheet.list', ['store_id' => request()->store_id, 'branch_id' => request()->branch_id]) }}"><i class="fas fa-list"></i> Đơn trả hàng nhập</a>
        </div>
        <div class="d-flex hover-effect border-bottom sidebar-border-color" id="sidebar_supplier">
            <span class="span-modify" ></span>
            <a class="list-group-item list-group-item-secondary border-0" href="{{ route('supplier.list', ['store_id' => request()->store_id, 'branch_id' => request()->branch_id]) }}"><i class="far fa-list-alt"></i> Nhà cung cấp</a>
        </div>
        <div class="d-flex hover-effect border-bottom sidebar-border-color" id="sidebar_category">
            <span class="span-modify" ></span>
            <a class="list-group-item list-group-item-secondary border-0" href="{{ route('category.list', ['store_id' => request()->store_id, 'branch_id' => request()->branch_id]) }}"><i class="fas fa-th-list"></i> Danh mục</a>
        </div>
    </div>
</div>
<script>
    var sidebar_code = {{$sidebar_code}};
    $(sidebar_code).find('span').addClass("span-active");
    $(sidebar_code).find('span').css("margin-bottom","-1px");
    // $(sidebar_code).hover(function(){
    //     color = $(this).css('backgroundColor');
    //     console.log(color);
    //     console.log("color");
    // })
    // console.log(sidebar_code+" span")
    // console.log(sidebar_code)
</script>