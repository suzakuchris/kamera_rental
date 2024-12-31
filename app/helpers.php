<?php
    use App\Models\Config;
    use Carbon\Carbon;
    use App\Models\Transaction\Header;
    function site_config(){
        $config = Config::first();
        return $config;
    }

    function bank_lists(){
        return [
            'BCA',
            'BNI',
            'Mandiri'
        ];
    }

    function get_transaction_number(){
        $prefix = "TR-";
        $now = Carbon::now();
        $header = Header::where('created_at', $now)->orderBy('created_at', 'desc')->first();
        $increment = 1;
        if(isset($header)){
            $last3 = substr($header->transaction_number, -3);
            $increment = (int)$last3;
            $increment++;
        }
        $timestamp = $prefix.$now->year.$now->month.str_pad($now->day, 2, '0', STR_PAD_LEFT).str_pad($increment, 3, '0', STR_PAD_LEFT);
        return $timestamp;
    }

    function comma_separated($number){
        return number_format($number, 0, '.', ',');
    }