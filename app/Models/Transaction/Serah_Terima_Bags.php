<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Master\Product;
use App\Models\Master\Items;

class Serah_Terima_Bags extends Model
{
    use HasFactory;
    protected $table = 'serah_terima_bags';
    protected $primaryKey = 'stbags_id';
    public $incrementing = true;

    public function item(){
        return $this->hasOne(Items::class, 'item_id', 'item_id');
    }
}
