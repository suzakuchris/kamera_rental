<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Auth;
use DB;
use Validator;
use Carbon\Carbon;
use App\Models\Master\Rekening;

class RekeningController extends Controller
{
    public function index(){
        $data['bank'] = bank_lists();
        return view('master.rekening', $data);
    }

    public function search(Request $req){
        $qr_data = DB::table('mst_rekening as a')
                ->select(
                    'a.*', 'b.name as create_name', 'c.name as update_name'
                )
                ->leftJoin('users as b', 'a.created_by', 'b.id')
                ->leftJoin('users as c', 'a.updated_by', 'c.id');

        if(isset($req->search)){
            $search = $req->search;
            $qr_data = $qr_data->where(function($query) use($search){
                $query->where('a.rekening_name', 'like', '%'.$search.'%')
                ->orWhere('a.rekening_number', 'like', '%'.$search.'%');
            });
        }

        $qrData = $qr_data->paginate($req->max_row);

        $data['data'] = $qrData;
        $data['pagination'] =  (string) $qrData->links();
        
        return json_encode($data);
    }

    public function view(Request $req){
        $rekening_id = $req->rekening_id;
        $qr_data = DB::table('mst_rekening')
                    ->where('rekening_id', $rekening_id)
                    ->first();

        return json_encode($qr_data);
    }

    public function upsert(Request $req){
        $validator = Validator::make($req->all(), [
            'rekening_name' => 'required|max:255',
            'rekening_number' => 'required|numeric',
            'rekening_bank' => 'required',
        ],[
            'rekening_name.required' => 'Nama pemilik rekening harus diisi',
            'rekening_name.max' => 'Nama pemilik rekening maksimal :max huruf',
            'rekening_number.required' => 'Nomor rekening harus diisi',
            'rekening_number.numeric' => 'Nomor rekening harus berupa angka',
            'rekening_bank.required' => 'Nama bank harus dipilih',
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
            if(isset($req->rekening_id)){
                $rekening = Rekening::find($req->rekening_id);
                $rekening->updated_by = $user;
            }else{
                $rekening = new Rekening();
                $rekening->created_by = $user;
            }

            $rekening->rekening_atas_nama = $req->rekening_name;
            $rekening->rekening_number = $req->rekening_number;
            $rekening->rekening_nama_bank = $req->rekening_bank;
            $rekening->save();

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
        $rekening_id = $req->rekening_id;

        //cek dipakai
        $product = DB::table('transaction_header')
                    ->where('transaction_rekening', $rekening_id)
                    ->where('fg_aktif', 1)
                    ->first();

        if(isset($product)){
            http_response_code(405);
            exit(json_encode(['Message' => 'Gagal menghapus, Rekening sudah dipakai di transaksi']));
        }

        DB::beginTransaction();
        try{
            $rekening = Rekening::find($rekening_id);
            $rekening->fg_aktif = 0;
            $rekening->save();

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
