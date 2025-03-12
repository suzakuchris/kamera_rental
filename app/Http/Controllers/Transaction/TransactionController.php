<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Auth;
use DB;
use Exception;
use Validator;
use Carbon\Carbon;

use App\Models\Master\Mitra;
use App\Models\Master\Customer;
use App\Models\Master\Rekening;
use App\Models\Master\Product;
use App\Models\Master\Items;
use App\Models\Master\Condition;
use App\Models\Master\Product_Bundling as Bundle;
use App\Models\Master\Product_Bundling_Detail as Bundle_Detail;

use App\Models\Transaction\Header;
use App\Models\Transaction\Detail;
use App\Models\Transaction\Accounting_Entry;
use App\Models\Transaction\Accounting_Entry_Attachment;
use App\Models\Transaction\Dosa;
use App\Models\Transaction\Dosa_Detail;
use App\Models\Transaction\Dosa_Attachment;
use App\Models\Transaction\Serah_Terima;
use App\Models\Transaction\Serah_Terima_Bags;
use App\Models\Transaction\Serah_Terima_Detail;
use App\Models\Transaction\Serah_Terima_Attachment;

use App\Models\Config;

class TransactionController extends Controller
{
    public function index(){
        return view('transaction.rent');
    }

    public function search(Request $req){
        $qr_data = DB::table('transaction_header as a')
                    ->select(
                        'a.*', 'b.name as create_name', 'c.name as update_name',
                        'd.customer_name', 'd.customer_email', 'd.customer_phone',
                        'e.rekening_atas_nama', 'e.rekening_nama_bank', 'e.rekening_number'
                    )
                    ->leftJoin('users as b', 'a.created_by', 'b.id')
                    ->leftJoin('users as c', 'a.updated_by', 'c.id')
                    ->join('mst_customers as d', 'd.customer_id', 'a.transaction_customer')
                    ->join('mst_rekening as e', 'a.transaction_rekening', 'e.rekening_id')
                    ->where('a.fg_aktif', 1);

        if(isset($req->search)){
            $search = $req->search;
            $qr_data = $qr_data->where(function($query) use($search){
                $query->where('a.transaction_number', 'like', '%'.$search.'%')
                ->orWhere('d.customer_name', 'like', '%'.$search.'%')
                ->orWhere('e.rekening_nama_bank', 'like', '%'.$search.'%');
            });
        }
        
        $qrData = $qr_data->paginate($req->max_row);

        $data['data'] = $qrData;
        $data['pagination'] =  (string) $qrData->links();
        
        return json_encode($data);
    }

    public function add(){
        $data['mode'] = 'add';
        $data['customers'] = Customer::where('fg_aktif', 1)->get();
        $data['rekenings'] = Rekening::where('fg_aktif', 1)->get();
        $data['products'] = Product::where('fg_aktif', 1)->where('product_type', '!=', tas_id())->get();
        $data['bundles'] = Bundle::where('fg_aktif', 1)->get();
        return view('transaction.form.rent', $data);
    }

    public function get_item(Request $req){
        $product_id = $req->product;
        $product_type = $req->product_type;

        if($product_type == 'bundle'){
            $bundle = Bundle::where('fg_aktif', 1)->where('bundle_id', $product_id)->first();
            $bundle->products = Bundle_Detail::where('bundle_id', $bundle->bundle_id)->get();
            foreach($bundle->products as $product){
                $_product = Product::with('type', 'brand')->where('product_id', $product->product_id)->where('fg_aktif', 1)->first();
                $product->product = $_product;
                $product->items = Items::where('product_id', $product->product_id)->where('fg_aktif', 1)->get();
                if(isset($product->items)){
                    foreach($product->items as $item){
                        if($item->item_owner_type == 1){
                            //mitra
                            $item->owner = Mitra::where('mitra_id', $item->item_owner)->first();
                        }else{
                            $item->owner = Customer::where('customer_id', $item->item_owner)->first();
                        }
                    }
                }
            }
            return json_encode($bundle);
        }else{
            $product = Product::with('type', 'brand')->where('product_id', $product_id)->where('fg_aktif', 1)->first();
            $product->items = Items::where('product_id', $product->product_id)->where('fg_aktif', 1)->get();
            if(!isset($product->items) || count($product->items) < 1){
                http_response_code(405);
                exit(json_encode(['Message' => "Terjadi kesalahan, Item tidak ditemukan, mohon cek kembali inventory."]));
            }
            foreach($product->items as $item){
                if($item->item_owner_type == 1){
                    //mitra
                    $item->owner = Mitra::where('mitra_id', $item->item_owner)->first();
                }else{
                    $item->owner = Customer::where('customer_id', $item->item_owner)->first();
                }
            }
            return json_encode($product);
        }
    }

    public function view(Request $req){
        $data['mode'] = 'view';
        $data['header'] = Header::find($req->transaction_id);
        if(!isset($data['header'])){
            return redirect()->route('transaction.rent')->with(['error_message' => 'Data tidak ditemukan']);
        }
        $data['customers'] = Customer::where('fg_aktif', 1)->get();
        $data['rekenings'] = Rekening::where('fg_aktif', 1)->get();
        $data['products'] = Product::where('fg_aktif', 1)->where('product_type', '!=', tas_id())->get();
        $data['bundles'] = Bundle::where('fg_aktif', 1)->get();
        return view('transaction.form.rent', $data);
    }

