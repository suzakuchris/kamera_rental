@extends('layouts.app')

@section('content_header')
<h4>Stock Opname</h4>
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
                            <span class="input-group-text"><i class="bi bi-calendar me-2"></i>Tanggal Opname</span>
                            <input id="date-search" type="date" class="form-control" placeholder="Tanggal..">
                        </div>
                    </div>
                    <div class="col-12 col-md-auto">
                        <div class="input-group">
                            <input id="text-search" type="text" class="form-control" placeholder="Search..">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                        </div>
                    </div>
                    <div class="col-12 col-md-auto">
                        <a href="{{route('transaction.stock_opname.form.add')}}" class="btn btn-primary">Start Opname</a>
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
                    <th>Waktu Inisiasi</th>
                    <th>Waktu Selesai</th>
                    <th>Petugas</th>
                    <th>Total Item</th>
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

        $("#row_count, #text-search, #date-search").change(function(){
            search_process();
        });
    });

    function search_process(){
        var search = $("#text-search").val();
        var date = $("#date-search").val();
        var max_row = $("#row_count").val();
        showLoading();
        $.ajax({
            type    : 'POST',
            url     : '{{route("transaction.stock_opname.search")}}',
            headers : { "X-CSRF-TOKEN": "{{ csrf_token() }}" },
            dataType: 'JSON',
            data    : {
                'page':curr_page,
                'max_row':max_row,
                'search':search,
                'date':date,
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
            var start_time = moment(y.opname_start_date).format('DD MMM YYYY hh:mm:ss');
            var end_time = moment(y.opname_end_date).format('DD MMM YYYY hh:mm:ss');
            rows += `
                <tr>
                    <td>`+(++page)+`.</td>
                    <td>`+start_time+`</td>
                    <td>`+end_time+`</td>
                    <td>`+y.nama+`</td>
                    <td>`+y.total_barang+`</td>
                    <td>
                        <div class="btn-group">
                            <a href="{{route('transaction.stock_opname.form.view')}}/`+y.id+`" class="btn btn-outline-primary d-flex align-items-center"><i class="bi bi-eye me-2"></i>View</a>
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
</script>
@endsection

@section('footer')

@endsection