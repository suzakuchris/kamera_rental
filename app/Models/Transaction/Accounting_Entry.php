<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Accounting_Entry extends Model
{
    use HasFactory;
    protected $table = 'accounting_entries';
    protected $primaryKey = 'entry_id';
    public $incrementing = true;

    public function images(){
        return $this->hasMany(Accounting_Entry_Attachment::class, 'entry_id', 'entry_id');
    }
}