    public function edit(Request $req){
        $data['mode'] = 'edit';
        $data['customers'] = Customer::where('fg_aktif', 1)->get();
        $data['rekenings'] = Rekening::where('fg_aktif', 1)->get();
        $data['products'] = Product::where('fg_aktif', 1)->where('product_type', '!=', tas_id())->get();
        $data['bundles'] = Bundle::where('fg_aktif', 1)->get();
        return view('transaction.form.rent', $data);
    }

    public function upsert(Request $req){
        // dd($req);
        $validator = Validator::make($req->all(), [
            'customer_id' => 'required_without:transaction_id',
            'date_start' => 'required',
            'date_end' => 'required',
            'rekening_id' => 'required_without:transaction_id',
            'details' => 'required_without:details_keep',
            'details_keep' => 'required_without:details',
            'diskon_persen' => 'min:0|max:100',
        ],[
            'customer_id.required' => 'Customer harus dipilih',
            'date_start.required' => 'Tanggal mulai sewa harus dipilih',
            'date_end.required' => 'Tanggal selesai sewa harus dipilih',
            'rekening_id.required' => 'Rekening penerimaan harus dipilih',
            'details.required_without' => 'Detail transaksi sewa harus diisi',
            'details_keep.required_without' => 'Detail transaksi sewa harus diisi',
            'diskon_persen.min' => 'Diskon Persen minimal :min',
            'diskon_persen.max' => 'Diskon Persen maksimal :max'
        ]);

        if($validator->fails()) {
            $errorArr = json_decode($validator->errors());//$validator->messages();
            $errorStr ='';

            foreach ($errorArr as $item) {
                $errorStr .= '<div>'.$item[0].'</div>';
            }

            http_response_code(405);
            exit(json_encode(['Message' => $errorStr]));
        }

        //cek date
        $date_start = Carbon::parse($req->date_start);
        $date_end = Carbon::parse($req->date_end);

        if($date_start->gt($date_end)){
            http_response_code(405);
            exit(json_encode(['Message' => 'Waktu mulai sewa harus sebelum waktu selesai sewa']));
        }

        $user = Auth::user()->id;
        DB::beginTransaction();
        try{
            if(!isset($req->transaction_id)){
                $header = new Header();
                $_cust = Customer::find($req->customer_id);
                $header->transaction_number = get_transaction_number($_cust->suffix_code);
                $header->transaction_customer = $req->customer_id;
                $header->transaction_rekening = $req->rekening_id;
                $header->created_by = $user;
            }else{
                $header = Header::find($req->transaction_id);
                $header->updated_by = $user;
            }

            $header->transaction_notes = $req->notes;
            $header->transaction_tgl_ambil = $req->date_start;
            $header->transaction_tgl_pemulangan = $req->date_end;
            $header->transaction_discount = $req->diskon_lain;
            $header->transaction_discount_percent = $req->diskon_persen;
            $header->transaction_ppn_amount = $req->ppn;

            $header->save();

            $detail_keep_id = [];
            $total_price = 0;

            if(isset($req->details)){
                $details = $req->details;
                foreach($details as $det){
                    $type = $det['product_type'];
                    if($type == "product"){
                        //product
                        $product_id = $det['product_id'];
                        $item_id = $det['product_item'];
                        $days_rent = $det['days_rent'];
                        $harga = $det['harga_per_hari'];
                        $price = $days_rent * $harga;
                        $total_price += $price;

                        if(!isset($item_id)){
                            throw new Exception("Item tidak boleh tidak diisi");
                        }

                        $product = Product::find($product_id);
                        $item = Items::find($item_id);
                        $item->item_status = 0;
                        $item->save();

                        $detail = new Detail();
                        $detail->transaction_id = $header->transaction_id;
                        $detail->item_id = $item_id;
                        $detail->item_parent_id = $product_id;
                        $detail->item_name = $product->product_name;
                        $detail->item_description = $product->product_description;
                        $detail->item_price = $price;
                        $detail->item_days_rent = $days_rent;
                        $detail->item_price_per_day = $harga;
                        $detail->created_by = $user;
                        $detail->save();

                        array_push($detail_keep_id, $detail->transaction_detail_id);
                    }else{
                        //bundle
                        $bundle_id = $det['bundle_id'];
                        $days_rent = $det['days_rent'];
                        $harga = $det['harga_per_hari'];
                        $price = $days_rent * $harga;
                        $total_price += $price;

                        $bundle = Bundle::find($bundle_id);
                        $detail = new Detail();
                        $detail->transaction_id = $header->transaction_id;
                        $detail->item_parent_id = $bundle_id;
                        $detail->item_name = $bundle->bundle_name;
                        $detail->item_description = $bundle->bundle_description;
                        $detail->item_price = $price;
                        $detail->item_days_rent = $days_rent;
                        $detail->item_price_per_day = $harga;
                        $detail->item_bundle = 1;
                        $detail->created_by = $user;
                        $detail->save();

                        array_push($detail_keep_id, $detail->transaction_detail_id);
                        $detail_bundle_id = $detail->transaction_detail_id;

                        $_details = $det['details'];
                        foreach($_details as $_det){
                            $product_id = $_det['product_id'];
                            $item_id = $_det['product_item'];

                            $product = Product::find($product_id);
                            $item = Items::find($item_id);
                            $item->item_status = 0;
                            $item->save();

                            $detail = new Detail();
                            $detail->transaction_id = $header->transaction_id;
                            $detail->item_id = $item_id;
                            $detail->item_parent_id = $product_id;
                            $detail->item_bundle_id = $detail_bundle_id;
                            $detail->item_name = $product->product_name;
                            $detail->item_description = $product->product_description;
                            $detail->item_price = 0;
                            $detail->item_bundle = 1;
                            $detail->created_by = $user;
                            $detail->save();

                            array_push($detail_keep_id, $detail->transaction_detail_id);
                        }
                    }
                }
            }

            if(isset($req->details_keep)){
                $keep = $req->details_keep;
                foreach($keep as $det){
                    $type = $det['product_type'];
                    if($type == "product"){
                        $detail_id = $det["transaction_detail_id"];
                        $product_id = $det['product_id'];
                        $item_id = $det['product_item'];
                        $days_rent = $det['days_rent'];
                        $harga = $det['harga_per_hari'];
                        $price = $days_rent * $harga;
                        $total_price += $price;

                        $detail = Detail::find($detail_id);
                        if($detail->item_id != $item_id){
                            $item = Items::find($item_id);
                            $item->item_status = 0;
                            $item->save();

                            $item = Items::find($detail->item_id);
                            $item->item_status = 1;
                            $item->save();
                        }
                        $detail->item_id = $item_id;
                        $detail->item_price = $price;
                        $detail->item_days_rent = $days_rent;
                        $detail->item_price_per_day = $harga;
                        $detail->updated_by = $user;
                        
                        $detail->save();
                        array_push($detail_keep_id, $detail->transaction_detail_id);
                    }else{
                        //bundle
                        $detail_id = $det["transaction_detail_id"];
                        $bundle_id = $det['bundle_id'];
                        $days_rent = $det['days_rent'];
                        $harga = $det['harga_per_hari'];
                        $price = $days_rent * $harga;
                        $total_price += $price;

                        $detail = Detail::find($detail_id);
                        $detail->item_price = $price;
                        $detail->item_days_rent = $days_rent;
                        $detail->item_price_per_day = $harga;
                        $detail->updated_by = $user;
                        $detail->save();

                        array_push($detail_keep_id, $detail->transaction_detail_id);
                        $detail_bundle_id = $detail->transaction_detail_id;

                        $_details = $det['details'];
                        foreach($_details as $_det){
                            $detail_id = $_det['transaction_detail_id'];
                            $product_id = $_det['product_id'];
                            $item_id = $_det['product_item'];

                            $detail = Detail::find($detail_id);
                            if($detail->item_id != $item_id){
                                $item = Items::find($item_id);
                                $item->item_status = 0;
                                $item->save();
    
                                $item = Items::find($detail->item_id);
                                $item->item_status = 1;
                                $item->save();
                            }
                            $detail->item_id = $item_id;
                            $detail->updated_by = $user;

                            $detail->save();

                            array_push($detail_keep_id, $detail->transaction_detail_id);
                        }
                    }
                }
            }

            $diskon_percent = 0;
            $diskon_lain = 0;
            $ppn = 0;
            if(isset($header->transaction_discount_percent)){
                $diskon_percent = $header->transaction_discount_percent;
            }

            if(isset($header->transaction_discount)){
                $diskon_lain = $header->transaction_discount;
            }

            if(isset($header->transaction_ppn_amount)){
                $ppn = $header->transaction_ppn_amount;
            }

            $total_price = ($total_price - ($total_price*$diskon_percent/100)) - $diskon_lain;
            $total_price += $total_price*$ppn/100;
            $header->transaction_amount = $total_price;
            $header->save();

            //delete unneeded details
            $discard = Detail::from('transaction_detail')
            ->where('transaction_id', $header->transaction_id)
            ->whereNotIn('transaction_detail_id', $detail_keep_id)
            ->get();

            foreach($discard as $dis){
                $item_id = $dis->item_id;
                if(isset($item_id)){
                    $item = Items::find($item_id);
                    $item->item_status = 1;
                    $item->save();
                }

                $dis->fg_aktif = 0;
                $dis->updated_by = $user;
                $dis->save();
            }

            DB::commit();
            return redirect()->route('transaction.rent.view', ['transaction_id' => $header->transaction_id])->with(['success_message' => 'Transaksi berhasil dibuat']);
        }catch(Exception $e){
            DB::rollback();
            http_response_code(405);
            exit(json_encode(['Message' => "Terjadi kesalahan, ".$e->getMessage()]));
        }
    }

