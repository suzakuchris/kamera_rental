<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Master\Items;
use App\Models\Master\Status;
use App\Models\Master\Condition;
use App\Models\Transaction\Stock_Opname;
use App\Models\Transaction\Stock_Opname_Detail;
use App\Models\Transaction\Dosa;
use App\Models\Transaction\Dosa_Detail;
use App\Models\Transaction\Dosa_Attachment;

use Auth;
use DB;
use Carbon\Carbon;
use Exception;
use Validator;

class StockOpnameController extends Controller
{
    public function index(){
        return view('transaction.stock_opname');
    }

    public function search(Request $req){
        $qr_data = DB::table('stock_opname as a')
                    ->select(
                        'a.id', 'a.opname_start_date', 'a.opname_end_date', 'c.name',
                        DB::raw("count(b.id) as total_barang")
                    )
                    ->join('stock_opname_detail as b', 'a.id', 'b.opname_id')
                    ->join('users as c', 'a.opname_user', 'c.id')
                    ->groupBy('a.id', 'a.opname_start_date', 'a.opname_end_date', 'c.name')
                    ->orderBy('a.opname_start_date', 'desc');

        if(isset($req->search)){
            $search = $req->search;
            $qr_data = $qr_data->where(function($query) use($search){
                $query->where('c.name', 'like', '%'.$search.'%');
            });
        }

        if(isset($req->date)){
            $date = Carbon::parse($req->date);
            $qr_data = $qr_data->where('a.opname_start_date', $date);
        }
        
        $qrData = $qr_data->paginate($req->max_row);

        $data['data'] = $qrData;
        $data['pagination'] =  (string) $qrData->links();
        
        return json_encode($data);
    }

    public function add(){
        $data['mode'] = 'add';
        $data['items'] = Items::where('fg_aktif', 1)->orderBy('product_id')->get();
        $data['status'] = Status::get();
        $data['condition'] = Condition::where('fg_aktif', 1)->get();
        $data['start'] = Carbon::now();
        return view('transaction.form.stock_opname', $data);
    }

    public function view(Request $req){
        $id = $req->id;
        $data['mode'] = 'view';
        $data['header'] = Stock_Opname::find($id);
        return view('transaction.form.stock_opname_view', $data);
    }

    public function upsert(Request $req){
        DB::beginTransaction();
        try{
            $header = new Stock_Opname;
            $header->opname_start_date = Carbon::parse($req->start_time);
            $header->opname_end_date = Carbon::now();
            $header->opname_user = Auth::user()->id;
            $header->created_by = Auth::user()->id;
            $header->save();

            $_header = null;

            if(isset($req->items)){
                $items = $req->items;
                foreach($items as $id=>$item){
                    $item_id = $id;
                    $_item = Items::find($item_id);

                    $item_status = $item['item_status'];
                    $item_condi = $item['item_condition'];
                    $item_notes = $item['item_notes'];

                    $_item->item_status = $item_status;
                    $_item->item_condition = $item_condi;
                    $_item->updated_by = Auth::user()->id;
                    $_item->save();

                    $detail = new Stock_Opname_Detail();
                    $detail->opname_id = $header->id;
                    $detail->opname_item = $item_id;
                    $detail->opname_item_status_before = $_item->item_status;
                    $detail->opname_item_condition_before = $_item->item_condition;

                    $detail->opname_item_status_after = $item_status;
                    $detail->opname_item_condition_after = $item_condi;
                    $detail->opname_item_comment = $item_notes;

                    if(isset($item['item_dosa'])){
                        //bikin entri dosa
                        $item_dosa_reason = $item['item_dosa_reason'];
                        $item_dosa_attachment = $item['item_dosa_attachment'];

                        if(!isset($_header)){
                            //bikin header dosa
                            $_header = new Dosa();
                            $_header->created_by = Auth::user()->id;
                            $_header->header_transaction_id = null;
                            $_header->header_datetime = Carbon::now();
                            $_header->header_notes = $item_notes;
                            $_header->save();
                        }

                        //bikin detail dosa
                        $_detail = new Dosa_Detail();
                        $_detail->header_id = $_header->header_id;
                        $_detail->item_id = $item_id;
                        $_detail->dosa_type_id = $item_condi;
                        $_detail->dosa_reason = $item_dosa_reason;
                        $_detail->created_by = Auth::user()->id;
                        $_detail->save();

                        //bikin attachment dosa
                        $image = new Dosa_Attachment();
                        $image->detail_id = $_detail->id;
                        $image->created_by = Auth::user()->id;
                        $image->image_sort = 0;

                        $name = uniqid()."_".date('dMY')."_".$_detail->id.".jpeg";
                        $path = "/images/dosa/attachment/";

                        $exp = explode(',', $item_dosa_attachment);
                        //we just get the last element with array_pop
                        $base64 = array_pop($exp);
                        //decode the image and finally save it
                        $file = base64_decode($base64);
                        // $file = str_replace('data:image/png;base64,', '', $add_image);
                        $success = file_put_contents(public_path().$path.$name, $file);

                        $image->image_name = $name;
                        $image->image_path = $path;
                        $image->save();

                        $detail->opname_item_dosa_id = $_detail->id;
                    }

                    $detail->save();
                }
            }

            DB::commit();
            return redirect()->route('transaction.stock_opname.form.view', ['id' => $header->id])->with(['success_message' => 'Transaksi berhasil dibuat']);
        }catch(Exception $e){
            DB::rollback();
            return redirect()->route('transaction.stock_opname.form.add')->with(['error_message' => 'Terjadi kesalahan, '.$e->getMessage()]);
        }
    }
}
