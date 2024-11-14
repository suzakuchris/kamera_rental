<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product_Image extends Model
{
    use HasFactory;
    protected $table = 'mst_product_images';
    protected $primaryKey = 'image_id';
    public $incrementing = true;
}
