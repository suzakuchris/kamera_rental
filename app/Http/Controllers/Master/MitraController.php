<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Master\Mitra;

use DB;
use Auth;
use Exception;
use Carbon\Carbon;
use Validator;

class MitraController extends Controller
{
    public function index(){
        return view('master.mitra');
    }

    public function search(Request $req){
        $qr_data = DB::table('mst_mitra as a')
                ->select(
                    'a.*', 'b.name', 'c.name'
                )
                ->leftJoin('users as b', 'a.created_by', 'b.id')
                ->leftJoin('users as c', 'a.updated_by', 'c.id')
                ->where('a.fg_aktif', 1);

        if(isset($req->search)){
            $search = $req->search;
            $qr_data = $qr_data->where('a.mitra_name', 'like', '%'.$search.'%')
                        ->orWhere('a.mitra_company', 'like', '%'.$search.'%');
        }

        $qrData = $qr_data->paginate($req->max_row);

        $data['data'] = $qrData;
        $data['pagination'] =  (string) $qrData->links();

        return json_encode($data);
    }

    public function add(){
        $data['mode'] = 'add';
        return view('master.form.mitra', $data);
    }

    public function view(Request $req){
        $data['mode'] = 'view';
        $data['mitra'] = Mitra::find($req->mitra_id);
        return view('master.form.mitra', $data);
    }

    public function edit(Request $req){
        $data['mode'] = 'edit';
        $data['mitra'] = Mitra::find($req->mitra_id);
        return view('master.form.mitra', $data);
    }

    public function upsert(Request $req){
        $validator = Validator::make($req->all(), [
            'mitra_name' => 'required|max:255',
            'mitra_company' => 'required|max:255',
        ],[
            'mitra_name.required' => 'Nama mitra harus diisi',
            'mitra_name.max' => 'Nama mitra maksimal :max karakter',
            'mitra_company.required' => 'Nama company harus diisi',
            'mitra_company.max' => 'Nama company maksimal :max karakter',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try{
            if(!isset($req->mitra_id)){
                $mitra = new Mitra();
                $mitra->created_by = Auth::user()->id;
            }else{
                $mitra = Mitra::find($req->mitra_id);
                $mitra->updated_by = Auth::user()->id;
                //cek code
                if(!isset($req->code)){
                    throw new Exception('Mitra Code harus diisi');
                }
                if(len($req->code) > 50){
                    throw new Exception('Mitra Code terlalu panjang!');
                }
                $qr_cek = Mitra::where('fg_aktif', 1)->where('suffix_code', $req->code)->first();
                if(isset($qr_cek)){
                    throw new Exception('Mitra Code sudah ada di database');
                }
                $mitra->suffix_code = $req->code;
            }

            $mitra->mitra_name = $req->mitra_name;
            $mitra->mitra_company = $req->mitra_company;
            $mitra->save();

            DB::commit();
            return redirect()->route('master.mitra')->with(['success_message' => 'Berhasil menyimpan data']);
        }catch(Exception $e){
            DB::rollback();
            return redirect()->back()->withInput()->with(['error_message' => 'Terjadi kesalahan'.$e->getMessage()]);
        }
    }

    public function delete(Request $req){
        $mitra_id = $req->mitra_id;

        DB::beginTransaction();
        try{
            $mitra = Mitra::find($mitra_id);
            $mitra->fg_aktif = 0;
            $mitra->save();

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
