<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Auth;
use DB;
use Validator;
use Carbon\Carbon;
use App\Models\Master\Users;
use App\Models\User;

class UsersController extends Controller
{
    var $roles;
    function __construct(){
        $this->roles = [
            1 => 'Superadmin',
            2 => 'Master Operator',
            3 => 'Daily Operator',
        ];
    }
    public function index(){
        $data['roles'] = $this->roles;
        return view('master.user', $data);
    }

    public function search(Request $req){
        $qr_data = DB::table('users as a')
                ->select(
                    'a.*'
                )
                ->where('fg_aktif', 1);

        if(isset($req->search)){
            $search = $req->search;
            $qr_data = $qr_data->where('a.name', 'like', '%'.$search.'%');
        }

        $qrData = $qr_data->paginate($req->max_row);

        $data['data'] = $qrData;
        $data['pagination'] =  (string) $qrData->links();
        
        return json_encode($data);
    }

    public function view(Request $req){
        $user_id = $req->user_id;
        $qr_data = DB::table('users')
                    ->where('id', $user_id)
                    ->first();

        return json_encode($qr_data);
    }

    public function upsert(Request $req){
        $validator = Validator::make($req->all(), [
            'name' => 'required|max:255',
        ],[
            'name.required' => 'Tipe produk harus diisi',
            'name.max' => 'Tipe produk maksimal :max kata'
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
            if(isset($req->user_id)){
                $_user = User::find($req->user_id);
                $_user->updated_by = $user;
            }else{
                $_user = new User();
                $_user->created_by = $user;
            }

            $_user->name = $req->name;
            $_user->email = $req->email;
            $_user->password = bcrypt($req->password);
            $_user->role = $req->role;
            $_user->save();

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
        $user_id = $req->id;

        DB::beginTransaction();
        try{
            $user = User::find($user_id);
            $user->fg_aktif = 0;
            $user->save();

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
