@extends('layouts.app')

@section('content_header')
<a href="{{route('transaction.stock_opname')}}"><i class="bi bi-chevron-left me-2"></i></a>
@if($mode == 'add')
Stock Opname
@elseif($mode == 'view')
History Stock Opname
@endif
@endsection

@section('content')
<form method="POST" action="{{route('transaction.stock_opname.upsert')}}" onsubmit="pre_submit(event, this);">
    {{csrf_field()}}
    <input type="hidden" name="start_time" value="{{$start}}">
    <fieldset class="border p-2">
        <div class="row">
            <div class="col-12 mb-3">
                <table class="table">
                    <tbody>
                        <tr>
                            <td class="auto-width"><b>Waktu Inisiasi</b></td>
                            <td>{{$start->format('d F Y, H:m:s')}}</td>
                        </tr>
                        <tr>
                            <td><b>Inisiator</b></td>
                            <td>{{Auth::user()->name}}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="col-12">
                <table class="table table-bordered">
                    <thead>
                        <th>No.</th>
                        <td>Nama Barang</td>
                        <td>Item Code</td>
                        <td>Real Availability</td>
                        <td>Condition</td>
                        <td>Catatan</td>
                        <td></td>
                    </thead>
                    <tbody>
                        @foreach($items as $k=>$item)
                            <tr>
                                <td>
                                    {{$k+1}}.
                                    <input type="hidden" value="{{$item->item_id}}">
                                </td>
                                <td><b>{{$item->product->brand->product_brand_name}}</b> {{$item->product->product_name}}</td>
                                <td class="text-center">{{$item->item_code}}</td>
                                <td>
                                    <select class="form-control" name="items[{{$item->item_id}}][item_status]" required>
                                        @foreach($status as $stat)
                                        <option value="{{$stat->status_id}}" @if($stat->status_id == 2) selected @endif>{{$stat->status_name}}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <select class="form-control" name="items[{{$item->item_id}}][item_condition]" required>
                                        <option value="">-</option>
                                        @foreach($condition as $cond)
                                        <option value="{{$cond->condition_id}}">{{$cond->condition_name}}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <textarea class="form-control" rows="4" name="items[{{$item->item_id}}][item_notes]"></textarea>
                                </td>
                                <td class="text-center">
                                    <label type="button" class="btn btn-danger btn-sm"><div class="d-flex align-items-center"><input type="checkbox" class="mb-0 me-2 dosa-toggler" name="items[{{$item->item_id}}][item_dosa]"  value="{{$item->item_id}}">Buat Entri Dosa</div></label>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <button id="SubmitBtn" class="d-none" type="submit"></button>
    </fieldset>
</form>
@endsection

@section('content_footer')
<div class="row mx-0">
    <div class="col"></div>
    <div class="col-auto">
        @if($mode == 'add')
        <label for="SubmitBtn" class="btn btn-success"><i class="bi bi-save me-2"></i>Save</label>
        @endif
    </div>
</div>
@endsection

@section('js')
<script>
    File.prototype.convertToBase64 = function(callback){
        var reader = new FileReader();
        reader.onloadend = function (e) {
            callback(e.target.result, e.target.error);
        };   
        reader.readAsDataURL(this);
    };
    $(document).on('change', '.imageinput', function(){
        var selectedFile = this.files[0];
        var input = $(this);
        selectedFile.convertToBase64(function(base64){
            console.log(base64);
            var next = input.next().val(base64);
        });
    });
</script>
<script>
    $(document).on('change', '.dosa-toggler', function(){
        showLoading();
        var cb = $(this);
        var value = cb.val();
        var row = cb.closest('tr');

        if(cb.is(":checked")){
            var new_row = `
                <tr>
                    <td></td>
                    <td colspan="6">
                        <div class="row my-3 d-flex justify-content-center">
                            <div class="col-8">
                                <div class="form-group">
                                    <label>Alasan:</label>
                                    <textarea class="form-control" rows="5" name="items[`+value+`][item_dosa_reason]" required></textarea>
                                </div>
                                <div>
                                    <input accept="image/*" type="file" class="form-control imageinput" required>
                                    <textarea class="d-none base64input" name="items[`+value+`][item_dosa_attachment]"></textarea>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
            `;

            var row_obj = $(new_row);
            row_obj.insertAfter(row);
            closeLoading();
        }else{
            row.next().remove();
            closeLoading();
        }
    });

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
@endsection