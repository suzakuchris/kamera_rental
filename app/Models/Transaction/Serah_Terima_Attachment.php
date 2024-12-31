<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Serah_Terima_Attachment extends Model
{
    use HasFactory;
    protected $table = 'serah_terima_images';
    protected $primaryKey = 'image_id';
    public $incrementing = true;
}
