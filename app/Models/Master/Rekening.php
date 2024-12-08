<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rekening extends Model
{
    use HasFactory;
    protected $table = 'mst_rekening';
    protected $primaryKey = 'rekening_id';
    public $incrementing = true;
}
