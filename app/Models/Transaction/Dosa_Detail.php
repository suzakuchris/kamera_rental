<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dosa_Detail extends Model
{
    use HasFactory;
    protected $table = 'tbl_dosa_detail';
    protected $primaryKey = 'id';
    public $incrementing = true;
}
