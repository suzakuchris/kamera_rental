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
    #product_table {
        counter-reset: row-num;
    }
    #product_table tbody tr  {
        counter-increment: row-num;
    }

    #product_table tbody tr:not(.no-data) td:first-child::before {
        content: counter(row-num) ". ";
    }
    #product_table tbody tr:not(.no-data) td:first-child {
        text-align: center;
    }

    #product_table tbody tr:first-child .btn-up{
        display:none;
    }

    #product_table tbody tr:last-child .btn-down{
        display:none;
    }
</style>
@endsection

@section('content_header')
@if($mode == 'add')
Add New Bundle
@elseif($mode == 'view')
View Product Bundle
@elseif($mode == 'edit')
Edit Product Bundle
@endif
@endsection

@section('content')
<form method="POST" action="{{route('master.product_bundle.upsert')}}" onsubmit="pre_submit(event, this);">
    <fieldset class="border p-2">
        {{ csrf_field() }}
        <input type="hidden" name="bundle_id" @if(isset($bundle)) value="{{$bundle->bundle_id}}" @endif>
        <legend class="w-auto">Data Bundling Product</legend>
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
                            <label>Nama Bundle</label>
                            <input name="bundle_name" class="form-control" required placeholder="Masukan nama bundle / paket" @if(old('bundle_name')) value="{{old('bundle_name')}}" @elseif(isset($bundle)) value="{{$bundle->bundle_name}}" @endif>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group">
                            <label>Harga</label>
                            <input class="form-control comma-separated" data-target="bundle_price" required placeholder="Masukan estimasi harga sewa / hari" @if(old('bundle_price')) value="{{number_format(old('bundle_price'), 0, '', ',')}}" @elseif(isset($bundle)) value="{{number_format($bundle->bundle_harga_perhari, 0, '', ',')}}" @endif>
                            <input type="hidden" id="bundle_price" name="bundle_price" @if(old('bundle_price')) value="{{old('bundle_price')}}" @elseif(isset($bundle)) value="{{$bundle->bundle_harga_perhari}}" @endif>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group">
                            <label>Deskripsi</label>
                            <textarea class="form-control" name="bundle_description" rows="4">@if(old('bundle_description')) {{old('bundle_description')}} @elseif(isset($bundle)) {{$bundle->bundle_description}} @endif</textarea>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group">
                            <label>Spesifikasi</label>
                            <textarea class="form-control" name="bundle_specification" rows="4">@if(old('bundle_specification')) {{old('bundle_specification')}} @elseif(isset($bundle)) {{$bundle->bundle_specification}} @endif</textarea>
                        </div>
                    </div>
                    <div class="col-12">
                        <table id="product_table" class="table table-bordered">
                            <thead>
                                <tr>
                                    <th class="auto-width">No.</th>
                                    <th>Nama Produk</th>
                                    <th class="auto-width">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(isset($bundle))
                                @foreach($bundle_product as $detail)
                                <tr class="input-row">
                                    <td></td>
                                    <td>
                                        <div class="nama-wrapper">{{$detail->product_name}}</div>
                                        <div class="input-wrapper">
                                            <input type="hidden" name="product_keep[]" value="{{$detail->bundle_detail_id}}">
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
                                    <td colspan="3">Tambahkan produk..</td>
                                </tr>
                                @endif
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="2">
                                        <select class="productInput form-control w-100">
                                            <option value="" disabled selected>Pilih Produk untuk ditambahkan</option>
                                            @foreach($products as $product)
                                            <option value="{{$product->product_id}}">{{$product->product_name}}</option>
                                            @endforeach
                                        </select> 
                                    </td>
                                    <td>
                                        <button onclick="add_product(this);" type="button" class="btn btn-sm btn-primary d-flex align-items-center" type="button"><i class="bi bi-plus me-2"></i>Add</button>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div class="col-12">
                        <table id="image_table" class="table table-bordered">
                            <thead>
                                <tr>
                                    <th class="auto-width">No.</th>
                                    <th>Gambar / Images</th>
                                    <th class="auto-width">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(isset($images) && count($images) > 0)
                                    @foreach($images as $i)
                                    <tr class="input-row">
                                        <td></td>
                                        <td>
                                            <div class="image-wrapper">
                                                <img style="width:100px;height:auto;max-height:200px;" src="{{asset($i->image_path.$i->image_name)}}">
                                            </div>
                                            <div class="input-wrapper">
                                                <input type="hidden" name="bundle_images_keep[]" value="{{$i->image_id}}">
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
                                        <input accept="image/png, image/gif, image/jpeg" type="file" class="form-control imageinput">
                                        <input type="text" class="d-none base64input">
                                    </td>
                                    <td>
                                        <button onclick="add_image(this);" type="button" class="btn btn-sm btn-primary d-flex align-items-center" type="button"><i class="bi bi-plus me-2"></i>Add</button>
                                    </td>
                                </tr>
                            </tfoot>
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
        input_b64.attr('name', 'bundle_images_add[]').appendTo(_row.find(".input-wrapper"));

        body.find('.no-data').remove();
        body.append(_row);

        row.find('.input-wrapper').append(`<input type="text" class="d-none base64input">`);
    }

    function add_product(btn){
        var btn = $(btn);
        var row = btn.closest('tr');
        var table = row.closest('table');
        var body = table.find('tbody');
        var input = row.find('.productInput');

        console.log("value:"+input.val());
        if(input.val() == "" || input.val() == null || input.val() == "null"){
            Swal.fire("Silahkan pilih Produk dulu");
            return;
        }

        var _row = $("#factory-table tr.input-row-product").clone();
        _row.find(".name-wrapper").html(input.find('option:selected').text());
        _row.find(".input-wrapper input[name='product_add[]']").val(input.val());
        body.find('.no-data').remove();
        body.append(_row);
        input.val('');
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
    <tr class="input-row-product">
        <td></td>
        <td>
            <div class="name-wrapper"></div>
            <div class="input-wrapper">
                <input type="hidden" name="product_add[]">
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
    <tr class="input-row-visible">
        <td>
            <input type="file" class="form-control imageinput">
            <input type="text" class="d-none base64input">
        </td>
    </tr>
    <tr class="no-data">
        <td colspan="3">Tambahkan gambar..</td>
    </tr>
</table>
@endsection