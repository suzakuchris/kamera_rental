<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Serah_Terima extends Model
{
    use HasFactory;
    protected $table = 'serah_terima_header';
    protected $primaryKey = 'header_id';
    public $incrementing = true;

    public function details(){
        return $this->hasMany(Serah_Terima_Detail::class, 'header_id', 'header_id');
    }

    public function images(){
        return $this->hasMany(Serah_Terima_Attachment::class, 'header_id', 'header_id');
    }

    public function transaction(){
        return $this->hasOne(Header::class, 'transaction_id', 'header_transaction_id');
    }

    public function bags(){
        return $this->hasMany(Serah_Terima_Bags::class, 'header_id', 'header_id');
    }
}