    public function search_payment_attachment(Request $req){
        $payment_id = $req->payment_id;
        $attachments = Accounting_Entry_Attachment::where('entry_id', $payment_id)->get();

        $return_data = [];
        foreach($attachments as $attachment){
            array_push($return_data, asset("/").$attachment->image_path.$attachment->image_name);
        }
        return json_encode($return_data);
    }

    public function search_payment(Request $req){
        $qr_data = DB::table('accounting_entries as a')
                    ->select(
                        'a.*', 'b.name as create_name', 'c.name as update_name',
                    )
                    ->leftJoin('users as b', 'a.created_by', 'b.id')
                    ->leftJoin('users as c', 'a.updated_by', 'c.id')
                    ->orderBy('a.entry_date', 'desc')
                    ->orderBy('a.created_at', 'desc')
                    ->where('a.entry_trx_id', $req->transaction_id)
                    ->where('a.fg_aktif', 1);

        if(isset($req->search)){
            $search = $req->search;
            $qr_data = $qr_data->where(function($query) use($search){
                $query->where('a.entry_notes', 'like', '%'.$search.'%');
            });
        }
        
        if(!isset($req->page)){
            $qrData = $qr_data->get(3);
            $data['data'] = $qrData;
            return json_encode($data);
        }else{
            $qrData = $qr_data->paginate($req->max_row);

            $data['data'] = $qrData;
            $data['pagination'] =  (string) $qrData->links();

            return json_encode($data);
        }
    }

