<!DOCTYPE html>
<html lang="en" data-bs-theme="dark" style="overflow:hidden;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Kamera Rental')</title>
    <link href="{{ asset('bootstrap/css/bootstrap.css') }}" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="{{ asset('style.css') }}?v=2.13">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <!-- <link href="https://fonts.googleapis.com/css2?family=Titillium+Web:ital,wght@0,200;0,300;0,400;0,600;0,700;0,900;1,200;1,300;1,400;1,600;1,700&display=swap" rel="stylesheet"> -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/webcamjs/1.0.26/webcam.min.js"></script>
    <style>
        fieldset {
            margin-bottom: 1em !important;
            border: 1px solid #666 !important;
            padding:1px !important;
        }

        legend {
            padding: 1px 10px !important;
            float:none;
            width:auto;
        }

        .form-group{
            margin-bottom:15px;
        }

        .auto-width{
            width:1%;
            white-space:nowrap;
        }
    </style>
    @yield('css')
    @stack('css_stack')
</head>
<body class="dark">
    <div class="container-fluid p-0 vh-100">
        @if(!isset($no_sidebar))
        <div class="row mx-0 h-100">
            <div class="col-auto ps-0 vh-100">
                @include('components.common.sidebar')
            </div>
            <div class="col py-0 pe-0 vh-100">
                <div class="card h-100 border-0">
                    <div class="card-header">@yield('content_header')</div>
                    <div class="card-body h-100 overflow-auto">@yield('content')</div>
                    <div class="card-footer">@yield('content_footer')</div>
                </div>
            </div>
        </div>
        @else
        <div class="row mx-0 h-100">
            @yield('content')    
        <div>
        @endif
    </div>
    <canvas hidden id="canvas" width="500" height="400"></canvas>
    <script src="{{ asset('bootstrap/js/bootstrap.bundle.min.js') }}" crossorigin="anonymous"></script>
    <script src="{{ asset('moment.js') }}" crossorigin="anonymous"></script>
    <script src="{{ asset('bootstrap/js/popper.min.js') }}" crossorigin="anonymous"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function(){
            @if(Session::has('error_message'))
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "{{ Session::get('error_message') }}",
                });
            @endif
            
            @if(Session::has('success_message'))
                Swal.fire({
                    title: "Success",
                    text: "{{ Session::get('success_message') }}",
                    icon: "success"
                });
            @endif
        });
    </script>
    @yield('js')
    @stack('js_stack')
    @yield('footer')
    @include('components.common.loader')
</body>
</html>