@extends('layouts.app', ['no_sidebar' => true])

@section('title')
- Login
@endsection

@section('css')
<style>
    .input-group-addon i {
        margin-left: -30px;
        cursor: pointer;
        z-index: 200;
        position: absolute;
        font-size: large;
        color: #6c757d;
        margin-top:7px;
    }
</style>
@endsection

@section('content')
<div class="row mx-0 h-100 d-flex justify-content-center align-items-center">
    <div class="col-12 col-md-6 col-xl-4">
        <div class="m-4">
            <img src="{{asset(site_config()->site_banner)}}" class="w-100">
        </div>
        <div class="card mb-4">
            <div class="card-header text-center"><h5><i class="bi bi-camera me-2"></i>{{site_config()->site_name}} System</h5></div>
            <div class="card-body">
                <form method="POST" action="{{route('login.post')}}">
                    {{csrf_field()}}
                    <div class="form-group mb-2">
                        <label>Email / Username</label>
                        <div class="input-group mb-3">
                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                            <input type="email" placeholder="E-mail" name="email" class="form-control">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <div class="input-group mb-3">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password" placeholder="Kata Sandi" name="password" class="form-control">
                            <div class="input-group-addon">
                                <i class="bi bi-eye-slash" id="togglePassword"></i>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="d-none" id="submitBtn"></button>
                </form>
            </div>
            <div class="card-footer text-end">
                <label for="submitBtn" class="btn btn-primary btn-sm">Login</label>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
    $("#togglePassword").click(function(){
        var type = $("[name='password']").attr('type');
        if(type == 'password'){
            $("[name='password']").attr('type', 'text');
            $(this).removeClass('bi-eye-slash');
            $(this).addClass('bi-eye');
        }else{
            $("[name='password']").attr('type', 'password');
            $(this).addClass('bi-eye-slash');
            $(this).removeClass('bi-eye');
        }
    });
</script>
@endsection