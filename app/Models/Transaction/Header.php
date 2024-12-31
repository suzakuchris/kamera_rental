<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Master\Customer;
use App\Models\Master\Rekening;

class Header extends Model
{
    use HasFactory;
    protected $table = 'transaction_header';
    protected $primaryKey = 'transaction_id';
    public $incrementing = true;

    public function customer(){
        return $this->hasOne(Customer::class, 'customer_id', 'transaction_customer');
    }

    public function rekening(){
        return $this->hasOne(Rekening::class, 'rekening_id', 'transaction_rekening');
    }
    
    public function details(){
        return $this->hasMany(Detail::class, 'transaction_id', 'transaction_id')->where('fg_aktif', 1);
    }
}
