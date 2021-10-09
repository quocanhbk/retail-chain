@extends('container')
@section('container-content')
    <style>
        .hover-effect:hover{
            box-shadow: 0 .5rem 1rem rgba(0,0,0,.15);
            transition: box-shadow 0.4s ease-in-out;
        }
        .banner{
            background: rgb(2,0,36);
            background: linear-gradient(180deg, rgba(2,0,36,1) 0%, rgba(255,255,255,1) 0%, rgba(147,209,255,1) 100%);
        }
        .image-holder{
            height: 50%;
        }
        .content-holder{
            height: 50%;
        }
        .price{
            font-size: 20px;
            color: #fe0000;
            font-weight: 600;
        }
        .product-name{
            height: 40%;
        }
        a{
            cursor:pointer;
        }
    </style>
    {{-- <div class="d-flex align-items-center banner" style="flex-direction: column; margin-left: -15px; margin-right: -15px;">
        <img src="{{ asset('img/BK.png') }}" class="img-fluid">
        <img src="{{ asset('img/RM.png') }}" class="img-fluid">
    </div> --}}
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary"  style="margin-left: -15px; margin-right: -15px;">
        
        <a class="navbar-brand col-sm-3 my-1 d-flex justify-content-center" href='./item-list'>{{ $branch_info->name }}</a>
        <div class="dropdown ml-auto col-sm-4 my-1 d-flex">
            <div class="col d-flex justify-content-center ">
                <button class="btn btn-outline-light dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                  Danh mục
                </button>
                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                    
                    <?php
                        $category_id = isset($_GET['category_id'])? $_GET['category_id'] : null;
                        if($category_id === null){
                            echo "<a class='dropdown-item active' onclick=changeCategory(null)>Tất cả</a>";
                        } else {
                            echo "<a class='dropdown-item' onclick=changeCategory(null)>Tất cả</a>";
                        }
                        foreach($category_list as $category){
                            if($category_id == $category->id){
                                echo "<a class='dropdown-item active' onclick=changeCategory($category->id)>$category->name</a>";
                            } else {
                                echo "<a class='dropdown-item' onclick=changeCategory($category->id)>$category->name</a>";
                            }
                    ?>
                    
                    <?php
                        }
                    ?>
                </div>
            </div>
            <div class="col-8 d-flex justify-content-center ">
                <a href="../qr-scanner">
                    <button class="btn btn-outline-light">
                        <i class="fa fa-qrcode fa-lg" aria-hidden="true"></i>
                        Quét mã QR
                    </button>
                </a>
            </div>
        </div>
        <form class="form-inline col-sm-5  my-2 my-lg-0 pl-0" method="GET">
            @isset($_GET['category_id'])
                <input type="hidden" name="category_id" value="{{$_GET['category_id']}}">
            @endisset
            @if (isset($_GET['key_word']))
                <input class="form-control col col-sm-8 mx-2" type="search" placeholder="Tìm kiếm" value="{{ $_GET['key_word'] }}" aria-label="Search" name="key_word">
            @else
                <input class="form-control col col-sm-8 mx-2" type="search" placeholder="Tìm kiếm" aria-label="Search" name="key_word">
            @endif
            <button class="btn btn-outline-light " type="submit"><i class="fa fa-search fa-lg" aria-hidden="true"></i> Tìm kiếm</button>
        </form>
    </nav>
    <div class="row d-flex justify-content-left my-5 px-3">
    
    <?php
        if(count($item_list) > 0){
            foreach($item_list as $item){
    ?>
    <div class="col-sm-3 my-1 p-1 border rounded hover-effect" >
        <div class="d-flex justify-content-center image-holder">
            <img src="{{ $item->image_url? asset($item->image_url) : asset('img/no-image.jpg') }}" class="img-fluid">
        </div>
        <div class="content-holder d-flex flex-column justify-content-between">
            <div class="d-flex justify-content-center align-items-center mt-3 product-name">
                {{ $item->name }}
            </div>
            <div class="mb-3 border-top">
                <div class="d-flex justify-content-center my-2 price">
                    {{ number_format($item->sell_price) }} đ
                </div>
                <div class="d-flex justify-content-center my-2">
                    Số lượng: {{ $item->quantity }}
                </div>
            </div>
        </div>
    </div>
    <?php      
            }    
        } else {
    ?>
    <h1 class="d-flex justify-content-center w-100">
        Không tìm thấy sản phẩm
    </h1>
    <?php
        }
    ?>
    </div>
    <div class="d-flex justify-content-center">
        {{ $item_list->links() }}
    </div>
    
    <script>
        function changeCategory(categoryId){
            var form = document.createElement("form");
            var category_id = document.createElement("input"); 
            var key_word = document.createElement("input");

            var url_string = window.location.href;
            var url = new URL(url_string);
            var key_word_value = url.searchParams.get("key_word");
            if (key_word_value !== null){
                key_word.value=key_word_value;
                key_word.name="key_word";
                key_word.type = 'hidden';
                form.appendChild(key_word);
            }

            form.method = "GET";
            // form.action = "#";   
            if (categoryId !== null){
                category_id.value=categoryId;
                category_id.name="category_id";
                category_id.type = 'hidden';
                form.appendChild(category_id);
            }
            

            document.body.appendChild(form);

            form.submit();
        }
    </script>
@endsection