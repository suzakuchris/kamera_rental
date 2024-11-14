<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Auth;
use DB;
use Validator;
use Carbon\Carbon;
use App\Models\Master\Type;

class ProductTypesController extends Controller
{
    public function index(){
        return view('master.product_type');
    }

    public function search(Request $req){
        $qr_data = DB::table('mst_product_type as a')
                ->select(
                    'a.*', 'b.name', 'c.name'
                )
                ->leftJoin('users as b', 'a.created_by', 'b.id')
                ->leftJoin('users as c', 'a.updated_by', 'c.id')
                ->where('a.fg_aktif', 1);

        if(isset($req->search)){
            $search = $req->search;
            $qr_data = $qr_data->where('a.product_type_name', 'like', '%'.$search.'%');
        }

        $qrData = $qr_data->paginate($req->max_row);

        $data['data'] = $qrData;
        $data['pagination'] =  (string) $qrData->links();
        
        return json_encode($data);
    }

    public function view(Request $req){
        $type_id = $req->type_id;
        $qr_data = DB::table('mst_product_type')
                    ->where('product_type_id', $type_id)
                    ->first();

        return json_encode($qr_data);
    }

    public function upsert(Request $req){
        $validator = Validator::make($req->all(), [
            'product_type_name' => 'required|max:255',
        ],[
            'product_type_name.required' => 'Tipe produk harus diisi',
            'product_type_name.max' => 'Tipe produk maksimal :max kata'
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
            if(isset($req->product_type_id)){
                $type = Type::find($req->product_type_id);
                $type->updated_by = $user;
            }else{
                $type = new Type();
                $type->created_by = $user;
            }

            $type->product_type_name = $req->product_type_name;
            $type->save();

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
        $type_id = $req->type_id;

        //cek dipakai
        $product = DB::table('mst_products')
                    ->where('product_type', $type_id)
                    ->where('fg_aktif', 1)
                    ->first();

        if(isset($product)){
            http_response_code(405);
            exit(json_encode(['Message' => 'Gagal menghapus, Type sudah dipakai di produk']));
        }

        DB::beginTransaction();
        try{
            $type = Type::find($type_id);
            $type->fg_aktif = 0;
            $type->save();

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
