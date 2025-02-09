<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stock_Opname_Detail extends Model
{
    use HasFactory;
    protected $table = 'stock_opname_detail';
    protected $primaryKey = 'id';
    public $incrementing = true;

    public function item(){
        return $this->hasOne(\App\Models\Master\Items::class, 'item_id', 'opname_item');
    }

    public function status(){
        return $this->hasOne(\App\Models\Master\Status::class, 'status_id', 'opname_item_status_after');
    }

    public function condition(){
        return $this->hasOne(\App\Models\Master\Condition::class, 'condition_id', 'opname_item_condition_after');
    }

    public function dosa(){
        return $this->hasOne(Dosa_Detail::class, 'id', 'opname_item_dosa_id');
    }
}
