<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Auth;
use DB;
use Exception;
use Validator;

use App\Models\Master\Customer;
use App\Models\Master\Rekening;
use App\Models\Master\Product;
use App\Models\Master\Product_Bundling as Bundle;
use App\Models\Transaction\Header;
use App\Models\Transaction\Detail;

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
                    ->join('mst_rekening as e', 'a.transaction_rekening', 'e.rekening_id');

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
        $data['products'] = Product::where('fg_aktif', 1)->get();
        $data['bundles'] = Bundle::where('fg_aktif', 1)->get();
        return view('transaction.form.rent', $data);
    }

    public function view(Request $req){
        $data['mode'] = 'view';
        return view('transaction.form.rent', $data);
    }

    public function edit(Request $req){
        $data['mode'] = 'edit';
        return view('transaction.form.rent', $data);
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
