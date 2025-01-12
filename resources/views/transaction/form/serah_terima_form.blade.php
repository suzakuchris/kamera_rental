@extends('layouts.app')

@section('css')
<style>
    #image_table {
        counter-reset: row-num;
    }
    #image_table tbody tr  {
        counter-increment: row-num;
    }

    #image_table tbody tr:not(.no-data) td:first-child::before {
        content: counter(row-num) ". ";
    }
    #image_table tbody tr:not(.no-data) td:first-child {
        text-align: center;
    }

    #image_table tbody tr:first-child .btn-up{
        display:none;
    }

    #image_table tbody tr:last-child .btn-down{
        display:none;
    }
</style>
<style>
    #detail-table tbody tr:not(.no-data, .detail-bundle){
        counter-increment: rowNumber;
    }

    #detail-table tbody tr:not(.no-data, .detail-bundle) td:first-child::before {
        display: table-cell;
        content: counter(rowNumber) ".";
        padding-right: 0.3em;
        text-align: right;
    }

    #detail-table input{
        text-align:end;
    }

    .table td{
        vertical-align:top;
    }
</style>
@endsection

@section('content_header')
@if($mode == 'add')
Add New Payment
@elseif($mode == 'view')
View Payment
@elseif($mode == 'edit')
Edit Payment
@endif
@endsection

