<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Auth;
use DB;
use Exception;
use Validator;

use App\Models\Transaction\Header;
use App\Models\Transaction\Detail;

class TransactionController extends Controller
{
    public function index(){
        return view('transaction.rent');
    }

    public function search(){
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
}
