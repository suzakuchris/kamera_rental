@extends('layouts.app')

@section('css')

@endsection

@section('content_header')
@if($mode == 'add')
Add New Mitra
@elseif($mode == 'view')
View Mitra
@elseif($mode == 'edit')
Edit Mitra
@endif
@endsection

@section('content')
<form method="POST" action="{{route('master.mitra.upsert')}}" onsubmit="pre_submit(event, this);">
    <fieldset class="border p-2">
        {{ csrf_field() }}
        <input type="hidden" name="mitra_id" @if(isset($mitra)) value="{{$mitra->mitra_id}}" @endif>
        <legend class="w-auto">Data Mitra</legend>
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
                            <label>Nama Mitra</label>
                            <input type="text" name="mitra_name" class="form-control" required placeholder="Masukan nama mitra" @if(old('mitra_name')) value="{{old('mitra_name')}}" @elseif(isset($mitra)) value="{{$mitra->mitra_name}}" @endif>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group">
                            <label>Nama Perusahaan</label>
                            <input type="text" name="mitra_company" class="form-control" required placeholder="Masukan nama perusahaan mitra" @if(old('mitra_company')) value="{{old('mitra_company')}}" @elseif(isset($mitra)) value="{{$mitra->mitra_company}}" @endif>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group">
                            <label>Suffix Code</label>
                            @if($mode != 'add')
                            <input type="text" class="form-control" readonly placeholder="Masukan kode unik mitra" @if(old('code')) value="{{old('code')}}" @elseif(isset($mitra)) value="{{$mitra->suffix_code}}" @endif>
                            @else
                            <input type="text" name="code" class="form-control" required placeholder="Masukan kode unik mitra" @if(old('code')) value="{{old('code')}}" @elseif(isset($mitra)) value="{{$mitra->suffix_code}}" @endif>
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