@section('content')
<form method="POST" action="{{route('transaction.rent.serah_terima.upsert')}}" onsubmit="pre_submit(event, this);">
    <fieldset class="border p-2" @if($mode == 'view') disabled @endif>
        {{ csrf_field() }}
        <input type="hidden" name="header_id" @if(isset($serah_terima)) value="{{$serah_terima->header_id}}" @endif>
        <input type="hidden" name="transaction_id" @if(isset($transaction)) value="{{$transaction->transaction_id}}" @endif>
        <legend class="w-auto">
            @if($mode == 'view')
                <a class="bi bi-chevron-left me-2" href="{{route('transaction.rent.serah_terima.view', ['transaction_id' => $transaction->transaction_id])}}"></a>
            @else
                <a class="bi bi-chevron-left me-2" href="{{route('transaction.rent.view', ['transaction_id' => $transaction->transaction_id])}}"></a>
            @endif 
            Data Serah Terima
        </legend>
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
                        <table class="table">
                            <tr>
                                <td>No. Invoice</td>
                                <td>{{$transaction->transaction_number}}</td>
                            </tr>
                            <tr>
                                <td>Customer</td>
                                <td>{{$transaction->customer->customer_name}} - {{$transaction->customer->customer_phone}}</td>
                            </tr>
                            <tr>
                                <td>Tanggal Sewa</td>
                                <td>
                                    {{\Carbon\Carbon::parse($transaction->transaction_tgl_ambil)->format('d M Y')}}
                                    -
                                    {{\Carbon\Carbon::parse($transaction->transaction_tgl_pemulangan)->format('d M Y')}}
                                </td>
                            </tr>
                            <tr>
                                <td>Rekening</td>
                                <td>
                                    {{$transaction->rekening->rekening_atas_nama}} - 
                                    {{$transaction->rekening->rekening_nama_bank}} - 
                                    {{$transaction->rekening->rekening_number}}
                                </td>
                            </tr>
                            <tr>
                                <td>Status</td>
                                <td>
                                    <select class="form-control" name="serah_terima_status" onchange="disable_some(this);" required>
                                        <option value="">--Pilih Status--</option>
                                        <option value="1" @if(isset($serah_terima) && $serah_terima->header_status == 1) selected @endif>Serah</option>
                                        <option value="2" @if(isset($serah_terima) && $serah_terima->header_status == 2) selected @endif>Terima</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td>Tanggal Serah/Terima</td>
                                <td>
                                    <input type="datetime-local" name="tanggal_serah_terima" class="form-control" required @if(isset($serah_terima)) value="{{datetime_stamp($serah_terima->header_datetime)}}" @endif>
                                </td>
                            </tr>
                            <tr>
                                <td>Keterangan</td>
                                <td>
                                    <textarea class="form-control" rows="4" name="keterangan" required>@if(isset($serah_terima)){{$serah_terima->header_notes}}@endif</textarea>
                                </td>
                            </tr>
                            <tr>
                                <td>Alat yang akan di serah terima</td>
                                <td>
                                    <table id="detail-table" class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <td>No.</td>
                                                <td>Nama Alat</td>
                                                <td>Brand</td>
                                                <td>Ownership</td>
                                                <td class="auto-width">Include</td>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $bundle_unique_code = [];
                                            @endphp
                                            @foreach($transaction->details as $detail)
                                            @if($detail->item_bundle == 1)
                                                @if(!isset($detail->item_id))
                                                @php
                                                    if(!isset($bundle_unique_code[$detail->transaction_detail_id])){
                                                        $bundle_unique_code[$detail->transaction_detail_id] = uniqid();
                                                    }
                                                    $unique_row = $bundle_unique_code[$detail->transaction_detail_id];
                                                @endphp
                                                <tr>
                                                    <td>
                                                        <input type="hidden" name="details_keep[{{$unique_row}}][transaction_detail_id]" value="{{$detail->transaction_detail_id}}">
                                                        <input type="hidden" name="details_keep[{{$unique_row}}][product_type]" value="bundle">
                                                        <input type="hidden" name="details_keep[{{$unique_row}}][bundle_id]" value="{{$detail->bundle->bundle_id}}">
                                                    </td>
                                                    <td colspan="3">{{$detail->bundle->bundle_name}}</td>
                                                    <td></td>
                                                </tr>
                                                @else
                                                @php
                                                    if(!isset($bundle_unique_code[$detail->item_bundle_id])){
                                                        $bundle_unique_code[$detail->item_bundle_id] = uniqid();
                                                    }
                                                    $unique_row = $bundle_unique_code[$detail->item_bundle_id];
                                                    $detail_numbering = uniqid();
                                                @endphp
                                                <tr class="detail-bundle detail-bundle-{{$detail->item_bundle_id}}">
                                                    <td>
                                                        <i class="bi bi-arrow-return-right icon"></i>
                                                        <input type="hidden" name="details_keep[{{$unique_row}}][details][{{$detail_numbering}}][transaction_detail_id]" value="{{$detail->transaction_detail_id}}">
                                                        <input type="hidden" name="details_keep[{{$unique_row}}][details][{{$detail_numbering}}][product_type]" value="product">
                                                        <input type="hidden" name="details_keep[{{$unique_row}}][details][{{$detail_numbering}}][product_id]" value="{{$detail->product->product_id}}">
                                                    </td>
                                                    <td>{{$detail->product->product_name}}</td>
                                                    <td>{{$detail->product->brand->product_brand_name}}</td>
                                                    <td>
                                                        @php
                                                            $item_collections = $detail->product->available_items_except($detail->item_id);
                                                        @endphp
                                                        @foreach($item_collections as $itema)
                                                        @php
                                                            $owner = $itema->getOwner();
                                                            if($itema->item_owner_type == 1){
                                                                $owner_name = $owner->mitra_name;
                                                            }else{
                                                                $owner_name = $owner->customer_name;
                                                            }
                                                            
                                                            $selected = '';
                                                            if($itema->item_id == $detail->item_id){
                                                                $selected = 'selected';
                                                            }
                                                        @endphp
                                                        @if($selected)
                                                            {{$itema->item_code}} - {{$owner_name}}
                                                        @endif
                                                        @endforeach
                                                    </td>
                                                    <td class="text-center">
                                                        <input class="checkbox_{{$detail->item_return}}" type="checkbox" name="details_keep[{{$unique_row}}][details][{{$detail_numbering}}][included]" @if(isset($serah_terima)) @if(in_details($serah_terima, $detail->transaction_detail_id)) checked @endif @endif>
                                                    </td>
                                                </tr>
                                                @endif
                                            @else
                                            @php
                                                $unique_row = uniqid();
                                            @endphp
                                            <tr>
                                                <td>
                                                    <input type="hidden" name="details_keep[{{$unique_row}}][transaction_detail_id]" value="{{$detail->transaction_detail_id}}">
                                                    <input type="hidden" name="details_keep[{{$unique_row}}][product_type]" value="product">
                                                    <input type="hidden" name="details_keep[{{$unique_row}}][product_id]" value="{{$detail->product->product_id}}">
                                                </td>
                                                <td>{{$detail->product->product_name}}</td>
                                                <td>{{$detail->product->brand->product_brand_name}}</td>
                                                <td>
                                                    @php
                                                        $item_collections = $detail->product->available_items_except($detail->item_id);
                                                    @endphp
                                                    @foreach($item_collections as $itema)
                                                    @php
                                                        $owner = $itema->getOwner();
                                                        if($itema->item_owner_type == 1){
                                                            $owner_name = $owner->mitra_name;
                                                        }else{
                                                            $owner_name = $owner->customer_name;
                                                        }
                                                        
                                                        $selected = '';
                                                        if($itema->item_id == $detail->item_id){
                                                            $selected = 'selected';
                                                        }
                                                    @endphp
                                                    @if($selected)
                                                        {{$itema->item_code}} - {{$owner_name}}
                                                    @endif
                                                    @endforeach
                                                </td>
                                                <td class="text-center">
                                                    <input class="checkbox_{{$detail->item_return}}" type="checkbox" name="details_keep[{{$unique_row}}][included]" @if(isset($serah_terima)) @if(in_details($serah_terima, $detail->transaction_detail_id)) checked @endif @endif>
                                                </td>
                                            </tr>
                                            @endif
                                            @endforeach
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td>Lampiran</td>
                                <td>
                                    <table id="image_table" class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th class="auto-width">No.</th>
                                                <th>Gambar / Images</th>
                                                <th class="auto-width">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @if(isset($serah_terima->images) && count($serah_terima->images) > 0)
                                                @foreach($serah_terima->images as $i)
                                                <tr class="input-row">
                                                    <td></td>
                                                    <td>
                                                        <div class="image-wrapper">
                                                            <img style="width:100px;height:auto;max-height:200px;" src="{{asset($i->image_path.$i->image_name)}}">
                                                        </div>
                                                        <div class="input-wrapper">
                                                            <input type="hidden" name="serah_terima_images_keep[]" value="{{$i->image_id}}">
                                                        </div>
                                                    </td>
                                                    <td>
                                                        @if($mode != 'view')
                                                        <div class="btn-group">
                                                            <button type="button" onclick="move_up(this);" class="btn btn-primary btn-up"><i class="bi bi-arrow-up"></i></button>
                                                            <button type="button" onclick="move_down(this);" class="btn btn-primary btn-down"><i class="bi bi-arrow-down"></i></button>
                                                            <button type="button" onclick="remove(this);" class="btn btn-danger btn-delete"><i class="bi bi-trash"></i></button>
                                                        </div>
                                                        @endif
                                                    </td>
                                                </tr>
                                                @endforeach
                                            @else
                                            <tr class="no-data">
                                                <td colspan="3">Tambahkan gambar..</td>
                                            </tr>
                                            @endif
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td class="input-wrapper" colspan="2">
                                                    <input accept="image/*" type="file" class="form-control imageinput">
                                                    <input type="text" class="d-none base64input">
                                                </td>
                                                <td>
                                                    <button onclick="add_image(this);" type="button" class="btn btn-sm btn-primary d-flex align-items-center" type="button"><i class="bi bi-plus me-2"></i>Add</button>
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <button type="submit" id="SubmitBtn" class="d-none">
    </fieldset>
