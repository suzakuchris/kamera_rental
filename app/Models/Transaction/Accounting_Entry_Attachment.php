<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Accounting_Entry_Attachment extends Model
{
    use HasFactory;
    protected $table = 'accounting_attachment';
    protected $primaryKey = 'image_id';
    public $incrementing = true;
}
