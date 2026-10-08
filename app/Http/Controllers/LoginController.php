<?php

namespace App\Http\Controllers;

use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('login');
    }

    public function login(Request $request)
    {
        $request->merge([
            'email' => Str::lower(trim((string) $request->input('email'))),
        ]);

        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password']])) {
            if (Auth::user()->is_locked) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'email' => 'Akun Anda sedang dikunci. Hubungi administrator.',
                ])->onlyInput('email');
            }

            $request->session()->regenerate();

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

    public function logout(Request $request)
    {
        app(ActivityLogService::class)->record([
            'action' => 'LOGOUT',
            'module' => 'authentication',
            'description' => 'Keluar dari aplikasi',
            'target_type' => 'session',
        ]);

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
