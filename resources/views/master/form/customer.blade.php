@extends('layouts.app')

@section('css')

@endsection

@section('content_header')
@if($mode == 'add')
Add New Customer
@elseif($mode == 'view')
View Customer
@elseif($mode == 'edit')
Edit Customer
@endif
@endsection

@section('content')
<form method="POST" action="{{route('master.customer.upsert')}}" onsubmit="pre_submit(event, this);">
    <fieldset class="border p-2">
        {{ csrf_field() }}
        <input type="hidden" name="customer_id" @if(isset($customer)) value="{{$customer->customer_id}}" @endif>
        <legend class="w-auto">Data Customer</legend>
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
                            <label>Nama Customer</label>
                            <input type="text" name="customer_name" class="form-control" required placeholder="Masukan nama customer" @if(old('customer_name')) value="{{old('customer_name')}}" @elseif(isset($customer)) value="{{$customer->customer_name}}" @endif>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group">
                            <label>Email Customer</label>
                            <input type="email" name="customer_email" class="form-control" placeholder="Masukan email customer" @if(old('customer_email')) value="{{old('customer_email')}}" @elseif(isset($customer)) value="{{$customer->customer_email}}" @endif>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group">
                            <label>No.HP Customer</label>
                            <input type="number" name="customer_phone" class="form-control" placeholder="Masukan no.hp customer" @if(old('customer_phone')) value="{{old('customer_phone')}}" @elseif(isset($customer)) value="{{$customer->customer_phone}}" @endif>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group">
                            <label>Alamat</label>
                            <textarea class="form-control" name="customer_address" rows="4">@if(old('customer_address')) {{old('customer_address')}} @elseif(isset($customer)) {{$customer->customer_address}} @endif</textarea>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group">
                            <label>Suffix Code</label>
                            @if($mode != 'add')
                            <input type="text" class="form-control" readonly placeholder="Masukan kode unik customer" @if(old('code')) value="{{old('code')}}" @elseif(isset($customer)) value="{{$customer->suffix_code}}" @endif>
                            @else
                            <input type="text" pattern="(.){0,6}" required title="0-6 characters" maxlength="6" name="code" class="form-control" required placeholder="Masukan kode unik customer" @if(old('code')) value="{{old('code')}}" @elseif(isset($customer)) value="{{$customer->suffix_code}}" @endif>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <button type="submit" id="SubmitBtn" class="d-none">
    </fieldset>
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

@section('footer')

@endsection