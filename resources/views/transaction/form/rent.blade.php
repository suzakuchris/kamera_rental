@extends('layouts.app')

@section('css')
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
</style>
@endsection

@section('content_header')
@if($mode == 'add')
Add New Transaction
@elseif($mode == 'view')
View Transaction
@elseif($mode == 'edit')
Update Transaction
@endif
@endsection

@section('content')
<form method="POST" action="{{route('transaction.rent.upsert')}}" onsubmit="pre_submit(event, this);">
    <fieldset class="border p-2">
        {{ csrf_field() }}
        <input type="hidden" name="transaction_id" @if(isset($header)) value="{{$header->transaction_id}}" @endif>
        <button type="submit" id="SubmitBtn" class="d-none"></button>
        <div class="row">
            <div class="col-12 mb-2 text-center">
                <h4>Form Transaksi</h4>
            </div>
            <div class="col-12">
                <table class="table header-table w-100">
                    <tbody>
                        <tr>
                            <td colspan="2"><h5>Data Transaksi</h5></td>
                        </tr>
                        <tr>
                            <td>No. Invoice</td>
                            <td>
                                <input type="text" @if($mode == 'add') value="-" @else value="{{$header->transaction_number}}" @endif disabled class="form-control">
                            </td>
                        </tr>
                        <tr>
                            <td>Customer</td>
                            <td>
                                <div class="input-group row mx-0">
                                    @if($mode == 'add')
                                    <div class="col p-0">
                                        <select style="width:100%;" name="customer_id" class="w-100 form-control select-searchable">
                                            <option value="">--Silahkan Pilih Customer--</option>
                                            @foreach($customers as $customer)
                                            <option value="{{$customer->customer_id}}">{{$customer->customer_name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-auto p-0">
                                        <a href="{{route('master.customer')}}" class="btn btn-sm btn-primary">+ Add New</a>
                                    </div>
                                    @else
                                    <div class="col p-0">
                                        <input type="text" class="form-control" value="{{$header->customer->customer_name}}" readonly>
                                    </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>Tanggal Sewa</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <input type="date" name="date_start" class="form-control" @if($mode != "add") value="{{\Carbon\Carbon::parse($header->transaction_tgl_ambil)->format('Y-m-d')}}" @else value="{{date('Y-m-d')}}" @endif>
                                    <b class="mx-2">-</b>
                                    <input type="date" name="date_end" class="form-control" @if($mode != "add") value="{{\Carbon\Carbon::parse($header->transaction_tgl_pemulangan)->format('Y-m-d')}}" @else value="{{date('Y-m-d')}}" @endif>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>Rekening</td>
                            <td>
                                @if($mode == 'add')
                                <select style="width:100%;" name="rekening_id" class="w-100 form-control select-searchable">
                                    <option value="">--Silahkan Pilih Rekening--</option>
                                    @foreach($rekenings as $rekening)
                                    <option value="{{$rekening->rekening_id}}">{{$rekening->rekening_number}} - {{$rekening->rekening_atas_nama}}</option>
                                    @endforeach
                                </select>
                                @else
                                <div class="col p-0">
                                    <input type="text" class="form-control" value="{{$header->rekening->rekening_number}} - {{$header->rekening->rekening_atas_nama}}" readonly>
                                </div>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td style="vertical-align:top;">Catatan</td>
                            <td>
                                <textarea rows="3" class="form-control" name="notes" placeholder="Catatan Tambahan..">@if($mode != 'add'){{$header->transaction_notes}}@endif</textarea>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="col-12">
                <table id="detail-table" class="table w-100">
                    <thead>
                        <tr>
                            <td colspan="9"><h5>Alat yang Disewa</h5></td>
                        </tr>
                        <tr>
                            <td colspan="9">
                                <div class="input-group mb-1 flex-nowrap">
                                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                                    <select id="search-select" class="w-100 form-control select-searchable" style="width:100% !important">
                                        <option value="">Cari Alat / Produk</option>
                                        <optgroup label="Produk Satuan">
                                            @foreach($products as $product)
                                            <option value="{{$product->product_id}}" data-group="product">{{$product->product_name}}</option>
                                            @endforeach
                                        </optgroup>
                                        <optgroup label="Produk Bundling">
                                            @foreach($bundles as $bundle)
                                            <option value="{{$bundle->bundle_id}}" data-group="bundle">{{$bundle->bundle_name}}</option>
                                            @endforeach
                                        </optgroup>
                                    </select>
                                    <button class="btn btn-primary d-flex align-items-center" onclick="add_row();" type="button"><i class="bi bi-plus me-1"></i>Add</button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>No.</td>
                            <td>Nama Alat</td>
                            <td>Brand</td>
                            <td>Ownership</td>
                            <td>Day</td>
                            <td>Harga Sewa per hari</td>
                            <td>Total Harga Sewa</td>
                            <td class="auto-width">@if($mode != 'add') Return @endif</td>
                            <td class="auto-width"></td>
                        </tr>
                    </thead>
                    <tbody>
                        @if($mode == 'add')
                        <tr class="no-data">
                            <td colspan="9" class="text-center">
                                Belum Ada Data
                            </td>
                        </tr>
                        @else
                            @php
                                $bundle_unique_code = [];
                            @endphp
                            @foreach($header->details as $detail)
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
                                    <td>
                                        <input type="number" class="form-control days_rent" min="0" max="100" style="width:75px;" @if($mode == "add") value="0" @else value="{{$detail->item_days_rent}}" @endif name="details_keep[{{$unique_row}}][days_rent]">
                                    </td>
                                    <td>
                                        <input type="text" placeholder="Harga sewa perhari" data-target="harga_per_hari{{$unique_row}}" class="form-control comma-separated" name="harga_per_hari_show" @if($mode == "add") value="0" @else value="{{comma_separated($detail->item_price_per_day)}}" @endif required>
                                        <input type="hidden" name="details_keep[{{$unique_row}}][harga_per_hari]" id="harga_per_hari{{$unique_row}}" class="harga_per_hari" @if($mode == "add") value="0" @else value="{{$detail->item_price_per_day}}" @endif>
                                    </td>
                                    <td>
                                    <input type="text" class="form-control subtotal" disabled readonly @if($mode == "add") value="0" @else value="{{comma_separated($detail->item_price)}}" @endif>
                                    </td>
                                    <td></td>
                                    <td>
                                        <a @if($detail->returned_all()) disabled @endif class="btn btn-danger btn-sm" onclick="remove_bundle_row(this, 'detail-bundle-{{$detail->item_parent_id}}');"><i class="bi bi-trash"></i></a>
                                    </td>
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
                                        <select class="form-control select-searchable inventory_select" name="details_keep[{{$unique_row}}][details][{{$detail_numbering}}][product_item]" onchange="calculate_pricing(this);">
                                            <option value="">--Pilih Barang--</option>
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
                                            <option {{$selected}} value="{{$itema->item_id}}" data-pricing="{{$itema->item_harga_perhari}}">{{$itema->item_code}} - {{$owner_name}}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td colspan="3"></td>
                                    <td class="text-center">
                                        <input type="checkbox" disabled>
                                    </td>
                                    <td></td>
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
                                    <select class="form-control select-searchable inventory_select" name="details_keep[{{$unique_row}}][product_item]" onchange="calculate_pricing(this);">
                                        <option value="">--Pilih Barang--</option>
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
                                        <option {{$selected}} value="{{$itema->item_id}}" data-pricing="{{$itema->item_harga_perhari}}">{{$itema->item_code}} - {{$owner_name}}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="number" class="form-control days_rent" min="0" max="100" style="width:75px;" @if($mode == "add") value="0" @else value="{{$detail->item_days_rent}}" @endif name="details_keep[{{$unique_row}}][days_rent]">
                                </td>
                                <td>
                                    <input type="text" placeholder="Harga sewa perhari" data-target="harga_per_hari{{$unique_row}}" class="form-control comma-separated" name="harga_per_hari_show" @if($mode == "add") value="0" @else value="{{comma_separated($detail->item_price_per_day)}}" @endif required>
                                    <input type="hidden" name="details_keep[{{$unique_row}}][harga_per_hari]" id="harga_per_hari{{$unique_row}}" class="harga_per_hari" @if($mode == "add") value="0" @else value="{{$detail->item_price_per_day}}" @endif>
                                </td>
                                <td>
                                    <input type="text" class="form-control subtotal" disabled readonly @if($mode == "add") value="0" @else value="{{comma_separated($detail->item_price)}}" @endif>
                                </td>
                                <td class="text-center">
                                    <input type="checkbox" disabled>
                                </td>
                                <td>
                                    <a @if(($detail->item_return) == 1) disabled @endif class="btn btn-danger btn-sm" onclick="remove_row(this);"><i class="bi bi-trash"></i></a>
                                </td>
                            </tr>
                            @endif
                            @endforeach
                        @endif
                    </tbody>
                    <tfoot>
                        <tr>
                            <td class="text-end" colspan="6">Total</td>
                            <td>
                                <div class="text-end" id="total_detail"></div>
                            </td>
                            <td colspan="2"></td>
                        </tr>
                        <tr>
                            <td class="text-end" colspan="6">Diskon (%)</td>
                            <td>
                                <input type="number" name="diskon_persen" placeholder="Persen" @if($mode == 'add') value="0" @else value="{{$header->transaction_discount_percent}}" @endif min="0" max="100" class="form-control">
                            </td>
                            <td colspan="2"></td>
                        </tr>
                        <tr>
                            <td class="text-end" colspan="6">Diskon Lainnya</td>
                            <td>
                                <input type="text" placeholder="Harga Diskon (Potongan)" data-target="diskon_lain" class="form-control comma-separated" @if($mode == 'add') value="0" @else value="{{comma_separated($header->transaction_discount)}}" @endif>
                                <input type="hidden" id="diskon_lain" name="diskon_lain" class="form-control" @if($mode == 'add') value="0" @else value="{{($header->transaction_discount)}}" @endif>
                            </td>
                            <td colspan="2"></td>
                        </tr>
                        <tr>
                            <td class="text-end" colspan="6">PPn (%)</td>
                            <td>
                                <input type="number" value="10" class="form-control" disabled>
                                <input type="hidden" name="ppn" value="10">
                            </td>
                            <td colspan="2"></td>
                        </tr>
                        <tr>
                            <td class="text-end" colspan="6">Grand Total</td>
                            <td>
                                <input type="text" name="grand_total" id="grand_total" class="form-control" disabled @if($mode == 'add') value="0" @else value="{{comma_separated($header->transaction_amount)}}" @endif>
                            </td>
                            <td colspan="2"></td>
                        </tr>
                        @if($mode != 'add')
                        <tr>
                            <td class="text-end" colspan="9">
                                <label for="SubmitBtn" class="ms-3 btn btn-success"><i class="bi bi-save me-2"></i>Save</label>
                            </td>
                        </tr>
                        @endif
                    </tfoot>
                </table>
            </div>
        </div>
    </fieldset>
    <fieldset class="border p-2">
        @include('transaction.form.rent_payment', ["transaction_id" => $header->transaction_id])
    </fieldset>
    <fieldset class="border p-2">
        @include('transaction.form.serah_terima', ["transaction_id" => $header->transaction_id])
    </fieldset>
    <fieldset class="border p-2">
        @include('transaction.form.rent_dosa', ["transaction_id" => $header->transaction_id])
    </fieldset>
</form>
@endsection

@section('content_footer')
<div class="row mx-0">
    <div class="col"></div>
    <div class="col-auto">
        @if($mode == 'add')
        <label for="SubmitBtn" class="btn btn-success"><i class="bi bi-save me-2"></i>Save</label>
        @else
        <a href="{{route('transaction.rent.payment.add', ['transaction_id' => $header->transaction_id])}}" class="btn btn-success">Tambah Payment</a>
        <a href="{{route('transaction.rent.dosa.add', ['transaction_id' => $header->transaction_id])}}" class="btn btn-success">Tambah Dosa</a>
        <a href="{{route('transaction.rent.serah_terima.add', ['transaction_id' => $header->transaction_id])}}" class="btn btn-success">Buat Serah Terima</a>
        @endif
    </div>
</div>
@endsection

@section('js')
<script>
    $(document).ready(function(){
        recalculate();
        $("#detail-table").on('change', 'input', function(){
            recalculate();
        });

        $("#detail-table").on('change', '.inventory_select', function(){
            var others = $('.inventory_select').not(this);
            var current = $(this);

            $.each(others, function(a,b){
                if(current.val() != "" && $(b).val() == current.val()){
                    Swal.fire("Terjadi kesalahan", 'Item ini sudah dipilih', "error");
                    current.val("").trigger("change");
                    return false;
                }
            });
        });
    });

    function recalculate(){
        var rows = $("#detail-table tbody tr");
        var total = 0;
        $.each(rows, function(a,b){
            var row = $(b);
            var days = row.find(".days_rent").val();
            var price = row.find(".harga_per_hari").val();
            if(days == null){
                days = 0;
            }
            if(price == null){
                price = 0;
            }
            var sub_total = days*price;
            row.find(".subtotal").val(numberWithCommas(sub_total));
            total+=sub_total;
        });

        var diskon_percent = $("#detail-table input[name='diskon_persen']").val();
        var diskon_lain = $("#detail-table input[name='diskon_lain']").val();
        var ppn = $("#detail-table input[name='ppn']").val();

        var grand_total = (total - (total*diskon_percent/100)) - diskon_lain;
        grand_total += grand_total*ppn/100;
        $("#detail-table #total_detail").html(numberWithCommas(total));
        $("#detail-table #grand_total").val(numberWithCommas(grand_total));
    }

    function calculate_pricing(select){
        var select = $(select);
        var pricing = select.find("option:selected");
        var price = pricing.data("pricing");
        var row = select.closest('tr');
        row.find(".harga_per_hari").val(price).trigger('change');
        row.find(".harga_per_hari").prev().val(numberWithCommas(price)).trigger('change');
    }

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

    function add_row(){
        var select = $("#search-select");
        var product_id = select.val();
        var product_group = select.find(":selected").data('group');

        if(product_id == ""){
            Swal.fire({
                icon: "error",
                text: "Silahkan pilih barangnya!",
            });
            return;
        }

        showLoading();
        $.ajax({
            type    : 'POST',
            url     : '{{route("transaction.rent.item.get")}}',
            headers : { "X-CSRF-TOKEN": "{{ csrf_token() }}" },
            dataType: 'JSON',
            data    : {
                'product':product_id,
                'product_type':product_group
            },
            success : function(msg) {
                if(product_group == 'product'){
                    create_row_product(msg);
                }else{
                    create_row_bundle(msg);
                }
            },
            error     : function(xhr) {
                console.log(xhr);
                Swal.fire("Terjadi kesalahan", xhr.responseJSON.Message, "error");
            },
            complete : function(xhr,status){
                closeLoading();
                select.val("").trigger('change');
            }
        });
    }

    function create_row_product(data){
        var table = $("#detail-table tbody");
        var row = '';

        if(table.find('.no-data').length > 0){
            table.html('');
        }

        var options = "";
        $.each(data.items, function(a,b){
            if(b.item_owner_type == 1){
                var owner = b.owner.mitra_name;
            }else{
                var owner = b.owner.customer_name;
            }
            options += `
                <option value="`+b.item_id+`" data-pricing="`+b.item_harga_perhari+`">`+b.item_code+` - `+owner+`</option>
            `;
        });

        var unique_row = data.product_id+"_"+makeid(10)

        row = `
            <tr>
                <td>
                    <input type="hidden" name="details[`+unique_row+`][product_type]" value="product">
                    <input type="hidden" name="details[`+unique_row+`][product_id]" value="`+data.product_id+`">
                </td>
                <td>`+data.product_name+`</td>
                <td>`+data.brand.product_brand_name+`</td>
                <td>
                    <select class="form-control select-searchable inventory_select" name="details[`+unique_row+`][product_item]" onchange="calculate_pricing(this);">
                        <option value="">--Pilih Barang--</option>
                        `+options+`
                    </select>
                </td>
                <td>
                    <input type="number" class="form-control days_rent" min="0" max="100" style="width:75px;" value="0" name="details[`+unique_row+`][days_rent]">
                </td>
                <td>
                    <input type="text" placeholder="Harga sewa perhari" data-target="harga_per_hari`+unique_row+`" class="form-control comma-separated" name="harga_per_hari_show" required>
                    <input type="hidden" name="details[`+unique_row+`][harga_per_hari]" id="harga_per_hari`+unique_row+`" class="harga_per_hari">
                </td>
                <td>
                    <input type="text" class="form-control subtotal" disabled readonly value="0">
                </td>
                <td></td>
                <td>
                    <a class="btn btn-danger btn-sm" onclick="remove_row(this);"><i class="bi bi-trash"></i></a>
                </td>
            </tr>
        `;

        table.append(row);
        init_select();
    }

    function create_row_bundle(data){
        var table = $("#detail-table tbody");
        var row = '';

        if(table.find('.no-data').length > 0){
            table.html('');
        }

        var unique_row = data.bundle_id+"_"+makeid(10)

        var row = `
            <tr>
                <td>
                    <input type="hidden" name="details[`+unique_row+`][product_type]" value="bundle">
                    <input type="hidden" name="details[`+unique_row+`][bundle_id]" value="`+data.bundle_id+`">
                </td>
                <td colspan="3">`+data.bundle_name+`</td>
                <td>
                    <input type="number" class="form-control days_rent" min="0" max="100" style="width:75px;" value="0" name="details[`+unique_row+`][days_rent]">
                </td>
                <td>
                    <input type="text" placeholder="Harga sewa perhari" data-target="harga_per_hari_bundle`+unique_row+`" class="form-control comma-separated" name="harga_per_hari_bundle_show" required>
                    <input type="hidden" name="details[`+unique_row+`][harga_per_hari]" id="harga_per_hari_bundle`+unique_row+`" class="harga_per_hari">
                </td>
                <td>
                    <input type="text" class="form-control subtotal" disabled readonly value="0">
                </td>
                <td></td>
                <td>
                    <a class="btn btn-danger btn-sm" onclick="remove_bundle_row(this, 'detail-bundle-`+data.bundle_id+`');"><i class="bi bi-trash"></i></a>
                </td>
            </tr>
        `;

        $.each(data.products, function(x,y){
            var options = "";

            $.each(y.items, function(a,b){
                if(b.item_owner_type == 1){
                    var owner = b.owner.mitra_name;
                }else{
                    var owner = b.owner.customer_name;
                }
                options += `
                    <option value="`+b.item_id+`" data-pricing="`+b.item_harga_perhari+`">`+b.item_code+` - `+owner+`</option>
                `;
            });

            var unique_row_detail = y.product.product_id+"_"+makeid(10)

            row += `
                <tr class="detail-bundle detail-bundle-`+data.bundle_id+`">
                    <td>
                        <i class="bi bi-arrow-return-right icon"></i>
                        <input type="hidden" name="details[`+unique_row+`][details][`+unique_row_detail+`][product_type]" value="product">
                        <input type="hidden" name="details[`+unique_row+`][details][`+unique_row_detail+`][product_id]" value="`+y.product.product_id+`">
                    </td>
                    <td>`+y.product.product_name+`</td>
                    <td>`+y.product.brand.product_brand_name+`</td>
                    <td>
                        <select class="form-control select-searchable inventory_select" name="details[`+unique_row+`][details][`+unique_row_detail+`][product_item]" onchange="calculate_pricing(this);">
                            <option value="">--Pilih Barang--</option>
                            `+options+`
                        </select>
                    </td>
                    <td colspan="5"></td>
                </tr>
            `;
        });

        table.append(row);
        init_select();
    }

    function remove_bundle_row(btn, cls_cls){
        var row_remove = $(btn).closest('tr');
        var table = $(btn).closest('tbody');

        row_remove.remove();
        console.log(cls_cls);
        $("."+cls_cls).remove();

        if(table.find("tr").length < 1){
            var row = `
                <tr class="no-data">
                    <td colspan="8" class="text-center">
                        Belum Ada Data
                    </td>
                </tr>
            `;

            table.html(row);
        }

        recalculate();
    }

    function remove_row(btn){
        var row_remove = $(btn).closest('tr');
        var table = $(btn).closest('tbody');

        row_remove.remove();
        if(table.find("tr").length < 1){
            var row = `
                <tr class="no-data">
                    <td colspan="8" class="text-center">
                        Belum Ada Data
                    </td>
                </tr>
            `;

            table.html(row);
        }

        recalculate();
    }
</script>
@endsection

@section('footer')

@endsection