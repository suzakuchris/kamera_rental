<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Auth;
use DB;
use Validator;
use Carbon\Carbon;
use App\Models\Master\Condition;

class ConditionController extends Controller
{
    public function index(){
        return view('master.item_condition');
    }

    public function search(Request $req){
        $qr_data = DB::table('mst_condition_item as a')
                ->select(
                    'a.*', 'b.name', 'c.name'
                )
                ->leftJoin('users as b', 'a.created_by', 'b.id')
                ->leftJoin('users as c', 'a.updated_by', 'c.id')
                ->where('a.fg_aktif', 1);

        if(isset($req->search)){
            $search = $req->search;
            $qr_data = $qr_data->where('a.condition_name', 'like', '%'.$search.'%');
        }

        $qrData = $qr_data->paginate($req->max_row);

        $data['data'] = $qrData;
        $data['pagination'] =  (string) $qrData->links();
        
        return json_encode($data);
    }

    public function view(Request $req){
        $condition_id = $req->condition_id;
        $qr_data = DB::table('mst_condition_item')
                    ->where('condition', $condition_id)
                    ->first();

        return json_encode($qr_data);
    }

    public function upsert(Request $req){
        $validator = Validator::make($req->all(), [
            'condition_name' => 'required|max:255',
        ],[
            'condition_name.required' => 'Kondisi harus diisi',
            'condition_name.max' => 'Kondisi maksimal :max kata'
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
            if(isset($req->condition_id)){
                $condition = Condition::find($req->condition_id);
                $condition->updated_by = $user;
            }else{
                $condition = new Condition();
                $condition->created_by = $user;
            }

            $condition->condition_name = $req->condition_name;
            $condition->save();

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
        $condition_id = $req->condition_id;

        //cek dipakai
        $product = DB::table('mst_condition_item')
                    ->where('condition_id', $condition_id)
                    ->where('fg_aktif', 1)
                    ->first();

        if(isset($product)){
            http_response_code(405);
            exit(json_encode(['Message' => 'Gagal menghapus, Kondisi sudah dipakai di produk']));
        }

        DB::beginTransaction();
        try{
            $condition = Condition::find($condition_id);
            $condition->fg_aktif = 0;
            $condition->save();

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
