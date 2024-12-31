@extends('layouts.app')

@section('css')

@endsection

@section('content_header')
@if($mode == 'add')
Add New Item
@elseif($mode == 'view')
View Item
@elseif($mode == 'edit')
Edit Item
@endif
@endsection

@section('content')
<form method="POST" action="{{route('master.item.upsert')}}" onsubmit="pre_submit(event, this);">
    <fieldset class="border p-2">
        {{ csrf_field() }}
        <input type="hidden" name="product_id" @if(isset($product)) value="{{$product->product_id}}" @endif>
        <input type="hidden" name="item_id" @if(isset($item)) value="{{$item->item_id}}" @endif>
        <legend class="w-auto">Data Item</legend>
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
                            <label>Serial Number</label>
                            <input name="item_code" class="form-control" required placeholder="Masukan serial number item" @if(old('item_code')) value="{{old('item_code')}}" @elseif(isset($item)) value="{{$item->item_code}}" @endif>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label>Owner</label>
                            <select name="item_owner" class="select-searchable form-control" required>
                                <option value="" disabled selected>--Pilih Owner Produk--</option>
                                <optgroup label="Mitra">
                                    @foreach($mitras as $mitra)
                                    <option value="{{'1'.$mitra->mitra_id}}" @if(old('item_owner') == '1'.$mitra->mitra_id || (isset($item) && $item->item_owner_type.$item->item_owner == '1'.$mitra->mitra_id)) selected @endif>{{$mitra->mitra_name}}</option>
                                    @endforeach
                                </optgroup>
                                <optgroup label="Customer">
                                    @foreach($customers as $customer)
                                    <option value="{{'0'.$customer->customer_id}}" @if(old('item_owner') == '0'.$customer->customer_id || (isset($item) && $item->item_owner_type.$item->item_owner == '0'.$customer->customer_id)) selected @endif>{{$customer->customer_name}}</option>
                                    @endforeach
                                </optgroup>
                            </select>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label>Kondisi</label>
                            <select name="item_condition" class="select-searchable form-control" required>
                                <option value="" disabled selected>--Pilih Kondisi--</option>
                                @foreach($conditions as $condition)
                                <option value="{{$condition->condition_id}}" @if(old('item_condition') == $condition->condition_id || (isset($item) && $item->item_condition == $condition->condition_id)) selected @endif>{{$condition->condition_name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group">
                            <label>Status</label>
                            <select name="item_status" class="select-searchable form-control" required>
                                <option value="" disabled selected>--Pilih Status--</option>
                                @foreach($status as $stat)
                                <option value="{{$stat->status_id}}" @if(old('item_status') == $stat->status_id || (isset($item) && $item->item_status == $stat->status_id)) selected @endif>{{$stat->status_name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label>Harga per hari</label>
                            <input type="text" placeholder="Harga sewa perhari" data-target="harga_per_hari" class="form-control comma-separated" name="harga_per_hari_show" required @if(old('harga_per_hari_show')) value="{{old('harga_per_hari_show')}}" @elseif(isset($item)) value="{{number_format($item->item_harga_perhari,0,'', ',')}}" @endif>
                            <input type="hidden" name="harga_per_hari" id="harga_per_hari" @if(old('harga_per_hari')) value="{{old('harga_per_hari')}}" @elseif(isset($item)) value="{{$item->item_harga_perhari}}" @endif>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label>Harga perolehan</label>
                            <input type="text" placeholder="Harga barang diperoleh" data-target="harga_perolehan" class="form-control comma-separated" name="harga_perolehan_show" required @if(old('harga_perolehan_show')) value="{{old('harga_perolehan_show')}}" @elseif(isset($item)) value="{{number_format($item->item_harga_perolehan,0,'', ',')}}" @endif>
                            <input type="hidden" name="harga_perolehan" id="harga_perolehan" @if(old('harga_perolehan')) value="{{old('harga_perolehan')}}" @elseif(isset($item)) value="{{($item->item_harga_perolehan)}}" @endif>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group">
                            <label>Catatan</label>
                            <textarea class="form-control" name="item_notes" rows="4">@if(old('item_notes')) {{old('item_notes')}} @elseif(isset($item)) {{$item->item_notes}} @endif</textarea>
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