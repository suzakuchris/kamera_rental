@extends('layouts.app', ['no_sidebar' => true, 'allow_scroll' => true])

@section('css')
<style>
    @media print {
        /* visible when printed */
        #print_btn {
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
                            <h5><b>{{$header->transaction_number}}</b></h5>
                            <button id="print_btn" class="btn btn-primary" onclick="window.print();"><i class=""></i>Print</button>
                        </div>
                        <div class="col-7">
                            <div>Jl. Tebet Barat VI E No. 1, Jakarta Selatan</div>
                            <div><i class="bi bi-phone me-2"></i>0812-2555-5136 dan 0856-4004-4255</div>
                        </div>
                        <div class="col-5 d-flex justify-content-end align-items-center">
                            <h5><b><i>INVOICE</i></b></h5>
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
                                    <td>{{$header->customer->customer_name}}</td>
                                </tr>
                                <tr>
                                    <td>Nomor&nbsp;Handphone:</td>
                                    <td>{{$header->customer->customer_phone}}</td>
                                </tr>
                                <tr>
                                    <td>Tanggal&nbsp;Pemakaian:</td>
                                    <td>{{format_time($header->transaction_tgl_ambil, "d F Y")}} - {{format_time($header->transaction_tgl_pemulangan, "d F Y")}}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-12">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>No.</th>
                                        <th>Peralatan</th>
                                        <th class="text-center">Qty</th>
                                        <th class="text-center">Price</th>
                                        <th class="text-center">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $i=1;
                                        $grand_total = 0;
                                    @endphp
                                    @foreach($header->details as $detail)
                                    @php
                                        $grand_total += $detail->item_price;
                                    @endphp
                                    <tr>
                                        <td>{{$i++}}</td>
                                        <td><b>{{$detail->product->brand->product_brand_name}}</b> {{$detail->product->product_name}} @if($detail->item_bundle == 1) (Paket) @endif</td>
                                        <td class="text-center">1</td>
                                        @if($detail->item_bundle == 1 && isset($detail->item_bundle_id))
                                        <td class="text-center">-</td>
                                        <td class="text-center">-</td>
                                        @else
                                        <td class="text-center">{{comma_separated($detail->item_price_per_day)}}</td>
                                        <td class="text-center">{{comma_separated($detail->item_price)}}</td>
                                        @endif
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td></td>
                                        <td colspan="3" class="text-center bg-danger text-white"><b><i>Grand Total</i></b></td>
                                        <td class="text-center"><h4>Rp. {{comma_separated($grand_total)}}</h4></td>
                                    </tr>
                                    <tr>
                                        <td></td>
                                        <td colspan="4" class="text-center bg-success text-white">
                                            <b>{{$rekening->rekening_nama_bank}} - {{$rekening->rekening_number}} - {{$rekening->rekening_atas_nama}}</b>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <div class="col-5">
                            <div class="border p-2 my-2">
                                <div>Jakarta, {{date("d F Y")}}</div>
                            </div>
                        </div>
                        <div class="col-7">
                            <div class="border p-2 my-2">
                                <div>- Apabila ada kerusakan/kehilangan di lokasi, menjadi tanggung jawab penyewa.</div>
                                <div>- Sebelum pengambilan barang, terlebih dahulu melakukan pengecekan / tes kelayakan.</div>
                            </div>
                        </div>
                        <div class="col-4 justify-content-center mt-3">
                            <div>FM_Rent</div>
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
    });
</script>
@endsection