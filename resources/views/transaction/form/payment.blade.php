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
<form method="POST" action="{{route('transaction.rent.payment.upsert')}}" onsubmit="pre_submit(event, this);">
    <fieldset class="border p-2">
        {{ csrf_field() }}
        <input type="hidden" name="entry_id" @if(isset($entry)) value="{{$entry->entry_id}}" @endif>
        <input type="hidden" name="transaction_id" @if(isset($transaction)) value="{{$transaction->transaction_id}}" @endif>
        <legend class="w-auto">Data Payment</legend>
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
                                <td>Nominal</td>
                                <td>
                                    <input type="text" placeholder="Jumlah Bayar" data-target="jumlah_bayar" class="form-control comma-separated" name="jumlah_bayar_show" required @if(isset($entry)) @if($entry->entry_cashflow == 1) value="{{comma_separated($entry->entry_debit)}}" @else value="{{comma_separated($entry->entry_credit)}}" @endif @endif>
                                    <input type="hidden" name="jumlah_bayar" id="jumlah_bayar" class="jumlah_bayar" required @if(isset($entry)) @if($entry->entry_cashflow == 1) value="{{($entry->entry_debit)}}" @else value="{{($entry->entry_credit)}}" @endif @endif>
                                </td>
                            </tr>
                            <tr>
                                <td>Tanggal Payment</td>
                                <td>
                                    <input type="datetime-local" name="tanggal_payment" class="form-control" required @if(isset($entry)) value="{{$entry->entry_date}}" @endif>
                                </td>
                            </tr>
                            <tr>
                                <td>Keterangan</td>
                                <td>
                                    <textarea class="form-control" rows="4" name="keterangan" required>@if(isset($entry)){{$entry->entry_notes}}@endif</textarea>
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
                                            @if(isset($entry->images) && count($entry->images) > 0)
                                                @foreach($entry->images as $i)
                                                <tr class="input-row">
                                                    <td></td>
                                                    <td>
                                                        <div class="image-wrapper">
                                                            <img style="width:100px;height:auto;max-height:200px;" src="{{asset($i->image_path.$i->image_name)}}">
                                                        </div>
                                                        <div class="input-wrapper">
                                                            <input type="hidden" name="payment_images_keep[]" value="{{$i->image_id}}">
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group">
                                                            <button type="button" onclick="move_up(this);" class="btn btn-primary btn-up"><i class="bi bi-arrow-up"></i></button>
                                                            <button type="button" onclick="move_down(this);" class="btn btn-primary btn-down"><i class="bi bi-arrow-down"></i></button>
                                                            <button type="button" onclick="remove(this);" class="btn btn-danger btn-delete"><i class="bi bi-trash"></i></button>
                                                        </div>
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
        input_b64.attr('name', 'payment_images_add[]').appendTo(_row.find(".input-wrapper"));

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