</form>
@endsection

@section('content_footer')
@if($mode != 'view')
<div class="row mx-0">
    <div class="col"></div>
    <div class="col-auto">
        <label for="SubmitBtn" class="btn btn-success"><i class="bi bi-save me-2"></i>Save</label>
    </div>
</div>
@endif
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
<script>
    File.prototype.convertToBase64 = function(callback){
        var reader = new FileReader();
        reader.onloadend = function (e) {
            callback(e.target.result, e.target.error);
        };   
        reader.readAsDataURL(this);
    };
    $(".imageinput").on('change', function(){
        var selectedFile = this.files[0];
        var input = $(this);
        selectedFile.convertToBase64(function(base64){
            var next = input.next().val(base64);
        });
    });

    function add_image(btn){
        var btn = $(btn);
        var row = btn.closest('tr');
        var table = row.closest('table');
        var body = table.find('tbody');
        var input = row.find('.imageinput');
        var input_b64 = row.find('.base64input');

        if(input_b64.val() == ""){
            Swal.fire("Silahkan pilih foto dulu");
            return;
        }

        var _row = $("#factory-table tr.input-row").clone();
        var image = `<img style="width:100px;height:auto;max-height:200px;" src="`+input_b64.val()+`">`;
        _row.find(".image-wrapper").html(image);
        // input.addClass('d-none').appendTo(_row.find(".input-wrapper"));
        input.val('');
        input_b64.attr('name', 'serah_terima_images_add[]').appendTo(_row.find(".input-wrapper"));

        body.find('.no-data').remove();
        body.append(_row);

        row.find('.input-wrapper').append(`<input type="text" class="d-none base64input">`);
    }

    function move_up(btn){
        var btn = $(btn);
        var row = btn.closest('tr');
        row.insertBefore(row.prev());
    }

    function move_down(btn){
        var btn = $(btn);
        var row = btn.closest('tr');
        row.insertAfter(row.next());
    }

    function remove(btn){
        var btn = $(btn);
        var tbody = btn.closest('tbody');
        btn.closest('tr').remove();

        if(tbody.children().length < 1){
            tbody.append($("#factory-table .no-data").clone());
        }
    }
