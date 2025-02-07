@extends('layouts.app')

@section('content_header')
<h4>Master User</h4>
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
                        <button class="btn btn-primary" onclick="add_new();">Add New</button>
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
                    <th>Nama</th>
                    <th>Email</th>
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
            url     : '{{route("master.users.search")}}',
            headers : { "X-CSRF-TOKEN": "{{ csrf_token() }}" },
            dataType: 'JSON',
            data    : {
                'page':curr_page,
                'max_row':max_row,
                'search':search
            },
            success : function(msg) {
                console.log(msg);
                var rs = msg.data;
                // var dt = rs["data"];

                show_data(rs["data"]);
                
                // $('.pagination-wrapper .from-data').html(rs.from);
                // $('.pagination-wrapper .to-data').html(rs.to);
                // $('.pagination-wrapper .total-data').html(rs.total);   
                // $('.data-box .card-footer .pagination-box').html($(msg.pagination));

                // $(".pagination-wrapper").html();
                // $(".pagination-wrapper").html();
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
            var disabled = '';
            if(y.id == 1){
                disabled = 'disabled';
            }
            rows += `
                <tr>
                    <td>`+(++page)+`.</td>
                    <td>`+y.name+`</td>
                    <td>`+y.email+`</td>
                    <td>`+created+`</td>
                    <td>`+updated+`</td>
                    <td>
                        <div class="btn-group">
                            <button `+disabled+` class="btn btn-outline-primary d-flex align-items-center" onclick="edit_data(`+y.id+`)"><i class="bi bi-pencil me-2"></i>Edit</button>
                            <button `+disabled+` class="btn btn-outline-danger d-flex align-items-center" onclick="delete_data(`+y.id+`)"><i class="bi bi-trash me-2"></i>Delete</button>
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

    function add_new(){
        var form = $("#form_modal form");
        form.trigger('reset');

        $("#form_modal").modal('show');
    }

    function edit_data(id){
        showLoading();
        $.ajax({
            type    : 'POST',
            url     : '{{route("master.users.view")}}',
            headers : { "X-CSRF-TOKEN": "{{ csrf_token() }}" },
            dataType: 'JSON',
            data    : {
                'user_id':id,
            },
            success : function(msg) {
                console.log(msg);
                fill_form(msg);
                $("#form_modal").modal('show');
            },
            error     : function(xhr) {
                console.log(xhr);
            },
            complete : function(xhr,status){
                closeLoading();
            }
        })
    }

    function fill_form(data){
        var form = $("#form_modal form");
        form.trigger('reset');
        form.find("[name='user_id']").val(data.id);
        form.find("[name='name']").val(data.name)
        form.find("[name='email']").val(data.email)
        form.find("[name='role']").val(data.role)
    }

    function save_form(form){
        showLoading();
        var dtForm = $(form).serializeArray();
        $.ajax({
            type        : 'POST',
            url         : '{{route("master.users.upsert")}}',
            headers     : { "X-CSRF-TOKEN": "{{ csrf_token() }}" },
            dataType    : 'JSON',
            data        : dtForm,
            success     : function(msg) {
                $("#form_modal form").trigger("reset");
                $('#form_modal').modal('hide');
            },
            error       : function(xhr) {
                console.log(xhr);
            },
            complete    : function(xhr){
                closeLoading();
                search_process();
            }
        });
    }

    function toggle_password(btn){
        var button = $(btn);
        var input = button.prev();
        button.find("i").toggle('fa-eye');
        button.find("i").toggle('fa-eye-slash');

        var current = input.attr('type');
        if(current == "password"){
            input.attr('type', 'text');
        }else{
            input.attr('type', 'password');
        }
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
                    url     : '{{route("master.users.delete")}}',
                    headers : { "X-CSRF-TOKEN": "{{ csrf_token() }}" },
                    dataType: 'JSON',
                    data    : {
                        'user_id':id,
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
<div class="modal fade" id="form_modal" tabindex="-1" aria-labelledby="formModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="formModalLabel">Data User</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form onsubmit="save_form(this);">
                    <input type="text" class="d-none" name="user_id">
                    <div class="row">
                        <div class="col-12 mb-3">
                            <div class="form-group">
                                <label>Nama</label>
                                <input type="text" class="form-control" name="name" required placeholder="Masukan nama">
                            </div>
                        </div>
                        <div class="col-12 mb-3">
                            <div class="form-group">
                                <label>E-mail</label>
                                <input type="email" class="form-control" name="email" required placeholder="Masukan email">
                            </div>
                        </div>
                        <div class="col-12 mb-3">
                            <div class="form-group">
                                <label>Password</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" name="password" required placeholder="Masukan password">
                                    <button type="button" class="btn btn-outline-secondary" onclick="toggle_password(this);"><i class="bi bi-eye"></i></button>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 mb-3">
                            <div class="form-group">
                                <label>Role</label>
                                <select class="form-control" name="role" required>
                                    <option value="" selected disabled>--Pilih Role--</option>
                                    @foreach($roles as $k=>$role)
                                    <option value="{{$k}}">{{$role}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="d-none" id="SubmitBtn"></button>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <label for="SubmitBtn" class="btn btn-primary">Save</label>
            </div>
        </div>
    </div>
</div>
@endsection