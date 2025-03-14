@extends('layouts.app', ['no_sidebar' => true, 'allow_scroll' => true])

@section('css')
<style>
    @media print {
        /* visible when printed */
        .hide-for-print {
            display: none;
        }

        body {
            margin: 0;
            color: #000;
            background-color: #fff;
        }

        @page { margin: 0; }
        body { margin: 0; }
    }
</style>
@endsection

@section('content')
<div class="container" style="width:936px;color:black;">
    <div class="row">
        <div class="col-12">
            <div class="card my-4">
                <div class="card-header">
                    <div class="row mx-0">
                        <div class="col-5">
                            <img src="{{asset(site_config()->site_banner)}}" class="mb-4 w-100">
                        </div>
                        <div class="col-7 d-flex justify-content-end align-items-center">
                            <h5><b>{{$serah_terima->transaction->transaction_number}}</b></h5>
                            <button id="print_btn" class="btn btn-primary hide-for-print" onclick="window.print();"><i class=""></i>Print</button>
                        </div>
                        <div class="col-7">
                            <div>Jl. Tebet Barat VI E No. 1, Jakarta Selatan</div>
                            <div><i class="bi bi-phone me-2"></i>0812-2555-5136 dan 0856-4004-4255</div>
                        </div>
                        <div class="col-5 d-flex justify-content-end align-items-center">
                            <h5><b>TANDA TERIMA ALAT</b></h5>
                        </div>
                        <div class="col-12">
                            <hr/>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mx-0">
                        <div class="col-12">
                            <table class="table">
                                <tr>
                                    <td class="auto-width">Nama Penyewa:</td>
                                    <td>{{$serah_terima->transaction->customer->customer_name}}</td>
                                </tr>
                                <tr>
                                    <td>Nomor&nbsp;Handphone:</td>
                                    <td>{{$serah_terima->transaction->customer->customer_phone}}</td>
                                </tr>
                                <tr>
                                    <td>Tanggal&nbsp;Pemakaian:</td>
                                    @php
                                        $date_start = format_time($serah_terima->transaction->transaction_tgl_ambil, "d F Y");
                                        $date_end = format_time($serah_terima->transaction->transaction_tgl_pemulangan, "d F Y");

                                        $_date_start = \Carbon\Carbon::parse($serah_terima->transaction->transaction_tgl_ambil)->startOfDay();
                                        $_date_end = \Carbon\Carbon::parse($serah_terima->transaction->transaction_tgl_pemulangan)->startOfDay();
                                        $date_diff = $_date_start->diffInDays($_date_end);
                                    @endphp
                                    @if($date_start != $date_end)
                                        <td>{{$date_start}} - {{$date_end}}</td>
                                    @else
                                        <td>{{$date_start}}</td>
                                    @endif
                                </tr>
                            </table>
                        </div>
                        <div class="col-12">
                            <table class="table table-bordered table-sm">
                                <thead>
                                    <tr>
                                        <th>No.</th>
                                        <th>Peralatan</th>
                                        <th class="text-center">Unit</th>
                                        <th class="text-center">Serial Number</th>
                                        <th>Ket.</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $i=1;
                                        $loop_arr = [];
                                        foreach($serah_terima->details as $detail){
                                            $go_continue = false;
                                            foreach($loop_arr as $_obj){
                                                if($detail->transaction_detail->product->product_id == $_obj->product_id){
                                                    $_obj->qty += 1;
                                                    $_obj->item_code .= " + ".$detail->transaction_detail->item->item_code;
                                                    $go_continue = true;
                                                }
                                            }
                                            if($go_continue){
                                                continue;
                                            }
                                            if($detail->transaction_detail->item_bundle == 1 && !isset($detail->transaction_detail->item_bundle_id)){
                                                continue;
                                            }
                                            $obj = new \stdClass();
                                            $obj->product_id = $detail->transaction_detail->product->product_id;
                                            $obj->product_type = $detail->transaction_detail->product->type->product_type_name;
                                            $obj->product_brand = $detail->transaction_detail->product->brand->product_brand_name;
                                            $obj->product_name = $detail->transaction_detail->product->product_name;
                                            $obj->qty = 1;
                                            $obj->item_code = $detail->transaction_detail->item->item_code;

                                            array_push($loop_arr, $obj);
                                        }
                                    @endphp
                                    @foreach($loop_arr as $obj)
                                    <tr>
                                        <td>{{$i++}}</td>
                                        <td><b>{{$obj->product_brand}} {{$obj->product_type}}</b> {{$obj->product_name}}</td>
                                        <td class="text-center">{{$obj->qty}}</td>
                                        <td class="text-center">{{$obj->item_code}}</td>
                                        <td></td>
                                    </tr>
                                    @endforeach

                                    @if(false)
                                    @foreach($serah_terima->details as $detail)
                                    <tr>
                                        <td>{{$i++}}</td>
                                        <td><b>{{$detail->transaction_detail->product->brand->product_brand_name}} {{$detail->transaction_detail->product->type->product_type_name}}</b> {{$detail->transaction_detail->product->product_name}}</td>
                                        <td class="text-center">1</td>
                                        <td class="text-center">{{$detail->transaction_detail->item->item_code}}</td>
                                        <td></td>
                                    </tr>
                                    @endforeach
                                    @endif
                                </tbody>
                            </table>
                        </div>
                        <div class="col-12">
                            <div class="border p-2">
                                <div>Tas:
                                    @if(isset($serah_terima->bags) && count($serah_terima->bags) > 0) 
                                        @foreach($serah_terima->bags as $k=>$bag) 
                                            <div>
                                            - ({{$bag->item->item_code}}) <b>{{$bag->item->product->brand->product_brand_name}}</b> {{$bag->item->product->product_name}}
                                            </div>
                                        @endforeach 
                                    @else 
                                    - 
                                    @endif
                                </div>
                                <div class="hide-for-print">
                                    <form method="POST" action="{{route('transaction.rent.serah_terima.print.add_bags')}}">
                                        {{csrf_field()}}
                                        <input type="hidden" name="header_id" value="{{$serah_terima->header_id}}">
                                        <hr/>
                                        Tambah Tas
                                        <select id="tas_picker" class="form-control" name="bags[]" multiple required>
                                            @foreach($bags as $bag)
                                                @foreach($bag->items as $item)
                                                    @if($item->item_status == 1)
                                                    <option value="{{$item->item_id}}">({{$item->item_code}}) - {{$bag->brand->product_brand_name}} {{$bag->product_name}}</option>
                                                    @endif
                                                @endforeach
                                            @endforeach
                                        </select>
                                        <div class="text-end">
                                            <button type="submit" class="btn btn-primary btn-sm mt-2"><i class="bi bi-save me-2"></i>Save</button>
                                        </div>
                                    </form>
                                </div>
                                <hr/>
                                <div>Note:<p class="mt-1">{{$serah_terima->header_notes}}</p></div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border p-2 my-2">
                                <div>Jakarta, {{date("d F Y")}}</div>
                            </div>
                            <div class="border p-2">
                                <div>Jam Pengambilan: {{date('H:i:s')}}</div>
                            </div>
                        </div>
                        <div class="col-8">
                            <div class="border p-2 my-2">
                                <div>- Apabila ada kerusakan/kehilangan di lokasi, menjadi tanggung jawab penyewa.</div>
                                <div>- Sebelum pengambilan barang, terlebih dahulu melakukan pengecekan / tes kelayakan.</div>
                            </div>
                        </div>
                        <div class="col-4 justify-content-center mt-3" style="text-align:center;">
                            <div>FM_Rent</div>
                            <div style="height:100px;">@include('components.common.signature')</div>
                        </div>
                        <div class="col"></div>
                        <div class="col-3 justify-content-center mt-3">
                            <div>Penyewa</div>
                            <div style="height:100px;"></div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <i class="text-muted small">Generated by system.</i>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
    $(document).ready(function(){
        $("body").removeClass("dark");
        $("html").removeAttr('data-bs-theme');

        $("#tas_picker").select2();
    });
</script>
@endsection