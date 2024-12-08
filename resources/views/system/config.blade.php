@extends('layouts.app')

@section('content_header')
Configuration
@endsection

@section('content')
<form method="POST" enctype='multipart/form-data' action="{{route('config.main.save')}}" onsubmit="pre_submit(event, this);">
    {{ csrf_field() }}
    <div class="row mx-0">
        <div class="col-12">
            @if(isset($errors) && count($errors->all()) > 0)
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
        </div>
    </div>
    <div class="row mx-0">
        <div class="col-12">
            <div class="row mx-0">
                <div class="col-12">
                    <div class="form-group">
                        <label>Nama Site</label>
                        <input name="site_name" value="{{site_config()->site_name}}" class="form-control" required placeholder="Masukan nama produk" @if(old('product_name')) value="{{old('product_name')}}" @elseif(isset($product)) value="{{$product->product_name}}" @endif>
                    </div>
                </div>
                <div class="col-12">
                    <div class="form-group">
                        <div>Site Logo</div>
                        <div class="p-2 py-3 border">
                            <img style="max-height:50px;" src="{{asset(site_config()->site_logo)}}">
                        </div>
                        <input type="file" name="site_logo" class="form-control">
                    </div>
                </div>
                <div class="col-12">
                    <div class="form-group">
                        <div>Site Banner</div>
                        <div class="p-2 py-3 border">
                            <img style="max-height:50px;" src="{{asset(site_config()->site_banner)}}">
                        </div>
                        <input type="file" name="site_banner" class="form-control">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <button type="submit" id="SubmitBtn" class="d-none">
</form>
@endsection

@section('content_footer')
<div class="row mx-0">
    <div class="col"></div>
    <div class="col-auto">
        <label for="SubmitBtn" class="btn btn-success"><i class="bi bi-save me-2"></i>Save</label>
    </div>
</div>
@endsection

@section('js')
<script>
    function pre_submit(event, form){
        event.preventDefault();
        var isValid = form.reportValidity();
        if(isValid){
            Swal.fire({
                title: "Apakah anda yakin mau menyimpan data?",
                showDenyButton: true,
                showCancelButton: false,
                confirmButtonText: "Yes",
                denyButtonText: `No`
            }).then((result) => {
                /* Read more about isConfirmed, isDenied below */
                if (result.isConfirmed) {
                    showLoading();
                    $(form).removeAttr('onsubmit');
                    $(form).submit();
                } else if (result.isDenied) {
                    // Swal.fire("Changes are not saved", "", "info");
                }
            });
        }
    }
</script>
@endsection