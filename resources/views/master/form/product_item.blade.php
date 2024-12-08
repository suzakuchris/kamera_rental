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
Produk - Item Inventory
@endsection

@section('content')
<div>
    <fieldset class="border p-2">
        <legend class="w-auto">Data Produk</legend>
        <div class="row mx-0">
            <div class="col-12">
                <div class="row mx-0">
                    <div class="col-12">
                        <div class="form-group">
                            <label>Nama Produk</label>
                            <input type="text" class="form-control" readonly value="{{$product->product_name}}">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label>Tipe Produk</label>
                            <input type="text" class="form-control" readonly value="{{$product->type->product_type_name}}">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label>Brand Produk</label>
                            <input type="text" class="form-control" readonly value="{{$product->brand->product_brand_name}}">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </fieldset>
    <fieldset class="border p-2">
        <legend class="w-auto">Inventory</legend>
        <div class="row mx-0">
            <div class="col-12">
                <div class="row mx-0 mb-3">
                    <div class="col"></div>
                    <div class="col-auto">
                        <div class="input-group">
                            <input id="text-search" type="text" class="form-control" placeholder="Search..">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                        </div>
                    </div>
                    <div class="col-auto pe-0">
                        <a href="{{route('master.item.add', ['product_id' => $product->product_id])}}" target="FORM_PRODUCT" class="btn btn-primary">Add New</a>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <table class="table table-bordered" id="data-table">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Serial Number</th>
                            <th>Catatan</th>
                            <th>Owner</th>
                            <th>Kondisi</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Updated</th>
                            <th class="auto-width">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
        @include('components.common.paginator')
    </fieldset>
</div>
@endsection

@section('content_footer')

@endsection

@section('js')
<script>
    var max_row = 0;
    var curr_page = 1;
    $(document).ready(function(){
        search_process();
        $("#row_count, #text-search").change(function(){
            search_process();
        });
    });

    function search_process(){
        var search = $("#text-search").val();
        var max_row = $("#row_count").val();
        showLoading();
        $.ajax({
            type    : 'POST',
            url     : '{{route("master.item.product.form.search")}}',
            headers : { "X-CSRF-TOKEN": "{{ csrf_token() }}" },
            dataType: 'JSON',
            data    : {
                'page':curr_page,
                'max_row':max_row,
                'search':search
            },
            success : function(msg) {
                var rs = msg.data;

                show_data(rs["data"]);
                $(".pagination-links").html($(msg.pagination));
            },
            error     : function(xhr) {
                console.log(xhr);
            },
            complete : function(xhr,status){
                closeLoading();
            }
        });
    }

    function show_data(data){
        var rows = '';
        var _curr_page = curr_page;
        var page = (_curr_page * max_row) - max_row;
        $.each(data, function(x,y){
            var created = moment(y.created_at).format('DD MMM YYYY hh:mm:ss');
            var updated = '-';
            if(y.updated_at){
                updated = moment(y.updated_at).format('DD MMM YYYY hh:mm:ss');
            }

            var owner = '-';
            if(y.item_owner_type == 1){
                //mitra
                owner = y.mitra_name;
            }else{
                //customer
                owner = y.customer_name;
            }

            rows += `
                <tr>
                    <td>`+(++page)+`.</td>
                    <td>`+y.item_code+`</td>
                    <td>`+y.item_notes+`</td>
                    <td>`+owner+`</td>
                    <td>`+y.condition_name+`</td>
                    <td>`+y.status_name+`</td>
                    <td>`+created+`</td>
                    <td>`+updated+`</td>
                    <td>
                        <div class="btn-group">
                            <a class="btn btn-outline-primary d-flex align-items-center" href="{{route('master.item.edit')}}/`+y.item_id+`"><i class="bi bi-pencil me-2"></i>Edit</a>
                            <button class="btn btn-outline-danger d-flex align-items-center" onclick="delete_data(`+y.item_id+`)"><i class="bi bi-trash me-2"></i>Delete</button>
                        </div>
                    </td>
                </tr>
            `;
        });

        if(rows == ''){
            var length = $("#data-table thead th").length;
            rows = `
                <tr>
                    <td colspan="`+length+`">Data kosong</td>
                </tr>
            `
        }

        $("#data-table tbody").html(rows);
    }

    function delete_data(id){
        Swal.fire({
            title: "Apakah anda yakin mau menghapus data?",
            showDenyButton: true,
            showCancelButton: true,
            confirmButtonText: "Yes",
            denyButtonText: `No`
        }).then((result) => {
            /* Read more about isConfirmed, isDenied below */
            if (result.isConfirmed) {
                showLoading();
                $.ajax({
                    type    : 'POST',
                    url     : '{{route("master.item.delete")}}',
                    headers : { "X-CSRF-TOKEN": "{{ csrf_token() }}" },
                    dataType: 'JSON',
                    data    : {
                        'item_id':id,
                    },
                    success : function(msg) {
                        Swal.fire("Saved!", "", "success");
                        search_process();
                    },
                    error     : function(xhr) {
                        console.log(xhr);
                    },
                    complete : function(xhr,status){
                        closeLoading();
                    }
                });
            } else if (result.isDenied) {
                // Swal.fire("Changes are not saved", "", "info");
            }
        });
    }
</script>
@endsection

@section('footer')

@endsection