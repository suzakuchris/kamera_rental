<?php
    use App\Models\Config;
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