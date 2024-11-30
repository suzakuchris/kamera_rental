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
}
