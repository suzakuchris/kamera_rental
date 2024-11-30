@extends('layouts.app')

@section('content_header')
<h4>Master Mitra</h4>
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
                    <div class="col-12 col-md-auto">
                        <a href="{{route('master.mitra.add')}}" target="FORM_PRODUCT" class="btn btn-primary">Add New</a>
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
                    <th>Nama Mitra</th>
                    <th>Perusahaan Mitra</th>
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
<div class="row pagination-wrapper">
    <div class="col-auto">
        <select id="row_count" class="form-control mb-2">
            <option value="10">10 Rows</option>
            <option value="25">25 Rows</option>
            <option value="50">50 Rows</option>
            <option value="100">100 Rows</option>
        </select>
    </div>
    <div class="col d-flex align-items-center d-none">
        <div class="small"><span class="from-data"></span>-<span class="to-data"></span> out of <span class="total-data"></span> data</div>
    </div>
    <div class="col">
        <div class="pagination-links"></div>
    </div>
</div>
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

        $(document).on('click', "a.page-link", function(event){
            event.preventDefault();
            var url_string = $(this).attr('href');
            var url = new URL(url_string);
            var page = url.searchParams.get("page");
            curr_page = page;
            search_process();
        });
    });

    function search_process(){
        var search = $("#text-search").val();
        var max_row = $("#row_count").val();
        showLoading();
        $.ajax({
            type    : 'POST',
            url     : '{{route("master.mitra.search")}}',
            headers : { "X-CSRF-TOKEN": "{{ csrf_token() }}" },
            dataType: 'JSON',
            data    : {
                'page':curr_page,
                'max_row':max_row
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
            var created = moment(y.created_at).format('DD MMM YYYY hh:mm:ss');
            var updated = '-';
            if(y.updated_at){
                updated = moment(y.updated_at).format('DD MMM YYYY hh:mm:ss');
            }
            rows += `
                <tr>
                    <td>`+(++page)+`.</td>
                    <td>`+y.mitra_name+`</td>
                    <td>`+y.mitra_company+`</td>
                    <td>`+created+`</td>
                    <td>`+updated+`</td>
                    <td>
                        <div class="btn-group">
                            <a class="btn btn-outline-primary d-flex align-items-center" href="{{route('master.mitra.edit')}}/`+y.mitra_id+`"><i class="bi bi-pencil me-2"></i>Edit</a>
                            <button class="btn btn-outline-danger d-flex align-items-center" onclick="delete_data(`+y.mitra_id+`)"><i class="bi bi-trash me-2"></i>Delete</button>
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
                    url     : '{{route("master.mitra.delete")}}',
                    headers : { "X-CSRF-TOKEN": "{{ csrf_token() }}" },
                    dataType: 'JSON',
                    data    : {
                        'mitra_id':id,
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