<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dosa extends Model
{
    use HasFactory;
    protected $table = 'tbl_dosa_header';
    protected $primaryKey = 'header_id';
    public $incrementing = true;
}