</script>
<script>
    function disable_some(select){
        var select = $(select);
        var value = select.val();

        $(".checkbox_0").removeAttr('disabled');
        $(".checkbox_1").removeAttr('disabled');
        $(".checkbox_2").removeAttr('disabled');
        $(".checkbox_0").prop('checked', false);
        $(".checkbox_1").prop('checked', false);
        $(".checkbox_2").prop('checked', false);

        if(value == ""){
            return;
        }

        if(value == "1"){
            $(".checkbox_1").attr('disabled', true);
            $(".checkbox_2").attr('disabled', true);
        };

        if(value == "2"){
            $(".checkbox_0").prop('disabled', true);
            $(".checkbox_2").attr('disabled', true);
        };
    }
</script>
@endsection

@section('footer')
<table class="d-none" id="factory-table">
    <tr class="input-row">
        <td></td>
        <td>
            <div class="image-wrapper"></div>
            <div class="input-wrapper"></div>
        </td>
        <td>
            <div class="btn-group">
                <button type="button" onclick="move_up(this);" class="btn btn-primary btn-up"><i class="bi bi-arrow-up"></i></button>
                <button type="button" onclick="move_down(this);" class="btn btn-primary btn-down"><i class="bi bi-arrow-down"></i></button>
                <button type="button" onclick="remove(this);" class="btn btn-danger btn-delete"><i class="bi bi-trash"></i></button>
            </div>
        </td>
    </tr>
    <tr class="input-row-visible">
        <td>
            <input type="file" accept="image/*" class="form-control imageinput">
            <input type="text" class="d-none base64input">
        </td>
    </tr>
    <tr class="no-data">
        <td colspan="3">Tambahkan gambar..</td>
    </tr>
</table>
@endsection