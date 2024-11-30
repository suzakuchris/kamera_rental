<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Condition extends Model
{
    use HasFactory;
    protected $table = 'mst_condition_item';
    protected $primaryKey = 'condition_id';
    public $incrementing = true;
}
