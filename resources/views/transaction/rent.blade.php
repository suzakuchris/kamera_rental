@extends('layouts.app')

@section('content_header')
<h4>Transaksi</h4>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="row mx-0 mb-4">
            <div class="col"></div>
            <div class="col-auto pe-0">
                <div class="row mx-0">
                    <div class="col-12 col-md-auto">
                        <select id="bank-search" class="form-control">
                            <option value="">Pilih Bank</option>
                            @foreach(bank_lists() as $bank)
                            <option value="{{$bank}}">{{$bank}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-auto">
                        <div class="input-group">
                            <input id="text-search" type="text" class="form-control" placeholder="Search..">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                        </div>
                    </div>
                    <div class="col-12 col-md-auto">
                        <a href="{{route('transaction.rent.add')}}" class="btn btn-primary">Add New</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12">
        <table class="table table-bordered" id="data-table">
            <thead>
                <tr>
                    <th class="auto-width">No.</th>
                    <th>Kode Transaksi</th>
                    <th>Nama Nasabah</th>
                    <th>Nomor Rekening</th>
                    <th>Mulai Sewa</th>
                    <th>Selesai Sewa</th>
                    <th>Harga</th>
                    <th>Created</th>
                    <th>Updated</th>
                    <th class="auto-width">Action</th>
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
    $(document).ready(function(){
        search_process();
        $("form").on('submit', function(event){
            event.preventDefault();
        });

        $("#row_count, #text-search, #bank-search").change(function(){
            search_process();
        });
    });

    function search_process(){
        var search = $("#text-search").val();
        var bank = $("#bank-search").val();
        var max_row = $("#row_count").val();
        showLoading();
        $.ajax({
            type    : 'POST',
            url     : '{{route("transaction.rent.search")}}',
            headers : { "X-CSRF-TOKEN": "{{ csrf_token() }}" },
            dataType: 'JSON',
            data    : {
                'page':curr_page,
                'max_row':max_row,
                'search':search,
                'bank':bank
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
            var tgl_ambil = moment(y.transaction_tgl_ambil).format('DD MMM YYYY hh:mm:ss');
            var tgl_return = moment(y.transaction_tgl_pemulangan).format('DD MMM YYYY hh:mm:ss');
            var created = moment(y.created_at).format('DD MMM YYYY hh:mm:ss');
            var updated = '-';
            if(y.updated_at){
                updated = moment(y.updated_at).format('DD MMM YYYY hh:mm:ss');
            }
            rows += `
                <tr>
                    <td>`+(++page)+`.</td>
                    <td>
                        <div>`+y.transaction_number+`</div>
                        <div class="small text-muted">`+y.transaction_notes+`</div>
                    </td>
                    <td>
                        <div>`+y.customer_name+`</div>
                        <div class="small text-muted">`+y.customer_phone+` - `+y.customer_email+`</div>
                    </td>
                    <td>`+tgl_ambil+`</td>
                    <td>`+tgl_return+`</td>
                    <td>`+numberWithCommas(y.transaction_amount)+`</td>
                    <td>`+y.rekening_atas_nama+` - `+y.rekening_number+` (`+y.rekening_nama_bank+`)</td>
                    <td>`+created+`</td>
                    <td>`+updated+`</td>
                    <td>
                        <div class="btn-group">
                            <a href="{{route('transaction.rent.view')}}/`+y.transaction_id+`" class="btn btn-outline-primary d-flex align-items-center"><i class="bi bi-pencil me-2"></i>Edit</a>
                            <button class="btn btn-outline-danger d-flex align-items-center" onclick="delete_data(`+y.transaction_id+`)"><i class="bi bi-trash me-2"></i>Delete</button>
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
                    url     : '{{route("transaction.rent.delete")}}',
                    headers : { "X-CSRF-TOKEN": "{{ csrf_token() }}" },
                    dataType: 'JSON',
                    data    : {
                        'transaction_id':id,
                    },
                    success : function(msg) {
                        Swal.fire("Saved!", "", "success");
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