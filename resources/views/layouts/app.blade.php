<!DOCTYPE html>
<html lang="en" data-bs-theme="dark" style="overflow:hidden;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Kamera Rental')</title>
    <link href="{{ asset('bootstrap/css/bootstrap.css') }}" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="{{ asset('style.css') }}?v=2.13">
    <link rel="stylesheet" href="{{ asset('footable/css/footable.bootstrap.min.css') }}?v=2.13">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <!-- <link href="https://fonts.googleapis.com/css2?family=Titillium+Web:ital,wght@0,200;0,300;0,400;0,600;0,700;0,900;1,200;1,300;1,400;1,600;1,700&display=swap" rel="stylesheet"> -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/webcamjs/1.0.26/webcam.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        body.dark .select2-search__field{
            background: #f7f7f7;
            color: black;
        }

        body.dark .select2-results{
            display: block;
            background: #212529;
            color: white;
        }

        body.dark .select2-selection{
            border:0px;
        }

        body.dark .select2-selection__rendered{
            background-color: #212529;
            color: white !important;
            border: 1px solid #495057;
            border-radius: 4px;
            padding-top: 3px;
        }

        span.select2-selection.select2-selection--single, .select2-container--default .select2-selection--single .select2-selection__rendered{
            height:100%;
        }
    </style>
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
    <script src="{{ asset('footable/js/footable.min.js') }}" crossorigin="anonymous"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        function init_select(){
            $(".select-searchable").select2({ width: 'resolve' });
        }

        function makeid(length) {
            let result = '';
            const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
            const charactersLength = characters.length;
            let counter = 0;
            while (counter < length) {
            result += characters.charAt(Math.floor(Math.random() * charactersLength));
            counter += 1;
            }
            return result;
        }

        $(document).ready(function(){
            init_select();
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

            $(document).on("keyup", ".comma-separated", function(event) {                            
                if(event.which >= 37 && event.which <= 40) return;
                // format number
                $(this).val(function(index, value) {                                                                  
                    return value.replace(/\D/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ",");                                                                
                });                                                  
            });

            $(document).on("blur", ".comma-separated", function(event) {
            // $(".comma-separated").blur(function (){    
                var val = this.value.replace(/,/g, "");
                var target = $(this).attr('data-target');
                target = $("#"+target);

                if(this.value == 'NaN' || val < 0){
                    $(this).val(0).trigger('change');
                    target.val(0).trigger('change');
                } else {
                    target.val(val).trigger('change');
                }
            });
        });
        function numberWithCommas(x) {
            return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        }
    </script>
    @yield('js')
    @stack('js_stack')
    @yield('footer')
    @yield('footer_stack')
    @include('components.common.loader')
</body>
</html>