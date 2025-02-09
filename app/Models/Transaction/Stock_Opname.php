<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stock_Opname extends Model
{
    use HasFactory;
    protected $table = 'stock_opname';
    protected $primaryKey = 'id';
    public $incrementing = true;

    public function details(){
        return $this->hasMany(Stock_Opname_Detail::class, 'opname_id', 'id');
    }

    public function user(){
        return $this->hasOne(\App\Models\User::class, 'id', 'opname_user');
    }
}
