@extends('head')
@section('content')
<style>
    #login-row #login-column #login-box {
        margin-top: 120px;
        /* border: 1px solid #9C9C9C; */
        background-color: white;
    }
    #login-row #login-column #login-box #login-form {
        padding: 20px;
    }
    #bg{
        /* background-image: url({{url("background.svg")}}) ;
        background-repeat: no-repeat;
        background-size: 100% 100%; */
        /* background-color: dodgerblue */
        background: rgb(2,0,36);
        background: radial-gradient(circle, rgba(2,0,36,1) 0%, rgba(27,123,252,1) 0%, rgba(147,209,255,1) 100%);
    }
    #page-name{
        font-family: "Lucida Handwriting", "Courier New", monospace;
    }
</style>

<div class="container-fluid p-0" style=" background-color: white">
    <div id="bg" class="vh-100">
        <div id="login-row" class="row justify-content-center align-items-center m-0">
            <div id="login-column" class="col-md-4 d-flex justify-content-center">
                <div id="login-box" class="col-md-12 border border-primary rounded shadow-lg">
                    <form id="login-form" class="form" action="" method="post" novalidate>
                        @csrf

                        <h1 id="page-name" class="text-center text-primary">BKRM</h1>

                        @isset($error_mess)
                            <div class="alert alert-danger" role="alert">
                                {{ $error_mess }}
                            </div>
                        @endisset

                        <div class="form-group">
                            <label for="username" class="text-primary">Tên đăng nhập:</label><br>
                            <input type="text" name="username" id="username" class="form-control" required>
                            <div class="invalid-feedback">
                                Vui lòng điền tên đăng nhập
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="password" class="text-primary">Mật khẩu:</label><br>
                            <input type="password" name="password" id="password" class="form-control" required>
                            <div class="invalid-feedback">
                                Vui lòng điền mật khẩu
                            </div>
                        </div>
                        <div class="form-group d-flex justify-content-center">
                            <input type="submit" name="submit" class="btn btn-primary btn-lg" value="Đăng nhập">
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

    <script>
        // Example starter JavaScript for disabling form submissions if there are invalid fields
        (function() {
          'use strict';
          window.addEventListener('load', function() {
            // Fetch all the forms we want to apply custom Bootstrap validation styles to
            var forms = document.getElementsByClassName('form');
            // Loop over them and prevent submission
            var validation = Array.prototype.filter.call(forms, function(form) {
              form.addEventListener('submit', function(event) {
                if (form.checkValidity() === false) {
                  event.preventDefault();
                  event.stopPropagation();
                }
                form.classList.add('was-validated');
              }, false);
            });
          }, false);
        })();
    </script>
@endsection