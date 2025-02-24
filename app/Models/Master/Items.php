<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Items extends Model
{
    use HasFactory;
    protected $table = 'items';
    protected $primaryKey = 'item_id';
    public $incrementing = true;

    public function product(){
        return $this->hasOne(Product::class, 'product_id', 'product_id');
    }

    public function getOwner(){
        if($this->item_owner_type == 1){
            return Mitra::find($this->item_owner);
        }else{
            return Customer::find($this->item_owner);
        }
    }
}
