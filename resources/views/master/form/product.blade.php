@extends('layouts.app')

@section('content_header')
Add New Product
@endsection

@section('content')
<form>
    <fieldset class="border p-2">
        <legend class="w-auto">Data Produk</legend>
        <div class="row mx-0">
            <div class="col-12">
                <div class="form-group">
                    <label>Nama Produk</label>
                    <input name="product_name" class="form-control" required placeholder="Masukan nama produk">
                </div>
            </div>
            <div class="col-6">
                <div class="form-group">
                    <label>Tipe Produk</label>
                    <select name="product_type" class="form-control" required>
                        <option value="" disabled selected>--Pilih Tipe Produk--</option>
                        @foreach($types as $type)
                        <option value="{{$type->product_type_id}}">{{$type->product_type_name}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-6">
                <div class="form-group">
                    <label>Brand Produk</label>
                    <select name="product_brand" class="form-control" required>
                        <option value="" disabled selected>--Pilih Brand Produk--</option>
                        @foreach($brands as $brand)
                        <option value="{{$brand->product_brand_id}}">{{$brand->product_brand_name}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </fieldset>
</form>
@endsection

@section('content_footer')
<div class="row mx-0">
    <div class="col"></div>
    <div class="col-auto">
        <label class="btn btn-success"><i class="bi bi-save me-2"></i>Save</label>
    </div>
</div>
@endsection