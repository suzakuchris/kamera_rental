<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dosa_Attachment extends Model
{
    use HasFactory;
    protected $table = 'tbl_dosa_detail_attachment';
    protected $primaryKey = 'id';
    public $incrementing = true;
}
