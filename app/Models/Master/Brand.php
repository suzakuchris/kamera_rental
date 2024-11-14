<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    use HasFactory;
    protected $table = 'mst_product_brand';
    protected $primaryKey = 'product_brand_id';
    public $incrementing = true;
}
