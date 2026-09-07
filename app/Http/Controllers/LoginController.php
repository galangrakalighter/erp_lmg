<?php

namespace App\Http\Controllers;

use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password']])) {
            app(ActivityLogService::class)->record([
                'action' => 'LOGIN',
                'module' => 'authentication',
                'description' => 'Masuk ke aplikasi',
                'target_type' => 'session',
            ]);

            return redirect('/home');
        } else {
            return back()->withErrors([
                'email' => 'The provided credentials do not match our records.',
            ]);
        }
    }

    public function logout()
    {
        app(ActivityLogService::class)->record([
            'action' => 'LOGOUT',
            'module' => 'authentication',
            'description' => 'Keluar dari aplikasi',
            'target_type' => 'session',
        ]);

        Auth::logout();

        return redirect('/');
    }
}
