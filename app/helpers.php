<?php
    use App\Models\Config;
    use Carbon\Carbon;
    use App\Models\Transaction\Header;
    function site_config(){
        $config = Config::first();
        return $config;
    }

    function format_time($time, $format){
        return Carbon::parse($time)->format($format);
    }

    function bank_lists(){
        return [
            'BCA',
            'BNI',
            'Mandiri'
        ];
    }

    function get_transaction_numberv2(){
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

    function get_transaction_number($customer_code = ""){
        $now = Carbon::now();
        $header = Header::where('created_at', $now)->orderBy('created_at', 'desc')->first();
        $increment = 1;
        if(isset($header)){
            $first4 = substr($header->transaction_number, 4);
            try{
                $increment = (int)$first4;
                $increment++;
            }catch(Exception $e){
                $increment = 1;
            }
        }

        $timestamp = str_pad($increment, 4, '0', STR_PAD_LEFT)."/"."FM/".$now->year."/".$now->month."/".str_pad($now->day, 2, '0', STR_PAD_LEFT)."/".$customer_code;
        return $timestamp;
    }

    function comma_separated($number){
        return number_format($number, 0, '.', ',');
    }

    function datetime_stamp($string){
        $datetime = Carbon::parse($string);
        return $datetime->format('Y-m-d')."T".$datetime->format('H:i');
    }

    function in_details($header, $id){
        $details = $header->details;
        foreach($details as $detail){
            if($detail->detail_transaction_id == $id){
                return true;
            }
        }

        return false;
    }

    function item_in_detail($header, $trx_detail, $obj = false){
        $details = $header->details;
        foreach($details as $detail){
            if($detail->item_id == $trx_detail->item_id){
                if($obj){
                    return $detail;
                }
                return true;
            }
        }

        if($obj){
            return null;
        }
        return false;
    }