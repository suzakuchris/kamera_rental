<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Auth;
use DB;
use Validator;
use Carbon\Carbon;
use App\Models\Master\Brand;

class ProductBrandsController extends Controller
{
    public function index(){
        return view('master.product_brand');
    }

    public function search(Request $req){
        $qr_data = DB::table('mst_product_brand as a')
                ->select(
                    'a.*', 'b.name', 'c.name'
                )
                ->leftJoin('users as b', 'a.created_by', 'b.id')
                ->leftJoin('users as c', 'a.updated_by', 'c.id')
                ->where('a.fg_aktif', 1);

        if(isset($req->search)){
            $search = $req->search;
            $qr_data = $qr_data->where('a.product_brand_name', 'like', '%'.$search.'%');
        }

        $qrData = $qr_data->paginate($req->max_row);

        $data['data'] = $qrData;
        $data['pagination'] =  (string) $qrData->links();
        
        return json_encode($data);
    }

    public function view(Request $req){
        $brand_id = $req->brand_id;
        $qr_data = DB::table('mst_product_brand')
                    ->where('product_brand_id', $brand_id)
                    ->first();

        return json_encode($qr_data);
    }

    public function upsert(Request $req){
        $validator = Validator::make($req->all(), [
            'product_brand_name' => 'required|max:255',
        ],[
            'product_brand_name.required' => 'Tipe produk harus diisi',
            'product_brand_name.max' => 'Tipe produk maksimal :max kata'
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

        $user = Auth::user()->id;
        DB::beginTransaction();
        try{
            if(isset($req->product_brand_id)){
                $brand = Brand::find($req->product_brand_id);
                $brand->updated_by = $user;
            }else{
                $brand = new Brand();
                $brand->created_by = $user;
            }

            $brand->product_brand_name = $req->product_brand_name;
            $brand->save();

            DB::commit();
            http_response_code(200);
            exit(json_encode(['Message' => 'Data berhasil disimpan']));
        }catch(Exception $e){
            DB::rollback();
            http_response_code(405);
            exit(json_encode(['Message' => "Terjadi kesalahan, ".$e->getMessage()]));
        }
    }

    public function delete(Request $req){
        $brand_id = $req->brand_id;

        //cek dipakai
        $product = DB::table('mst_products')
                    ->where('product_brand', $brand_id)
                    ->where('fg_aktif', 1)
                    ->first();

        if(isset($product)){
            http_response_code(405);
            exit(json_encode(['Message' => 'Gagal menghapus, Brand sudah dipakai di produk']));
        }

        DB::beginTransaction();
        try{
            $brand = Brand::find($brand_id);
            $brand->fg_aktif = 0;
            $brand->save();

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
