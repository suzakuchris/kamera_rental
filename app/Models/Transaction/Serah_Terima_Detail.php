<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Serah_Terima_Detail extends Model
{
    use HasFactory;
    protected $table = 'serah_terima_detail';
    protected $primaryKey = 'detail_id';
    public $incrementing = true;

    public function transaction_detail(){
        return $this->hasOne(Detail::class, 'transaction_detail_id', 'detail_transaction_id');
    }
}
