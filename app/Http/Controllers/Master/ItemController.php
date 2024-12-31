<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Master\Product;
use App\Models\Master\Product_Image;
use App\Models\Master\Items;
use App\Models\Master\Brand;
use App\Models\Master\Type;
use App\Models\Master\Customer;
use App\Models\Master\Condition;
use App\Models\Master\Status;
use App\Models\Master\Mitra;

use DB;
use Auth;
use Exception;
use Carbon\Carbon;
use Validator;
class ItemController extends Controller
{
    public function index(){
        return view('master.product_item');
    }

    public function search(Request $req){
        $qr_data = DB::table('mst_product as a')
                ->select(
                    'a.*', 'b.name', 'c.name', 'd.product_type_name', 'e.product_brand_name'
                )
                ->leftJoin('users as b', 'a.created_by', 'b.id')
                ->leftJoin('users as c', 'a.updated_by', 'c.id')
                ->join('mst_product_type as d', 'a.product_type', 'd.product_type_id')
                ->join('mst_product_brand as e', 'a.product_brand', 'e.product_brand_id')
                ->where('a.fg_aktif', 1);

        if(isset($req->search)){
            $search = $req->search;
            $qr_data = $qr_data->where(function($query) use($search){
                $query->where('a.product_name', 'like', '%'.$search.'%')
                ->orWhere('d.product_type_name', 'like', '%'.$search.'%')
                ->orWhere('d.product_brand_name', 'like', '%'.$search.'%');
            });
        }

        $qrData = $qr_data->paginate($req->max_row);

        $data['data'] = $qrData;
        $data['pagination'] =  (string) $qrData->links();

        return json_encode($data);
    }

    public function search_item(Request $req){
        $product_id = $req->product_id;
        $qr_data = DB::table('items as a')
                ->select(
                    'a.*', 'b.name', 'c.name', 'd.status_name', 'e.condition_name',
                    'f.customer_name', 'g.mitra_name'
                )
                ->leftJoin('users as b', 'a.created_by', 'b.id')
                ->leftJoin('users as c', 'a.updated_by', 'c.id')
                ->leftJoin('mst_status_item as d', 'a.item_status', 'd.status_id')
                ->leftJoin('mst_condition_item as e', 'a.item_condition', 'e.condition_id')
                ->leftJoin('mst_customers as f', 'a.item_owner', 'f.customer_id')
                ->leftJoin('mst_mitra as g', 'a.item_owner', 'g.mitra_id')
                ->where('a.fg_aktif', 1);

        if(isset($product_id)){
            $qr_data = $qr_data->where('a.product_id', $product_id);
        }

        if(isset($req->search)){
            $search = $req->search;
            $qr_data = $qr_data->where(function($query) use($search){
                $query->where('a.item_code', 'like', '%'.$search.'%');
            });
        }

        $qrData = $qr_data->paginate($req->max_row);

        $data['data'] = $qrData;
        $data['pagination'] =  (string) $qrData->links();

        return json_encode($data);
    }

    public function product_header(Request $req){
        $data['mode'] = 'add';
        $data['product'] = Product::where('fg_aktif', 1)->where('product_id', $req->product_id)->first();
        return view('master.form.product_item', $data);
    }

    public function view(Request $req){
        $data['mode'] = 'view';
        $data['item'] = Items::find($req->item_id);
        $data['product'] = $data['item']->product;
        $data['status'] = Status::get();
        $data['conditions'] = Condition::where('fg_aktif', 1)->get();
        $data['customers'] = Customer::where('fg_aktif', 1)->get();
        $data['mitras'] = Mitra::where('fg_aktif', 1)->get();
        return view('master.form.item', $data);
    }

    public function add(Request $req){
        $data['mode'] = 'add';
        $data['product'] = Product::find($req->product_id);
        $data['status'] = Status::get();
        $data['conditions'] = Condition::where('fg_aktif', 1)->get();
        $data['customers'] = Customer::where('fg_aktif', 1)->get();
        $data['mitras'] = Mitra::where('fg_aktif', 1)->get();
        return view('master.form.item', $data);
    }

    public function edit(Request $req){
        $data['mode'] = 'edit';
        $data['item'] = Items::find($req->item_id);
        $data['product'] = $data['item']->product;
        $data['status'] = Status::get();
        $data['conditions'] = Condition::where('fg_aktif', 1)->get();
        $data['customers'] = Customer::where('fg_aktif', 1)->get();
        $data['mitras'] = Mitra::where('fg_aktif', 1)->get();
        return view('master.form.item', $data);
    }

    public function upsert(Request $req){
        $except = '';
        if(isset($req->item_id)){
            $except = ','.$req->item_id.",item_id";
        }
        $validator = Validator::make($req->all(), [
            'product_id' => 'required',
            'item_code' => 'required|max:255|unique:items,item_code'.$except,
            'item_owner' => 'required',
            'item_condition' => 'required',
            'item_status' => 'required',
            'item_notes' => 'max:255',
        ],[
            'product_id.required' => 'Terjadi kesalahan, ID Produk tidak ditemukan',
            'item_code.required' => 'Serial Number barang harus diisi',
            'item_code.required' => 'Serial Number barang harus unik',
            'item_code.max' => 'Serial Number barang maksimal :max karakter',
            'item_owner.required' => 'Pemilik barang harus diisi',
            'item_condition.required' => 'Kondisi barang harus diisi',
            'item_status.required' => 'Status barang harus diisi',
            'item_notes.max' => 'Notes barang maksimal :max karakter',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try{
            if(!isset($req->item_id)){
                $item = new Items();
                $item->product_id = $req->product_id;
                $item->created_by = Auth::user()->id;
            }else{
                $item = Items::find($req->item_id);
                $item->updated_by = Auth::user()->id;
            }

            $item->item_code = $req->item_code;
            $item->item_owner = substr($req->item_owner,1,strlen($req->item_owner));
            $item->item_owner_type = substr($req->item_owner,0,1);
            $item->item_condition = $req->item_condition;
            $item->item_harga_perhari = $req->harga_per_hari;
            $item->item_harga_perolehan = $req->harga_perolehan;
            $item->item_status = $req->item_status;
            $item->item_notes = $req->item_notes;

            $item->save();

            DB::commit();
            return redirect()->route('master.item.product.form', ['product_id' => $item->product_id])->with(['success_message' => 'Berhasil menyimpan data']);
        }catch(Exception $e){
            DB::rollback();
            return redirect()->back()->withInput()->with(['error_message' => 'Terjadi kesalahan'.$e->getMessage()]);
        }
    }

    public function delete(Request $req){
        $item_id = $req->item_id;

        DB::beginTransaction();
        try{
            $item = Item::find($item_id);
            $item->fg_aktif = 0;
            $item->save();

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
