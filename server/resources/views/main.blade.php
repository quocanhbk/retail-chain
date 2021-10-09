@extends('head')
@section('content')
<style>
    #wrapper {
        overflow-x: hidden;
    }

    #sidebar-wrapper {
        min-height: 100vh;
        margin-left: -15rem;
        transition: margin 0.4s ease-out;
    }

    #sidebar-wrapper .sidebar-heading {
        padding: 0.875rem 1.25rem;
        font-size: 1.1rem;
    }

    #sidebar-wrapper .list-group {
        width: 15rem;
    }

    #page-content-wrapper {
        min-width: 100vw;
    }

    body.sb-sidenav-toggled #wrapper #sidebar-wrapper {
        margin-left: 0;
    }

    body{
        overflow: hidden;
    }

    @media (min-width: 768px) {
        #sidebar-wrapper {
            margin-left: 0;
        }

        #page-content-wrapper {
            min-width: 0;
            width: 100%;
        }

        body.sb-sidenav-toggled #wrapper #sidebar-wrapper {
            margin-left: -15rem;
        }
    }

    .breadcrumb{
        margin: 0px!important;
    }

    .loader {
        display:    none;
        position:   fixed;
        z-index:    100000000;
        top:        0;
        left:       0;
        height:     100%;
        width:      100%;
        background: rgba( 255, 255, 255, .8 ) 
                    url('http://i.stack.imgur.com/FhHRx.gif') 
                    50% 50% 
                    no-repeat;
    }

    /* Anytime the body has the loading class, our
    modal element will be visible */
    body.loading .loader {
        display: block;
    }

    /* disable up down arrow of input[type='number'] */
    /* Chrome, Safari, Edge, Opera */
    input::-webkit-outer-spin-button,
    input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    /* Firefox */
    input[type=number] {
        -moz-appearance: textfield;
    }
</style>
<div class="d-flex" id="wrapper">
    <!-- Sidebar-->
    @include('sidebar')
    <!-- Page content wrapper-->
    <div id="page-content-wrapper">
        <!-- Top navigation-->
        @include('navbar')
        {{-- Breadcrum --}}
        <div class="container-fluid overflow-auto bg-white p-0" style="margin: 10px 10px 0 10px; width: calc(100% - 20px)">
            @yield('breadcrumb')
        </div>
        <!-- Page content-->
        <div class="container-fluid overflow-auto bg-white p-2" style="height: calc(100vh - 55px - 10px - 48px - 10px - 10px); margin: 10px 10px 0 10px; width: calc(100% - 20px)">
            @yield('main-content')
        </div>
    </div>
</div>
<div class="loader"><!-- Place at bottom of page --></div>
<script>
    window.addEventListener('DOMContentLoaded', event => {
        // Toggle the side navigation
        const sidebarToggle = document.body.querySelector('#sidebarToggle');
        if (sidebarToggle) {
            // Uncomment Below to persist sidebar toggle between refreshes
            // if (localStorage.getItem('sb|sidebar-toggle') === 'true') {
            //     document.body.classList.toggle('sb-sidenav-toggled');
            // }
            sidebarToggle.addEventListener('click', event => {
                event.preventDefault();
                document.body.classList.toggle('sb-sidenav-toggled');
                localStorage.setItem('sb|sidebar-toggle', document.body.classList.contains('sb-sidenav-toggled'));
            });
        }
    });
    
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    
    $body = $("body");
    // $(document).click(function(event) {
    //     var text = $(event.target);
    //     console.log(text);
    // });
    $(document).on({
        ajaxStart: function() { $body.addClass("loading");    },
        ajaxStop: function() { $body.removeClass("loading"); }    
    });
</script>
@endsection