    public function add_payment(Request $req){
        $data['mode'] = 'add';
        $data['transaction_id'] = $req->transaction_id;
        $data['transaction'] = Header::find($req->transaction_id);
        return view('transaction.form.payment', $data);
    }

    public function upsert_payment(Request $req){
        $validator = Validator::make($req->all(), [
            'transaction_id' => 'required',
            'tanggal_payment' => 'required',
            'keterangan' => 'required',
            'jumlah_bayar' => 'required|numeric',
            'payment_images_add' => 'required_without:payment_images_keep',
            'payment_images_keep' => 'required_without:payment_images_add'
        ],[
            'transaction_id.required' => 'Data transaksi tidak ditemukan',
            'tanggal_payment.required' => 'Tanggal pembayaran harus diisi',
            'keterangan.required' => 'Silahkan masukan keterangan mengenai pembayaran',
            'jumlah_bayar.required' => 'Silahkan masukan jumlah pembayaran',
            'jumlah_bayar.numeric' => 'Jumlah bayar harus berupa angka'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try{
            if(!isset($req->entry_id)){
                $entry = new Accounting_Entry();
                $entry->entry_trx_id = $req->transaction_id;
                $entry->created_by = Auth::user()->id;
            }else{
                $entry = Accounting_Entry::find($req->entry_id);
                $entry->updated_by = Auth::user()->id;
            }

            $entry->entry_date = Carbon::parse($req->tanggal_payment);
            $entry->entry_cashflow = 1;
            $entry->entry_notes = $req->keterangan;
            $entry->entry_credit = $req->jumlah_bayar;
            $entry->save();

            $image_to_keep = [];
            if(isset($req->payment_images_keep)){
                foreach($req->payment_images_keep as $k=>$keep_image){
                    $image = Accounting_Entry_Attachment::find($keep_image);
                    $image->image_sort = $k;
                    $image->updated_by = Auth::user()->id;
                    $image->save();
                    array_push($image_to_keep, $image->image_id);
                }
            }

            if(isset($req->payment_images_add)){
                foreach($req->payment_images_add as $k=>$add_image){
                    $image = new Accounting_Entry_Attachment();
                    $image->entry_id = $entry->entry_id;
                    $image->image_sort = $k;

                    $name = uniqid()."_".date('dMY')."_".$entry->entry_id.".jpeg";
                    $path = "/images/accounting/receipt/";

                    $exp = explode(',', $add_image);
                    //we just get the last element with array_pop
                    $base64 = array_pop($exp);
                    //decode the image and finally save it
                    $file = base64_decode($base64);
                    // $file = str_replace('data:image/png;base64,', '', $add_image);
                    $success = file_put_contents(public_path().$path.$name, $file);

                    $image->image_name = $name;
                    $image->image_path = $path;
                    $image->created_by = Auth::user()->id;
                    $image->save();

                    array_push($image_to_keep, $image->image_id);
                }
            }

            $discarding = Accounting_Entry_Attachment::where('entry_id', $entry->entry_id)
            ->whereNotIn('image_id', $image_to_keep)
            ->get();

            foreach($discarding as $d){
                $path = $d->image_path;
                $name = $d->image_name;
                $file = public_path().$path.$name;
                if (file_exists($file)) {
                    unlink($file);
                }

                $d->delete();
            }

            DB::commit();
            return redirect()->route('transaction.rent.view', ['transaction_id' => $entry->entry_trx_id])->with(['success_message' => 'Transaksi berhasil dibuat']);
        }catch(Exception $e){
            DB::rollback();
            return redirect()->back()->withInput()->with(['error_message' => 'Terjadi kesalahan'.$e->getMessage()]);
        }
    }

    public function search_dosa(Request $req){
        $qr_data = DB::table('tbl_dosa_header as a')
                    ->select(
                        'a.*', 'b.name as create_name', 'c.name as update_name',
                    )
                    ->leftJoin('users as b', 'a.created_by', 'b.id')
                    ->leftJoin('users as c', 'a.updated_by', 'c.id')
                    ->orderBy('a.header_datetime', 'desc')
                    ->orderBy('a.created_at', 'desc')
                    ->where('a.header_transaction_id', $req->transaction_id)
                    ->where('a.fg_aktif', 1);

        if(isset($req->search)){
            $search = $req->search;
            $qr_data = $qr_data->where(function($query) use($search){
                $query->where('a.header_notes', 'like', '%'.$search.'%');
            });
        }
        
        if(!isset($req->page)){
            $qrData = $qr_data->get(3);
        }else{
            $qrData = $qr_data->paginate($req->max_row);
        }

        foreach($qrData as $qr){
            $qr->details = DB::table('tbl_dosa_detail as a')
                            ->select(
                                'a.*', 'f.product_name', 'g.product_brand_name', 'c.item_code',
                                DB::raw("case when c.item_owner_type = 0 then d.customer_name else e.mitra_name end as owner_name"),
                                'b.condition_name', 'h.image_name', 'h.image_path'
                            )
                            ->leftJoin('mst_condition_item as b', 'a.dosa_type_id', 'b.condition_id')
                            ->leftJoin('items as c', 'a.item_id', 'c.item_id')
                            ->leftJoin('mst_customers as d', 'c.item_owner', 'd.customer_id')
                            ->leftJoin('mst_mitra as e', 'c.item_owner', 'e.mitra_id')
                            ->leftJoin('mst_products as f', 'f.product_id', 'c.product_id')
                            ->leftJoin('mst_product_brand as g', 'f.product_brand', 'g.product_brand_id')
                            ->leftJoin('tbl_dosa_detail_attachment as h', 'h.detail_id', 'a.id')
                            ->where('a.header_id', $qr->header_id)
                            ->get();
        }

        if(!isset($req->page)){
            $data['data'] = $qrData;
            return json_encode($data);
        }else{
            $data['data'] = $qrData;
            $data['pagination'] =  (string) $qrData->links();

            return json_encode($data);
        }
    }

    public function add_dosa(Request $req){
        $data['mode'] = 'add';
        $data['transaction_id'] = $req->transaction_id;
        $data['transaction'] = Header::find($req->transaction_id);
        $data['item_status'] = Condition::where('fg_aktif', 1)->get();
        return view('transaction.form.dosa', $data);
    }

    public function upsert_dosa(Request $req){
        $validator = Validator::make($req->all(), [
            'transaction_id' => 'required',
            'tanggal_dosa' => 'required',
            'keterangan' => 'required',
        ],[
            'transaction_id.required' => 'Data transaksi tidak ditemukan',
            'tanggal_dosa.required' => 'Tanggal dosa harus diisi',
            'keterangan.required' => 'Silahkan masukan keterangan mengenai dosa',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try{
            if(!isset($req->header_id)){
                $header = new Dosa();
                $header->created_by = Auth::user()->user_id;
                $header->header_transaction_id = $req->transaction_id;
            }else{
                $header = Dosa::find($req->header_id);
                $header->updated_by = Auth::user()->user_id;
            }

            $header->header_datetime = Carbon::parse($req->tanggal_dosa);
            $header->header_notes = $req->keterangan;
            $header->save();

            $details = $req->details_keep;
            foreach($details as $detail){
                if($detail["product_type"] == "product" && isset($detail["included"])){
                    $detail_id = $detail["transaction_detail_id"];
                    $__detail = Detail::find($detail_id);
                    
                    $_detail = new Dosa_Detail();
                    $_detail->header_id = $header->header_id;
                    $_detail->item_id = $__detail->item_id;
                    $_detail->dosa_type_id = $detail["dosa_status"];
                    $_detail->dosa_reason = $detail["dosa_notes"];
                    $_detail->created_by = Auth::user()->user_id;
                    $_detail->save();

                    if(isset($detail["dosa_lampiran"])){
                        $image = Dosa_Attachment::where('detail_id', $_detail->id)->first();
                        if(!isset($image)){
                            $image = new Dosa_Attachment();
                            $image->detail_id = $_detail->id;
                            $image->created_by = Auth::user()->id;
                        }else{
                            $image->updated_by = Auth::user()->id;
                        }
                        $image->image_sort = 0;

                        $name = uniqid()."_".date('dMY')."_".$_detail->id.".jpeg";
                        $path = "/images/dosa/attachment/";

                        $exp = explode(',', $detail["dosa_lampiran"]);
                        //we just get the last element with array_pop
                        $base64 = array_pop($exp);
                        //decode the image and finally save it
                        $file = base64_decode($base64);
                        // $file = str_replace('data:image/png;base64,', '', $add_image);
                        $success = file_put_contents(public_path().$path.$name, $file);

                        $image->image_name = $name;
                        $image->image_path = $path;
                        $image->save();
                    }
                }elseif($detail["product_type"] == "bundle"){
                    $bundle_details = $detail["details"];
                    foreach($bundle_details as $b_detail){
                        if($b_detail["product_type"] == "product" && isset($b_detail["included"])){
                            $b_detail_id = $b_detail["transaction_detail_id"];
                            $__detail = Detail::find($b_detail_id);
                            
                            $_detail = new Dosa_Detail();
                            $_detail->header_id = $header->header_id;
                            $_detail->item_id = $__detail->item_id;
                            $_detail->dosa_type_id = $b_detail["dosa_status"];
                            $_detail->dosa_reason = $b_detail["dosa_notes"];
                            $_detail->created_by = Auth::user()->user_id;
                            $_detail->save();
        
                            if(isset($b_detail["dosa_lampiran"])){
                                $image = Dosa_Attachment::where('detail_id', $_detail->id)->first();
                                if(!isset($image)){
                                    $image = new Dosa_Attachment();
                                    $image->detail_id = $_detail->id;
                                    $image->created_by = Auth::user()->id;
                                }else{
                                    $image->updated_by = Auth::user()->id;
                                }
                                $image->image_sort = 0;
        
                                $name = uniqid()."_".date('dMY')."_".$_detail->id.".jpeg";
                                $path = "/images/dosa/attachment/";
        
                                $exp = explode(',', $b_detail["dosa_lampiran"]);
                                //we just get the last element with array_pop
                                $base64 = array_pop($exp);
                                //decode the image and finally save it
                                $file = base64_decode($base64);
                                // $file = str_replace('data:image/png;base64,', '', $add_image);
                                $success = file_put_contents(public_path().$path.$name, $file);
        
                                $image->image_name = $name;
                                $image->image_path = $path;
                                $image->save();
                            }
                        }
                    }
                }
            }
            
            DB::commit();
            return redirect()->route('transaction.rent.view', ['transaction_id' => $header->header_transaction_id])->with(['success_message' => 'Transaksi berhasil dibuat']);
        }catch(Exception $e){
            DB::rollback();
            return redirect()->back()->withInput()->with(['error_message' => 'Terjadi kesalahan'.$e->getMessage()]);
        }
    }

    public function search_serah_terima(Request $req){
        $qr_data = DB::table('serah_terima_header as a')
                    ->select(
                        'a.*', 'b.name as create_name', 'c.name as update_name',
                    )
                    ->leftJoin('users as b', 'a.created_by', 'b.id')
                    ->leftJoin('users as c', 'a.updated_by', 'c.id')
                    ->orderBy('a.header_datetime', 'desc')
                    ->orderBy('a.created_at', 'desc')
                    ->where('a.header_transaction_id', $req->transaction_id)
                    ->where('a.fg_aktif', 1);

        if(isset($req->search)){
            $search = $req->search;
            $qr_data = $qr_data->where(function($query) use($search){
                $query->where('a.header_notes', 'like', '%'.$search.'%');
            });
        }

        if(!isset($req->page)){
            $qrData = $qr_data->get(3);
        }else{
            $qrData = $qr_data->paginate($req->max_row);
        }

        foreach($qrData as $qr){
            $qr->details = DB::table('serah_terima_detail as a')
                            ->select(
                                'a.*', 'c.product_name', 'g.product_brand_name', 'd.item_code',
                                DB::raw("case when d.item_owner_type = 0 then e.customer_name else f.mitra_name end as owner_name"),
                                'i.bundle_name'
                            )
                            ->join('transaction_detail as b', 'a.detail_transaction_id', 'b.transaction_detail_id')
                            ->join('mst_products as c', 'b.item_parent_id', 'c.product_id')
                            ->join('items as d', 'b.item_id', 'd.item_id')
                            ->leftJoin('mst_customers as e', 'd.item_owner', 'e.customer_id')
                            ->leftJoin('mst_mitra as f', 'd.item_owner', 'f.mitra_id')
                            ->join('mst_product_brand as g', 'c.product_brand', 'g.product_brand_id')
                            ->leftJoin('transaction_detail as h', 'b.item_bundle_id', 'h.transaction_detail_id')
                            ->leftJoin('product_bundling as i', 'h.item_parent_id', 'i.bundle_id')
                            ->where('a.header_id', $qr->header_id)
                            ->where('b.fg_aktif', 1)
                            ->get();
        }

        if(!isset($req->page)){
            $data['data'] = $qrData;
            return json_encode($data);
        }else{
            $data['data'] = $qrData;
            $data['pagination'] =  (string) $qrData->links();

            return json_encode($data);
        }
    }

    public function search_serah_terima_attachment(Request $req){
        $serah_terima_id = $req->serah_terima_id;
        $attachments = Serah_Terima_Attachment::where('header_id', $serah_terima_id)->get();

        $return_data = [];
        foreach($attachments as $attachment){
            array_push($return_data, asset("/").$attachment->image_path.$attachment->image_name);
        }
        return json_encode($return_data);
    }

    public function print_serah_terima(Request $req){
        $data['serah_terima'] = Serah_Terima::find($req->transaction_id);
        $data['setting'] = Config::first();
        $data['bags'] = Product::where('fg_aktif', 1)->where('product_type', tas_id())->get(); 
        return view('transaction.serah_terima_print', $data);
    }

    public function print_add_bags(Request $req){
        $validator = Validator::make($req->all(), [
            'header_id' => 'required',
            'bags' => 'required|array|min:1',
        ],[
            'header_id.required' => 'Data transaksi tidak ditemukan',
            'bags.required' => 'Tas harus dipilih',
            'bags.min' => 'Tas harus dipilih'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $header = Serah_Terima::find($req->header_id);

        DB::beginTransaction();
        try{
            foreach($req->bags as $bag){
                $existing = Serah_Terima_Bags::where('header_id', $req->header_id)->where('item_id', $bag)->first();
                if(!isset($existing)){
                    $new_bag = new Serah_Terima_Bags();
                    $new_bag->header_id = $req->header_id;
                    $new_bag->item_id = $bag;
                    $new_bag->created_by = Auth::user()->id;
                    $new_bag->save();

                    $_bag = Items::find($bag);
                    if($header->header_status == 1){
                        //dikasih
                        $_bag->item_status = 2;
                        $_bag->updated_by = Auth::user()->id;
                        $_bag->save();
                    }else{
                        //dibalikin
                        $_bag->item_status = 1;
                        $_bag->updated_by = Auth::user()->id;
                        $_bag->save();
                    }
                }
            }
            DB::commit();
            return redirect()->route('transaction.rent.serah_terima.print', ['transaction_id' => $req->header_id])->with(['success_message' => 'Tas berhasil ditambahkan']);
        }catch(Exception $e){
            DB::rollback();
            return redirect()->back()->with(['error_message' => 'Terjadi kesalahan, '.$e->getMessage()]);
        }
    }

    public function add_serah_terima(Request $req){
        $data['mode'] = 'add';
        $data['transaction_id'] = $req->transaction_id;
        $data['transaction'] = Header::find($req->transaction_id);
        return view('transaction.form.serah_terima_form', $data);
    }

    public function upsert_serah_terima(Request $req){
        $validator = Validator::make($req->all(), [
            'transaction_id' => 'required',
            'tanggal_serah_terima' => 'required',
            'keterangan' => 'required',
            'details_keep' => 'required',
            'serah_terima_status' => 'required|numeric',
            'serah_terima_images_add' => 'required_without:serah_terima_images_keep',
            'serah_terima_images_keep' => 'required_without:serah_terima_images_add'
        ],[
            'transaction_id.required' => 'Data transaksi tidak ditemukan',
            'tanggal_serah_terima.required' => 'Tanggal serah terima harus diisi',
            'keterangan.required' => 'Silahkan masukan keterangan mengenai serah terima',
            'serah_terima_status.required' => 'Silahkan masukan Status serah terima',
            'details_keep.required' => 'Harus pilih barang serah terima',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try{
            if(!isset($req->header_id)){
                $header = new Serah_Terima();
                $header->created_by = Auth::user()->user_id;
                $header->header_transaction_id = $req->transaction_id;
            }else{
                $header = Serah_Terima::find($req->header_id);
                $header->updated_by = Auth::user()->user_id;
            }

            $header->header_datetime = Carbon::parse($req->tanggal_serah_terima);
            $header->header_status = $req->serah_terima_status;
            $header->header_notes = $req->keterangan;
            $header->save();

            $details = $req->details_keep;
            $found = false;
            foreach($details as $detail){
                $detail_id = $detail["transaction_detail_id"];
                if($detail["product_type"] == "product" && isset($detail["included"])){
                    $_detail = new Serah_Terima_Detail();
                    $_detail->header_id = $header->header_id;
                    $_detail->detail_transaction_id = $detail_id;
                    $_detail->created_by = Auth::user()->user_id;
                    $_detail->save();
                    $found = true;

                    $__detail = Detail::find($detail_id);
                    $__detail->item_return = $header->header_status;
                    $__detail->updated_by = Auth::user()->user_id;
                    $__detail->save();
                }elseif($detail["product_type"] == "bundle"){
                    $bundle_detail = $detail["details"];
                    foreach($bundle_detail as $b_detail){
                        $b_detail_id = $b_detail["transaction_detail_id"];
                        if($b_detail["product_type"] == "product" && isset($b_detail["included"])){
                            $_detail = new Serah_Terima_Detail();
                            $_detail->header_id = $header->header_id;
                            $_detail->detail_transaction_id = $b_detail_id;
                            $_detail->created_by = Auth::user()->user_id;
                            $_detail->save();
                            $found = true;
        
                            $__detail = Detail::find($b_detail_id);
                            $__detail->item_return = $header->header_status;
                            $__detail->updated_by = Auth::user()->user_id;
                            $__detail->save();
                        }
                    }
                }
            }

            if(!$found){
                throw new Exception("Tidak ada produk yang dipilih serah terima");
            }

            $image_to_keep = [];
            if(isset($req->serah_terima_images_keep)){
                foreach($req->serah_terima_images_keep as $k=>$keep_image){
                    $image = Serah_Terima_Attachment::find($keep_image);
                    $image->image_sort = $k;
                    $image->updated_by = Auth::user()->id;
                    $image->save();
                    array_push($image_to_keep, $image->image_id);
                }
            }

            if(isset($req->serah_terima_images_add)){
                foreach($req->serah_terima_images_add as $k=>$add_image){
                    $image = new Serah_Terima_Attachment();
                    $image->header_id = $header->header_id;
                    $image->image_sort = $k;

                    $name = uniqid()."_".date('dMY')."_".$header->header_id.".jpeg";
                    $path = "/images/serah_terima/receipt/";

                    $exp = explode(',', $add_image);
                    //we just get the last element with array_pop
                    $base64 = array_pop($exp);
                    //decode the image and finally save it
                    $file = base64_decode($base64);
                    // $file = str_replace('data:image/png;base64,', '', $add_image);
                    $success = file_put_contents(public_path().$path.$name, $file);

                    $image->image_name = $name;
                    $image->image_path = $path;
                    $image->created_by = Auth::user()->id;
                    $image->save();

                    array_push($image_to_keep, $image->image_id);
                }
            }

            $discarding = Serah_Terima_Attachment::where('header_id', $header->header_id)
            ->whereNotIn('image_id', $image_to_keep)
            ->get();

            foreach($discarding as $d){
                $path = $d->image_path;
                $name = $d->image_name;
                $file = public_path().$path.$name;
                if (file_exists($file)) {
                    unlink($file);
                }

                $d->delete();
            }

            DB::commit();
            return redirect()->route('transaction.rent.view', ['transaction_id' => $header->header_transaction_id])->with(['success_message' => 'Transaksi berhasil dibuat']);
        }catch(Exception $e){
            DB::rollback();
            return redirect()->back()->withInput()->with(['error_message' => 'Terjadi kesalahan, '.$e->getMessage()]);
        }
    }


    // =====================================================

    public function payment_index(Request $req){
        $data['transaction_id'] = $req->transaction_id;
        return view('transaction.payment', $data);
    }

    public function payment_search(Request $req){
        return $this->search_payment($req);
    }

    public function payment_detail(Request $req){
        $data['entry'] = Accounting_Entry::find($req->entry_id);
        $data['mode'] = 'view';
        $data['transaction_id'] = $req->transaction_id;
        $data['transaction'] = Header::find($req->transaction_id);
        return view('transaction.form.payment', $data);
    }

    public function payment_delete(Request $req){
        $entry_id = $req->entry_id;

        DB::beginTransaction();
        try{
            $entry = Accounting_Entry::find($entry_id);
            $entry->fg_aktif = 0;
            $entry->save();

            DB::commit();
            http_response_code(200);
            exit(json_encode(['Message' => 'Data berhasil dihapus']));
        }catch(Exception $e){
            DB::rollback();
            http_response_code(405);
            exit(json_encode(['Message' => "Terjadi kesalahan, ".$e->getMessage()]));
        }
    }

    // =====================================================

    public function serah_terima_index(Request $req){
        $data['transaction_id'] = $req->transaction_id;
        return view('transaction.serah_terima', $data);
    }

    public function serah_terima_search(Request $req){
        return $this->search_serah_terima($req);
    }

    public function serah_terima_detail(Request $req){
        $data['serah_terima'] = Serah_Terima::find($req->header_id);
        $data['mode'] = 'view';
        $data['transaction_id'] = $req->transaction_id;
        $data['transaction'] = Header::find($req->transaction_id);
        return view('transaction.form.serah_terima_form', $data);
    }

    public function serah_terima_delete(Request $req){
        $header_id = $req->header_id;

        DB::beginTransaction();
        try{
            $entry = Serah_Terima::find($header_id);
            $entry->fg_aktif = 0;
            $entry->save();

            DB::commit();
            http_response_code(200);
            exit(json_encode(['Message' => 'Data berhasil dihapus']));
        }catch(Exception $e){
            DB::rollback();
            http_response_code(405);
            exit(json_encode(['Message' => "Terjadi kesalahan, ".$e->getMessage()]));
        }
    }

    // =====================================================

    public function dosa_index(Request $req){
        $data['transaction_id'] = $req->transaction_id;
        return view('transaction.dosa', $data);
    }

    public function dosa_search(Request $req){
        return $this->search_dosa($req);
    }

    public function dosa_detail(Request $req){
        $data['dosa'] = Dosa::find($req->header_id);
        $data['mode'] = 'view';
        $data['transaction_id'] = $req->transaction_id;
        $data['transaction'] = Header::find($req->transaction_id);
        $data['item_status'] = Condition::where('fg_aktif', 1)->get();
        return view('transaction.form.dosa', $data);
    }

    public function dosa_delete(Request $req){
        $header_id = $req->header_id;

        DB::beginTransaction();
        try{
            $entry = Dosa::find($header_id);
            $entry->fg_aktif = 0;
            $entry->save();

            DB::commit();
            http_response_code(200);
            exit(json_encode(['Message' => 'Data berhasil dihapus']));
        }catch(Exception $e){
            DB::rollback();
            http_response_code(405);
            exit(json_encode(['Message' => "Terjadi kesalahan, ".$e->getMessage()]));
        }
    }

    public function print(Request $req){
        $data['type'] = "INVOICE";
        $data['header'] = Header::find($req->transaction_id);
        $data['rekening'] = Rekening::where('fg_aktif', 1)->first();
        // dd($data['header']->details);
        $data['setting'] = Config::first();
        return view('transaction.rent_print', $data);
    }

    public function print_quotation(Request $req){
        $data['type'] = "QUOTATION";
        $data['header'] = Header::find($req->transaction_id);
        $data['rekening'] = Rekening::where('fg_aktif', 1)->first();
        // dd($data['header']->details);
        $data['setting'] = Config::first();
        return view('transaction.rent_print', $data);
    }

    public function delete(Request $req){
        $transaction_id = $req->transaction_id;
        
        DB::beginTransaction();
        try{
            $header = Header::find($req->transaction_id);
            $header->fg_aktif = 0;
            $header->save();

            foreach($header->details as $detail){
                if(isset($detail->item_id)){
                    $item = Items::find($detail->item_id);
                    if($item->item_status == 0){
                        $item->item_status = 1;
                        $item->save();  
                    }
                }
            }

            DB::commit();
            http_response_code(200);
            exit(json_encode(['Message' => 'Data berhasil dihapus']));
        }catch(Exception $e){
            DB::rollback();
            http_response_code(405);
            exit(json_encode(['Message' => "Terjadi kesalahan, ".$e->getMessage()]));
        }
    }
}
