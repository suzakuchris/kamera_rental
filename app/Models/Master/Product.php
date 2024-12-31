<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $table = 'mst_products';
    protected $primaryKey = 'product_id';
    public $incrementing = true;

    public function brand(){
        return $this->hasOne(Brand::class, 'product_brand_id', 'product_brand');
    }

    public function type(){
        return $this->hasOne(Type::class, 'product_type_id', 'product_type');
    }

    public function items(){
        return $this->hasMany(Items::class, 'product_id', 'product_id')->where('fg_aktif', 1);
    }

    public function available_items(){
        return $this->hasMany(Items::class, 'product_id', 'product_id')->where('item_status', 1)->where('fg_aktif', 1);
    }

    public function available_items_except($item_id){
        return $this->hasMany(Items::class, 'product_id', 'product_id')->where(function($query) use($item_id){
            $query->where('item_status', 1)
            ->orWhere('item_id', $item_id);
        })->where('fg_aktif', 1)->get();
    }
}
