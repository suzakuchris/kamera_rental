<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product_Bundling_Detail extends Model
{
    use HasFactory;
    protected $table = 'product_bundling_detail';
    protected $primaryKey = 'bundle_detail_id';
    public $incrementing = true;
}
