@extends('layouts.app')

@section('content_header')
<h4><a href="{{route('transaction.rent.view', ['transaction_id' => $transaction_id])}}" class="me-2"><i class="bi bi-chevron-left"></i></a>- History Payment</h4>
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
        <table class="table table-bordered" id="data-table">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Nominal</th>
                    <th>Keterangan</th>
                    <th>Out / In</th>
                    <th>Tanggal Payment</th>
                    <th>Lampiran</th>
                    <th>Action</th>
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
            url     : '{{route("transaction.rent.payment.view.search", ["transaction_id" => $transaction_id])}}',
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
            var amount = y.entry_debit;
            var tipe_flow = "Out";
            if(y.entry_cashflow == 1){
                amount = y.entry_credit;
                tipe_flow = 'In';
            }

            rows += `
                <tr>
                    <td>`+(++page)+`.</td>
                    <td>`+numberWithCommas(amount)+`</td>
                    <td>`+y.entry_notes+`</td>
                    <td>`+tipe_flow+`</td>
                    <td>`+moment(y.entry_date).format('DD MMM YYYY')+`</td>
                    <td>
                        <button type="button" class="btn btn-primary btn-sm" onclick="show_lampiran_payment(`+y.entry_id+`)">Lampiran</button>
                    </td>
                    <td>
                        <div class="btn-group">
                            <a href="{{route('transaction.rent.payment.view.detail', ['transaction_id' => $transaction_id])}}/`+y.entry_id+`" class="btn btn-primary btn-sm"><i class="me-2 bi bi-pencil"></i>View</a>
                            <button class="btn btn-danger btn-sm" onclick="delete_data(`+y.entry_id+`)"><i class="me-2 bi bi-pencil"></i>Delete</button>
                        </div>
                    </td>
                </tr>
            `;
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
                    url     : '{{route("transaction.rent.payment.view.delete", ["transaction_id" => $transaction_id])}}',
                    headers : { "X-CSRF-TOKEN": "{{ csrf_token() }}" },
                    dataType: 'JSON',
                    data    : {
                        'entry_id':id,
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

    function show_lampiran_payment(payment_id){
        showLoading();
        $.ajax({
            type    : 'POST',
            url     : '{{route("transaction.rent.payment.attachment")}}',
            headers : { "X-CSRF-TOKEN": "{{ csrf_token() }}" },
            dataType: 'JSON',
            data    : {
                'payment_id':payment_id
            },
            success : function(msg) {
                var images_data = msg;
                var images = '';
                $.each(images_data, function(a,b){
                    var active = '';
                    if(a==0){
                        active = 'active';
                    }
                    images += `
                        <div class="carousel-item `+active+`">
                            <img src="`+b+`" class="d-block w-100">
                        </div>
                    `;
                });

                $("#rent_payment_container").html(images);
                $("#rent_payment_modal").modal('show');
                $('#rent_payment_modal .carousel').carousel({
                    interval: 0
                });
            },
            error     : function(xhr) {
                console.log(xhr);
            },
            complete : function(xhr,status){
                closeLoading();
            }
        });
    }
</script>
@endsection

@section('footer')
<div class="modal fade" id="rent_payment_modal" tabindex="-1" aria-labelledby="rent_payment_modal_label" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="rent_payment_modal_label">Lampiran Payment</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="rent_payment_container_wrapper" class="carousel slide">
                    <div class="carousel-inner" id="rent_payment_container">
                        
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#rent_payment_container_wrapper" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#rent_payment_container_wrapper" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Next</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection