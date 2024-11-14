<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product_Bundling extends Model
{
    use HasFactory;
    protected $table = 'product_bundling';
    protected $primaryKey = 'bundle_id';
    public $incrementing = true;
}
