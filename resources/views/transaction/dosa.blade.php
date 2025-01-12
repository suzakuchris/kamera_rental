@extends('layouts.app')

@section('content_header')
<h4><a href="{{route('transaction.rent.view', ['transaction_id' => $transaction_id])}}" class="me-2"><i class="bi bi-chevron-left"></i></a>- History Dosa</h4>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="row mx-0 mb-4">
            <div class="col"></div>
            <div class="col-auto pe-0">
                <div class="row mx-0">
                    <div class="col-12 col-md-auto">
                        <div class="input-group">
                            <input id="text-search" type="text" class="form-control" placeholder="Search..">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12">
        <table class="table table-bordered" id="data-table" data-show-toggle="true">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Tanggal & Jam</th>
                    <th>Keterangan</th>
                    <th>Action</th>
                    <th data-breakpoints="all" data-title="List Alat Bermasalah:"></th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>
@endsection

@section('content_footer')
@include('components.common.paginator')
@endsection

@section('js')
<script>
    var max_row = 0;
    var curr_page = 1;
    var detail_tables = [];
    $(document).ready(function(){
        search_process();
        $("form").on('submit', function(event){
            event.preventDefault();
        });

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
            url     : '{{route("transaction.rent.dosa.view.search", ["transaction_id" => $transaction_id])}}',
            headers : { "X-CSRF-TOKEN": "{{ csrf_token() }}" },
            dataType: 'JSON',
            data    : {
                'page':curr_page,
                'transaction_id':"{{$transaction_id}}",
                'max_row':max_row,
                'search':search
            },
            success : function(msg) {
                console.log(msg);
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
            var details = y.details;
            var table_dosa = "";
            $.each(details, function(c,d){
                var btn_lampiran = "";
                if(d.image_path){
                    btn_lampiran = `<button type="button" class="btn btn-sm btn-primary" onclick="show_lampiran('`+d.image_path+d.image_name+`')">Lampiran</button>`
                }
                table_dosa += `
                    <tr>
                        <td>`+(c+1)+`.</td>
                        <td>`+d.product_name+`</td>
                        <td>`+d.product_brand_name+`</td>
                        <td>`+d.item_code+` - `+d.owner_name+`</td>
                        <td>`+d.condition_name+`</td>
                        <td>`+d.dosa_reason+`</td>
                        <td>
                            `+btn_lampiran+`
                        </td>
                    </tr>
                `;
            });
            var detail_table = `
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Nama Barang</th>
                            <th>Brand</th>
                            <th>Inventory</th>
                            <th>Dosa</th>
                            <th>Keterangan</th>
                            <th>Lampiran</th>
                        </tr>
                    </thead>
                    <tbody>
                        `+table_dosa+`
                    </tbody>
                </table>
            `;

            var tanggal_jam = "";
            tanggal_jam = moment(y.header_datetime).format("DD MMM YYYY hh:mm");

            rows += `
                <tr data-rowid="`+y.header_id+`">
                    <td>`+(++page)+`.</td>
                    <td>`+tanggal_jam+`</td>
                    <td>`+y.header_notes+`</td>
                    <td>
                        <div class="btn-group">
                            <a href="{{route('transaction.rent.dosa.view.detail', ['transaction_id' => $transaction_id])}}/`+y.header_id+`" class="btn btn-primary btn-sm"><i class="me-2 bi bi-pencil"></i>View</a>
                            <button class="btn btn-danger btn-sm" onclick="delete_data(`+y.header_id+`)"><i class="me-2 bi bi-pencil"></i>Delete</button>
                        </div>
                    </td>
                    <td>
                        <div id="detail_table_wrapper_`+y.header_id+`"></div>
                    </td>
                </tr>
            `;

            detail_tables[y.header_id] = detail_table;
        });

        if(rows == ''){
            var length = $("#data-table thead th").length;
            rows = `
                <tr class="no-data">
                    <td colspan="`+length+`">Data kosong</td>
                </tr>
            `
        }

        $("#data-table tbody").html(rows);
        // $('#data-table').footable();

        $('#data-table').footable().bind({
            'collapse.ft.row' : function(e, ft, row) {
                //Your code when a row is collapsed
            },

            'expand.ft.row' : function(e, ft, row) {
                var rowid = $(row.$el).data('rowid')
                $("#detail_table_wrapper_"+rowid).html(detail_tables[rowid]);
                //Your code when a row is expanded                  
            },
        });
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
                    url     : '{{route("transaction.rent.dosa.view.delete", ["transaction_id" => $transaction_id])}}',
                    headers : { "X-CSRF-TOKEN": "{{ csrf_token() }}" },
                    dataType: 'JSON',
                    data    : {
                        'header_id':id,
                        'transaction_id':"{{$transaction_id}}"
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

    function show_lampiran(url){
        $("#lampiran_dosa_image").attr("src", "{{asset('/')}}"+url);
        $("#dosa_lampiran_modal").modal('show');
    }
</script>
@endsection

@section('footer')
<div class="modal fade" id="dosa_lampiran_modal" tabindex="-1" aria-labelledby="dosa_lampiran_modal_label" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="dosa_lampiran_modal_label">Lampiran Dosa</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <img id="lampiran_dosa_image" class="w-100" src="">
            </div>
        </div>
    </div>
</div>
@endsection