<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Master\Product;
use App\Models\Master\Product_Image;
use App\Models\Master\Brand;
use App\Models\Master\Type;

use DB;
use Auth;
use Exception;
use Carbon\Carbon;
use Validator;
class ProductController extends Controller
{
    public function index(){
        return view('master.product');
    }

    public function search(Request $req){
        $qr_data = DB::table('mst_products as a')
                ->select(
                    'a.*', 'b.name', 'c.name', 'd.product_type_name', 'e.product_brand_name'
                )
                ->leftJoin('users as b', 'a.created_by', 'b.id')
                ->leftJoin('users as c', 'a.updated_by', 'c.id')
                ->join('mst_product_type as d', 'a.product_type', 'd.product_type_id')
                ->join('mst_product_brand as e', 'a.product_brand', 'e.product_brand_id')
                ->where('a.fg_aktif', 1);

        if(isset($req->search)){
            $search = $req->search;
            $qr_data = $qr_data->where('a.product_name', 'like', '%'.$search.'%');
        }

        $qrData = $qr_data->paginate($req->max_row);

        $data['data'] = $qrData;
        $data['pagination'] =  (string) $qrData->links();

        return json_encode($data);
    }

    public function add(){
        $data['mode'] = 'add';
        $data['types'] = Type::where('fg_aktif', 1)->get();
        $data['brands'] = Brand::where('fg_aktif', 1)->get();
        return view('master.form.product', $data);
    }

    public function view(Request $req){
        $data['mode'] = 'view';
        $data['types'] = Type::where('fg_aktif', 1)->get();
        $data['brands'] = Brand::where('fg_aktif', 1)->get();
        $data['product'] = Product::find($req->product_id);
        $data['images'] = Product_Image::where('product_id', $req->product_id)->orderBy('image_sort')->get();
        return view('master.form.product', $data);
    }

    public function edit(Request $req){
        $data['mode'] = 'edit';
        $data['types'] = Type::where('fg_aktif', 1)->get();
        $data['brands'] = Brand::where('fg_aktif', 1)->get();
        $data['product'] = Product::find($req->product_id);
        $data['images'] = Product_Image::where('product_id', $req->product_id)->orderBy('image_sort')->get();
        return view('master.form.product', $data);
    }

    public function upsert(Request $req){
        $validator = Validator::make($req->all(), [
            'product_name' => 'required|max:255',
            'product_brand' => 'required',
            'product_type' => 'required',
            // 'product_specification' => 'required',
            // 'product_description' => 'required',
            'product_images_add' => 'required_without:product_images_keep',
            'product_images_keep' => 'required_without:product_images_add'
        ],[
            'product_name.required' => 'Nama produk harus diisi',
            'product_name.max' => 'Nama produk maksimal :max karakter',
            'product_brand.required' => 'Brand harus dipilih',
            'product_type.required' => 'Type harus dipilih',
            // 'product_specification.required' => 'Spesifikasi produk harus diisi',
            // 'product_description.required' => 'Deskripsi produk harus diisi'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try{
            if(!isset($req->product_id)){
                $product = new Product();
                $product->created_by = Auth::user()->id;
            }else{
                $product = Product::find($req->product_id);
                $product->updated_by = Auth::user()->id;
            }

            $product->product_name = $req->product_name;
            $product->product_brand = $req->product_brand;
            $product->product_type = $req->product_type;
            $product->product_specification = $req->product_specification;
            $product->product_description = $req->product_description;
            $product->save();

            $image_to_keep = [];
            if(isset($req->product_images_keep)){
                foreach($req->product_images_keep as $k=>$keep_image){
                    $image = Product_Image::find($keep_image);
                    $image->image_sort = $k;
                    $image->updated_by = Auth::user()->id;
                    $image->save();
                    array_push($image_to_keep, $image->image_id);
                }
            }

            if(isset($req->product_images_add)){
                foreach($req->product_images_add as $k=>$add_image){
                    $image = new Product_Image();
                    $image->product_id = $product->product_id;
                    $image->image_sort = $k;

                    $name = uniqid()."_".date('dMY')."_".$product->product_name.".jpeg";
                    $path = "/images/products/";

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

            $discarding = Product_Image::where('product_id', $product->product_id)
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
            return redirect()->route('master.product')->with(['success_message' => 'Berhasil menyimpan data']);
            // return redirect()->route('master.product.edit', ['product_id' => $product->product_id])->with(['success_message' => 'Berhasil menyimpan data']);
        }catch(Exception $e){
            DB::rollback();
            return redirect()->back()->withInput()->with(['error_message' => 'Terjadi kesalahan'.$e->getMessage()]);
        }
    }

    public function delete(Request $req){
        $product_id = $req->product_id;

        DB::beginTransaction();
        try{
            $product = Product::find($product_id);
            $product->fg_aktif = 0;
            $product->save();

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
