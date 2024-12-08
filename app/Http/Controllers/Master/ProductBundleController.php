<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Master\Product;
use App\Models\Master\Product_Image;
use App\Models\Master\Product_Bundling;
use App\Models\Master\Product_Bundling_Image;
use App\Models\Master\Product_Bundling_Detail;
use App\Models\Master\Brand;
use App\Models\Master\Type;

use DB;
use Auth;
use Exception;
use Carbon\Carbon;
use Validator;
class ProductBundleController extends Controller
{
    public function index(){
        return view('master.product_bundle');
    }

    public function search(Request $req){
        $qr_data = DB::table('product_bundling as a')
                ->select(
                    'a.*', 'b.name as create_name', 'c.name as update_name'
                )
                ->leftJoin('users as b', 'a.created_by', 'b.id')
                ->leftJoin('users as c', 'a.updated_by', 'c.id')
                ->where('a.fg_aktif', 1);

        if(isset($req->search)){
            $search = $req->search;
            $qr_data = $qr_data->where(function($_query){
                $_query->where('a.bundle_name', 'like', '%'.$search.'%')
                ->orWhere('a.bundle_description', 'like', '%'.$search.'%')
                ->orWhere('a.bundle_specification', 'like', '%'.$search.'%');
            });
        }

        $qrData = $qr_data->paginate($req->max_row);

        foreach($qrData as $_data){
            $qr_detail = DB::table('product_bundling_detail as a')
                         ->select(
                            'b.product_name', 'c.product_type_name', 'd.product_brand_name',
                            'b.product_id', DB::raw("count(distinct a.bundle_detail_id) as jumlah_item")
                         )
                         ->join('mst_products as b', 'a.product_id', 'b.product_id')
                         ->join('mst_product_type as c', 'b.product_type', 'c.product_type_id')
                         ->join('mst_product_brand as d', 'b.product_brand', 'd.product_brand_id')
                         ->where('a.bundle_id', $_data->bundle_id)
                         ->where('b.fg_aktif', 1)
                         ->where('c.fg_aktif', 1)
                         ->where('d.fg_aktif', 1)
                         ->groupBy('b.product_name', 'c.product_type_name', 'd.product_brand_name', 'b.product_id')
                         ->get();

            $_data->details = $qr_detail;
        }

        $data['data'] = $qrData;
        $data['pagination'] =  (string) $qrData->links();

        return json_encode($data);
    }

    public function add(){
        $data['mode'] = 'add';
        $data['products'] = Product::where('fg_aktif', 1)->get();
        return view('master.form.product_bundle', $data);
    }

    public function view(Request $req){
        $data['mode'] = 'view';
        $data['bundle'] = Product_Bundling::where('fg_aktif', 1)->where('bundle_id', $req->bundle_id)->first();
        $data['bundle_product'] = DB::table('product_bundling_detail as a')
                                  ->select('a.bundle_detail_id', 'b.product_name')
                                  ->join('mst_products as b', 'a.product_id', 'b.product_id')
                                  ->where('a.bundle_id', $req->bundle_id)
                                  ->orderBy('a.product_sort')
                                  ->get();

        $data['products'] = Product::where('fg_aktif', 1)->get();
        $data['images'] = Product_Bundling_Image::where('bundle_id', $req->bundle_id)->orderBy('image_sort')->get();
        return view('master.form.product_bundle', $data);
    }

    public function edit(Request $req){
        $data['mode'] = 'edit';
        $data['bundle'] = Product_Bundling::where('fg_aktif', 1)->where('bundle_id', $req->bundle_id)->first();
        $data['bundle_product'] = DB::table('product_bundling_detail as a')
                                  ->select('a.bundle_detail_id', 'b.product_name')
                                  ->join('mst_products as b', 'a.product_id', 'b.product_id')
                                  ->where('a.bundle_id', $req->bundle_id)
                                  ->orderBy('a.product_sort')
                                  ->get();

        $data['products'] = Product::where('fg_aktif', 1)->get();
        $data['images'] = Product_Bundling_Image::where('bundle_id', $req->bundle_id)->orderBy('image_sort')->get();
        return view('master.form.product_bundle', $data);
    }

