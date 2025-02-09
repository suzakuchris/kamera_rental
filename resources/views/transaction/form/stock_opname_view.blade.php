@extends('layouts.app')

@section('content_header')
<a href="{{route('transaction.stock_opname')}}"><i class="bi bi-chevron-left me-2"></i></a>History Stock Opname
@endsection

@section('content')
<form>
    <fieldset class="border p-2" >
        <div class="row">
            <div class="col-12 mb-3">
                <table class="table">
                    <tbody>
                        <tr>
                            <td class="auto-width"><b>Waktu Inisiasi</b></td>
                            <td>{{Carbon\Carbon::parse($header->opname_start_date)->format('d F Y, H:m:s')}}</td>
                        </tr>
                        <tr>
                            <td class="auto-width"><b>Waktu Selesai</b></td>
                            <td>{{Carbon\Carbon::parse($header->opname_end_date)->format('d F Y, H:m:s')}}</td>
                        </tr>
                        <tr>
                            <td><b>Inisiator</b></td>
                            <td>{{$header->user->name}}</td>
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
                    </thead>
                    <tbody>
                        @foreach($header->details as $k=>$detail)
                            <tr>
                                <td>
                                    {{$k+1}}.
                                </td>
                                <td><b>{{$detail->item->product->brand->product_brand_name}}</b> {{$detail->item->product->product_name}}</td>
                                <td class="text-center">{{$detail->item->item_code}}</td>
                                <td>
                                    {{$detail->status->status_name}}
                                </td>
                                <td>
                                    {{$detail->condition->condition_name}}
                                </td>
                                <td>
                                    {{$detail->opname_item_comment}}
                                </td>
                            </tr>
                            @if(isset($detail->dosa))
                            <tr>
                                <td></td>
                                <td colspan="6">
                                    <table class="table">
                                        <tr>
                                            <td class="auto-width">Alasan Dosa:</td>
                                            <td>{{$detail->dosa->dosa_reason}}</td>
                                        </tr>
                                        @php 
                                            $image = $detail->dosa->images[0];
                                        @endphp
                                        <tr>
                                            <td>Lampiran:</td>
                                            <td><button type="button" class="btn btn-sm btn-primary" onclick="show_lampiran('{{$image->image_path.$image->image_name}}')">Lampiran</button></td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <button id="SubmitBtn" class="d-none" type="submit"></button>
    </fieldset>
</form>
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

@section('content_footer')

@endsection

@section('js')
<script>
    function show_lampiran(url){
        $("#lampiran_dosa_image").attr("src", "{{asset('/')}}"+url);
        $("#dosa_lampiran_modal").modal('show');
    }
</script>
@endsection