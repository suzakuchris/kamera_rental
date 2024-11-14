<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use DB;
use App\Models\User;

class AuthController extends Controller
{
    public function init(Request $req){
        $user = new User();
        $user->name = "superadmin";
        $user->email = "superadmin@toko.com";
        $user->password = bcrypt("Admin123456!");
        $user->role = 1;
        $user->save();
    }
    
    public function login(Request $req){
        return view('login');
    }

    public function login_process(Request $req){
        $credentials = $req->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $req->session()->regenerate();
 
            return redirect()->intended('dashboard');
        }else{
            return redirect()->route('login')->with(['error_message' => 'Email atau password salah']);
        }
    }

    public function logout(Request $request){
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}
