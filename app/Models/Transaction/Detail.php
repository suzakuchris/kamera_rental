<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Master\Product;
use App\Models\Master\Items;
use App\Models\Master\Product_Bundling;

use DB;

class Detail extends Model
{
    use HasFactory;
    protected $table = 'transaction_detail';
    protected $primaryKey = 'transaction_detail_id';
    public $incrementing = true;

    public function bundle(){
        return $this->hasOne(Product_Bundling::class, 'bundle_id', 'item_parent_id');
    }

    public function returned_all(){
        $return_data = DB::table('transaction_detail')
                        ->where('item_bundle_id', $this->transaction_detail_id)
                        ->where('item_return', 0)
                        ->count();

        return !($return_data > 0);
    }

    public function product(){
        return $this->hasOne(Product::class, 'product_id', 'item_parent_id');
    }

    public function item(){
        return $this->hasOne(Items::class, 'item_id', 'item_id');
    }

    public function header(){
        return $this->hasOne(Header::class, 'transaction_id', 'transaction_id');
    }
}
