<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Auth;
use DB;

class DashboardController extends Controller
{
    public function dashboard(){
        $user = Auth::user();
        if($user->role == 1){
            return $this->dashboard_admin();
        }
    }

    public function dashboard_admin(){
        return view('dashboard.admin_dashboard');
    }
}
