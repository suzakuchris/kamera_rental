<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Config;

class ConfigController extends Controller
{
    public function index(){
        return view('system.config');
    }

    public function save(Request $req){
        $config = Config::first();
        $config->site_name = $req->site_name;

        if(isset($req->site_banner)){
            $image = $req->file('site_banner');
            $name = uniqid()."_".date('dMY')."_site_banner.jpeg";
            $path = "/images";
            $image->move(public_path().$path, $name);
            $total_name = $path."/".$name;
            $config->site_banner = $total_name;
        }

        if(isset($req->site_logo)){
            $image = $req->file('site_logo');
            $name = uniqid()."_".date('dMY')."_site_logo.jpeg";
            $path = "/images";
            $image->move(public_path().$path, $name);
            $total_name = $path."/".$name;
            $config->site_logo = $total_name;
        }

        $config->save();

        return redirect()->route('config.main')->with(['success_message' => 'Berhasil menyimpan data']);
    }
}
