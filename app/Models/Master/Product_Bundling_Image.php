<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product_Bundling_Image extends Model
{
    use HasFactory;
    protected $table = 'product_bundling_images';
    protected $primaryKey = 'image_id';
    public $incrementing = true;
}