    public function upsert(Request $req){
        $validator = Validator::make($req->all(), [
            'bundle_name' => 'required|max:255',
            'bundle_price' => 'required',
            // 'bundle_description' => 'required',
            'bundle_specification' => 'required',
            'product_add' => 'required_without:product_keep',
            'product_keep' => 'required_without:product_add',
        ],[
            'bundle_name.required' => 'Nama produk bundling harus diisi',
            'bundle_name.max' => 'Nama produk maksimal :max karakter',
            'bundle_price.required' => 'Harga estimasi harus diisi',
            // 'bundle_description.required' => 'Deskripsi bundling produk harus diisi',
            'bundle_specification.required' => 'Spesifikasi bundling produk harus diisi'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try{
            if(isset($req->bundle_id)){
                $bundle = Product_Bundling::find($req->bundle_id);
                $bundle->updated_by = Auth::user()->user_id;
            }else{
                $bundle = new Product_Bundling();
                $bundle->created_by = Auth::user()->user_id;
            }

            $bundle->bundle_name = $req->bundle_name;
            $bundle->bundle_harga_perhari = $req->bundle_price;
            $bundle->bundle_description = $req->bundle_description;
            $bundle->bundle_specification = $req->bundle_specification;
            $bundle->save();

            $product_to_keep = [];
            if(isset($req->product_keep)){
                $product_keep = $req->product_keep;
                foreach($product_keep as $k=>$keep){
                    $detail = Product_Bundling_Detail::find($keep);
                    $detail->product_sort = $k;
                    $detail->updated_by = Auth::user()->user_id;
                    $detail->save();
    
                    array_push($product_to_keep, $keep);
                }
            }

            if(isset($req->product_add)){
                $product_add = $req->product_add;
                foreach($product_add as $add){
                    $detail = new Product_Bundling_Detail();
                    $detail->bundle_id = $bundle->bundle_id;
                    $detail->product_id = $add;
                    $detail->created_by = Auth::user()->user_id;
                    $detail->save();
    
                    array_push($product_to_keep, $detail->bundle_detail_id);
                }
            }

            Product_Bundling_Detail::where('bundle_id', $bundle->bundle_id)
            ->whereNotIn('product_id', $product_to_keep)
            ->get();

            $image_to_keep = [];
            if(isset($req->bundle_images_keep)){
                foreach($req->bundle_images_keep as $k=>$keep_image){
                    $image = Product_Bundling_Image::find($keep_image);
                    $image->image_sort = $k;
                    $image->updated_by = Auth::user()->id;
                    $image->save();
                    array_push($image_to_keep, $image->image_id);
                }
            }

            if(isset($req->bundle_images_add)){
                foreach($req->bundle_images_add as $k=>$add_image){
                    $image = new Product_Bundling_Image();
                    $image->bundle_id = $bundle->bundle_id;
                    $image->image_sort = $k;

                    $name = uniqid()."_".date('dMY')."_".$bundle->bundle_name.".jpeg";
                    $path = "/images/bundles/";

                    $exp = explode(',', $add_image);
                    //we just get the last element with array_pop
                    $base64 = array_pop($exp);
                    //decode the image and finally save it
                    $file = base64_decode($base64);
                    // $file = str_replace('data:image/png;base64,', '', $add_image);
                    $success = file_put_contents(public_path().$path.$name, $file);

                    $image->image_name = $name;
                    $image->image_path = $path;
                    $image->created_by = Auth::user()->id;
                    $image->save();

                    array_push($image_to_keep, $image->image_id);
                }
            }

            // dd($image_to_keep);

            $discarding = Product_Bundling_Image::where('bundle_id', $bundle->bundle_id)
            ->whereNotIn('image_id', $image_to_keep)
            ->get();

            foreach($discarding as $d){
                $path = $d->image_path;
                $name = $d->image_name;
                $file = public_path().$path.$name;
                if (file_exists($file)) {
                    unlink($file);
                }

                $d->delete();
            }

            DB::commit();
            return redirect()->route('master.product_bundle')->with(['success_message' => 'Berhasil menyimpan data']);
            // return redirect()->route('master.product_bundle.edit', ['bundle_id' => $bundle->bundle_id])->with(['success_message' => 'Berhasil menyimpan data']);
        }catch(Exception $e){
            DB::rollback();
            return redirect()->back()->withInput()->with(['error_message' => 'Terjadi kesalahan'.$e->getMessage()]);
        }
    }

    public function delete(Request $req){
        $bundle_id = $req->bundle_id;

        DB::beginTransaction();
        try{
            $bundle = Product_Bundling::find($bundle_id);
            $bundle->fg_aktif = 0;
            $bundle->save();

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
