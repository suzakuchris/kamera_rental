<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Auth;
use DB;
use Validator;
use Carbon\Carbon;
use App\Models\Master\Status;

class StatusController extends Controller
{
    public function index(){
        return view('master.item_status');
    }

    public function search(Request $req){
        $qr_data = DB::table('mst_status_item as a')
                ->select(
                    'a.*', 'b.name', 'c.name'
                )
                ->leftJoin('users as b', 'a.created_by', 'b.id')
                ->leftJoin('users as c', 'a.updated_by', 'c.id');

        if(isset($req->search)){
            $search = $req->search;
            $qr_data = $qr_data->where('a.status_name', 'like', '%'.$search.'%');
        }

        $qrData = $qr_data->paginate($req->max_row);

        $data['data'] = $qrData;
        $data['pagination'] =  (string) $qrData->links();
        
        return json_encode($data);
    }

    public function view(Request $req){
        $status_id = $req->status_id;
        $qr_data = DB::table('mst_status_item')
                    ->where('status_id', $status_id)
                    ->first();

        return json_encode($qr_data);
    }

    public function upsert(Request $req){
        $validator = Validator::make($req->all(), [
            'status_name' => 'required|max:255',
        ],[
            'status_name.required' => 'Status harus diisi',
            'status_name.max' => 'Status maksimal :max kata'
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
            if(isset($req->status_id)){
                $status = Status::find($req->status_id);
                $status->updated_by = $user;
            }else{
                $status = new Status();
                $status->created_by = $user;
            }

            $status->status_name = $req->status_name;
            $status->save();

            DB::commit();
            http_response_code(200);
            exit(json_encode(['Message' => 'Data berhasil disimpan']));
        }catch(Exception $e){
            DB::rollback();
            http_response_code(405);
            exit(json_encode(['Message' => "Terjadi kesalahan, ".$e->getMessage()]));
        }
    }
}
