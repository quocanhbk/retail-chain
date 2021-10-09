<style>
    .rotate{
        -moz-transition: all 0.4s linear;
        -webkit-transition: all 0.4s linear;
        transition: all 0.4s linear;
    }

    .rotate.right{
        -ms-transform: rotate(180deg);
        -moz-transform: rotate(180deg);
        -webkit-transform: rotate(180deg);
        transform: rotate(180deg);
    }

    .user-name:hover{
        text-decoration: none;
    }

    .nav-font-weight{
        font-weight: 500
    }
</style>

<nav class="navbar navbar-expand-lg navbar-light  border-bottom nav-font-weight" style="background-color: #b8daff">
    <div class="container-fluid">
        <button class="btn btn-outline-primary" id="sidebarToggle">
            <i class="fas fa-arrow-left rotate" aria-hidden="true"></i>
        </button>
        <div class="dropdown">
            <a class="dropdown user-name" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="color: #007bff">
                {{ Auth::user()->name }}
                <img src="{{ url(Auth::user()->avatar_url) }}" alt="" class="rounded-circle" height="35">
            </a>
            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuButton">
              <a class="dropdown-item" href="{{ route('logout') }}">Đăng xuất</a>
            </div>
        </div>
    </div>
</nav>


<script>
    $("#sidebarToggle").click(function(){
        $('.rotate').toggleClass("right"); 
    });
</script>