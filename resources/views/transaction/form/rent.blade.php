@extends('layouts.app')

@section('css')

@endsection

@section('content_header')
@if($mode == 'add')
Add New Transaction
@elseif($mode == 'view')
View Transaction
@elseif($mode == 'edit')
Update Transaction
@endif
@endsection

@section('content')
<form method="POST" action="{{route('transaction.rent.upsert')}}" onsubmit="pre_submit(event, this);">
    <fieldset class="border p-2">
        {{ csrf_field() }}
        <input type="hidden" name="transaction_id" @if(isset($transaction)) value="{{$transaction->transaction_id}}" @endif>
        <button type="submit" id="SubmitBtn" class="d-none"></button>
        <div class="row">
            <div class="col-12 mb-2 text-center">
                <h4>Form Transaksi</h4>
            </div>
            <div class="col-12">
                <table class="table header-table w-100">
                    <tbody>
                        <tr>
                            <td colspan="2"><h5>Data Transaksi</h5></td>
                        </tr>
                        <tr>
                            <td>No. Invoice</td>
                            <td>
                                <input type="text" value="-" disabled class="form-control">
                            </td>
                        </tr>
                        <tr>
                            <td>Customer</td>
                            <td>
                                @if($mode == 'add')
                                <select style="width:100%;" name="customer_id" class="w-100 form-control select-searchable">
                                    <option value="">--Silahkan Pilih Customer--</option>
                                    @foreach($customers as $customer)
                                    <option value="{{$customer->customer_id}}">{{$customer->customer_name}}</option>
                                    @endforeach
                                </select>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td>Tanggal Sewa</td>
                            <td>
                                @if($mode=='add')
                                <div class="d-flex align-items-center">
                                    <input type="date" name="date_start" class="form-control" value="{{date('Y-m-d')}}">
                                    <b class="mx-2">-</b>
                                    <input type="date" name="date_end" class="form-control" value="{{date('Y-m-d')}}">
                                </div>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td>Rekening</td>
                            <td>
                                <select style="width:100%;" name="rekening_id" class="w-100 form-control select-searchable">
                                    <option value="">--Silahkan Pilih Rekening--</option>
                                    @foreach($rekenings as $rekening)
                                    <option value="{{$rekening->rekening_id}}">{{$rekening->rekening_number}} - {{$rekening->rekening_atas_nama}}</option>
                                    @endforeach
                                </select>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="col-12">
                <table class="table detail-table w-100">
                    <thead>
                        <tr>
                            <td colspan="8"><h5>Alat yang Disewa</h5></td>
                        </tr>
                        <tr>
                            <td colspan="8">
                                <div class="input-group mb-1 flex-nowrap">
                                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                                    <select class="w-100 form-control select-searchable" style="width:100% !important">
                                        <option>Cari Alat / Produk</option>
                                        <optgroup label="Produk Satuan">
                                            @foreach($products as $product)
                                            <option value="{{$product->product_id}}" data-group="product">{{$product->product_name}}</option>
                                            @endforeach
                                        </optgroup>
                                        <optgroup label="Produk Bundling">
                                            @foreach($bundles as $bundle)
                                            <option value="{{$bundle->bundle_id}}" data-group="bundle">{{$bundle->bundle_name}}</option>
                                            @endforeach
                                        </optgroup>
                                    </select>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>No.</td>
                            <td>Nama Alat</td>
                            <td>Brand</td>
                            <td>Ownership</td>
                            <td>Day</td>
                            <td>Harga Sewa per hari</td>
                            <td>Total Harga Sewa</td>
                            <td class="auto-width"></td>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="8" class="text-center">
                                Belum Ada Data
                            </td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td class="text-end" colspan="6">Total</td>
                            <td></td>
                            <td>
                                <div id="total_detail"></div>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-end" colspan="6">Diskon (%)</td>
                            <td>
                                <input type="number" name="diskon_persen" value="0" class="form-control">
                            </td>
                            <td></td>
                        </tr>
                        <tr>
                            <td class="text-end" colspan="6">Diskon Lainnya</td>
                            <td>
                                <input type="number" name="diskon_lain" value="0" class="form-control">
                            </td>
                            <td></td>
                        </tr>
                        <tr>
                            <td class="text-end" colspan="6">PPn (%)</td>
                            <td>
                                <input type="number" name="ppn" value="10" class="form-control" disabled>
                            </td>
                            <td></td>
                        </tr>
                        <tr>
                            <td class="text-end" colspan="6">Grand Total</td>
                            <td>
                                <input type="text" name="grand_total" class="form-control" disabled>
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </fieldset>
</form>
@endsection

@section('content_footer')
<div class="row mx-0">
    <div class="col"></div>
    <div class="col-auto">
        @if($mode == 'add')
        <label for="SubmitBtn" class="btn btn-success"><i class="bi bi-save me-2"></i>Save</label>
        @else
        <button class="btn btn-success">Tambah Payment</button>
        <button class="btn btn-success">Tambah Dosa</button>
        <button class="btn btn-success">Buat Serah Terima</button>
        @endif
    </div>
</div>
@endsection

@section('js')
<script>
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

@section('footer')

@endsection