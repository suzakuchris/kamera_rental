<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Master\Product;
use App\Models\Master\Brand;
use App\Models\Master\Type;

use DB;
use Auth;
use Exception;
use Carbon\Carbon;
class ProductController extends Controller
{
    public function index(){
        return view('master.product');
    }

    public function search(Request $req){
        $qr_data = DB::table('mst_products as a')
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
            $qr_data = $qr_data->where('a.product_name', 'like', '%'.$search.'%');
        }

        $qrData = $qr_data->paginate($req->max_row);

        $data['data'] = $qrData;
        $data['pagination'] =  (string) $qrData->links();

        return json_encode($data);
    }

    public function add(){
        $data['mode'] = 'add';
        $data['types'] = Type::where('fg_aktif', 1)->get();
        $data['brands'] = Brand::where('fg_aktif', 1)->get();
        return view('master.form.product', $data);
    }

    public function view(Request $req){
        $data['mode'] = 'view';
        $data['types'] = Type::where('fg_aktif', 1)->get();
        $data['brands'] = Brand::where('fg_aktif', 1)->get();
        $data['product'] = Product::find($req->product_id);
        $data['images'] = Product_Image::where('product_id', $req->product_id)->get();
        return view('master.form.product', $data);
    }

    public function edit(Request $req){
        $data['mode'] = 'edit';
        $data['types'] = Type::where('fg_aktif', 1)->get();
        $data['brands'] = Brand::where('fg_aktif', 1)->get();
        $data['product'] = Product::find($req->product_id);
        $data['images'] = Product_Image::where('product_id', $req->product_id)->get();
        return view('master.form.product', $data);
    }

    public function save(Request $req){
        dd($req);
    }

    public function delete(Request $req){
        $product_id = $req->product_id;

        DB::beginTransaction();
        try{
            $product = Product::find($product_id);
            $product->fg_aktif = 0;
            $product->save();

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
