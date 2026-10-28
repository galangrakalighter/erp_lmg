<?php

namespace App\Http\Controllers;

use App\Mail\LoginOtpMail;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class LoginController extends Controller
{
    private const OTP_SESSION_KEY = 'login_otp';

    public function showLoginForm(Request $request): View|RedirectResponse
    {
        return $this->challenge($request)
            ? redirect()->route('login.otp.form')
            : view('login');
    }

    public function login(Request $request): RedirectResponse
    {
        $request->merge(['email' => Str::lower(trim((string) $request->input('email')))]);
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ]);

        $rateLimitKey = 'login:'.Str::lower($credentials['email']).'|'.$request->ip();
        if (RateLimiter::tooManyAttempts($rateLimitKey, 5)) {
            return back()->withErrors(['email' => 'Terlalu banyak percobaan login. Coba kembali dalam '.RateLimiter::availableIn($rateLimitKey).' detik.'])->onlyInput('email');
        }

        $provider = Auth::getProvider();
        $user = $provider->retrieveByCredentials(['email' => $credentials['email']]);
        if (! $user || ! $provider->validateCredentials($user, ['password' => $credentials['password']])) {
            RateLimiter::hit($rateLimitKey, 60);
            return back()->withErrors(['email' => 'Email atau password tidak sesuai.'])->onlyInput('email');
        }

        if ($user->is_locked) {
            return back()->withErrors(['email' => 'Akun Anda sedang dikunci. Hubungi administrator.'])->onlyInput('email');
        }

        RateLimiter::clear($rateLimitKey);
        $request->session()->regenerate();

        if ($this->bypassesOtp($user)) {
            Auth::login($user, $request->boolean('remember'));
            $request->session()->forget(self::OTP_SESSION_KEY);
            $request->session()->regenerate();

            app(ActivityLogService::class)->record([
                'action' => 'LOGIN',
                'module' => 'authentication',
                'description' => 'Masuk ke aplikasi tanpa OTP untuk akun yang dikecualikan',
                'target_type' => 'session',
            ]);

            return redirect()->intended('/home');
        }

        try {
            $this->sendOtp($request, $user, $request->boolean('remember'));
        } catch (Throwable $exception) {
            report($exception);
            $request->session()->forget(self::OTP_SESSION_KEY);
            return back()->withErrors(['email' => 'Kode OTP belum dapat dikirim. Periksa konfigurasi email atau coba kembali.'])->onlyInput('email');
        }

        return redirect()->route('login.otp.form')->with('status', 'Kode OTP telah dikirim ke email Anda.');
    }

    public function showOtpForm(Request $request): View|RedirectResponse
    {
        $challenge = $this->challenge($request);
        if (! $challenge) {
            return redirect()->route('login')->withErrors(['email' => 'Sesi OTP sudah berakhir. Silakan login kembali.']);
        }

        return view('auth.otp', [
            'maskedEmail' => $this->maskEmail((string) $challenge['email']),
            'expiresInMinutes' => (int) config('auth_otp.expires_minutes', 5),
            'resendAfter' => max(0, (int) $challenge['sent_at'] + (int) config('auth_otp.resend_seconds', 60) - now()->timestamp),
        ]);
    }

    public function verifyOtp(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'otp' => ['required', 'digits:6'],
        ], [
            'otp.required' => 'Masukkan kode OTP.',
            'otp.digits' => 'Kode OTP harus terdiri dari 6 angka.',
        ]);

        $challenge = $this->challenge($request);
        if (! $challenge) {
            return redirect()->route('login')->withErrors(['email' => 'Sesi OTP sudah berakhir. Silakan login kembali.']);
        }

        $maxAttempts = (int) config('auth_otp.max_attempts', 5);
        if ((int) $challenge['attempts'] >= $maxAttempts) {
            $request->session()->forget(self::OTP_SESSION_KEY);
            return redirect()->route('login')->withErrors(['email' => 'Batas percobaan OTP telah tercapai. Silakan login kembali.']);
        }

        if (! Hash::check($validated['otp'], $challenge['code_hash'])) {
            $challenge['attempts'] = (int) $challenge['attempts'] + 1;
            if ($challenge['attempts'] >= $maxAttempts) {
                $request->session()->forget(self::OTP_SESSION_KEY);
                return redirect()->route('login')->withErrors([
                    'email' => 'Batas percobaan OTP telah tercapai. Silakan login kembali.',
                ]);
            }
            $request->session()->put(self::OTP_SESSION_KEY, $challenge);
            return back()->withErrors(['otp' => 'Kode OTP tidak sesuai. Sisa percobaan: '.max(0, $maxAttempts - $challenge['attempts']).'.']);
        }

        $user = User::find($challenge['user_id']);
        if (! $user || $user->is_locked) {
            $request->session()->forget(self::OTP_SESSION_KEY);
            return redirect()->route('login')->withErrors(['email' => 'Akun tidak tersedia atau sedang dikunci.']);
        }

        Auth::login($user, (bool) $challenge['remember']);
        $request->session()->forget(self::OTP_SESSION_KEY);
        $request->session()->regenerate();

        app(ActivityLogService::class)->record([
            'action' => 'LOGIN',
            'module' => 'authentication',
            'description' => 'Masuk ke aplikasi dengan verifikasi OTP',
            'target_type' => 'session',
        ]);

        return redirect()->intended('/home');
    }

    public function resendOtp(Request $request): RedirectResponse
    {
        $challenge = $this->challenge($request);
        if (! $challenge) {
            return redirect()->route('login')->withErrors(['email' => 'Sesi OTP sudah berakhir. Silakan login kembali.']);
        }

        $wait = (int) config('auth_otp.resend_seconds', 60) - (now()->timestamp - (int) $challenge['sent_at']);
        if ($wait > 0) {
            return back()->withErrors(['otp' => 'Kode baru dapat dikirim ulang dalam '.$wait.' detik.']);
        }

        $user = User::find($challenge['user_id']);
        if (! $user || $user->is_locked) {
            $request->session()->forget(self::OTP_SESSION_KEY);
            return redirect()->route('login');
        }

        try {
            $this->sendOtp($request, $user, (bool) $challenge['remember']);
        } catch (Throwable $exception) {
            report($exception);
            return back()->withErrors(['otp' => 'Kode OTP belum dapat dikirim ulang.']);
        }

        return back()->with('status', 'Kode OTP baru telah dikirim.');
    }

    public function cancelOtp(Request $request): RedirectResponse
    {
        $request->session()->forget(self::OTP_SESSION_KEY);
        return redirect()->route('login');
    }

    public function logout(Request $request): RedirectResponse
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

    private function sendOtp(Request $request, User $user, bool $remember): void
    {
        $code = (string) random_int(100000, 999999);
        $expiresAt = now()->addMinutes((int) config('auth_otp.expires_minutes', 5));
        Mail::to($user->email)->send(new LoginOtpMail($code, $expiresAt));
        $request->session()->put(self::OTP_SESSION_KEY, [
            'user_id' => $user->id,
            'email' => $user->email,
            'remember' => $remember,
            'code_hash' => Hash::make($code),
            'expires_at' => $expiresAt->timestamp,
            'sent_at' => now()->timestamp,
            'attempts' => 0,
        ]);
    }

    private function challenge(Request $request): ?array
    {
        $challenge = $request->session()->get(self::OTP_SESSION_KEY);
        if (! is_array($challenge) || empty($challenge['expires_at']) || now()->timestamp > (int) $challenge['expires_at']) {
            $request->session()->forget(self::OTP_SESSION_KEY);
            return null;
        }
        return $challenge;
    }

    private function maskEmail(string $email): string
    {
        [$name, $domain] = array_pad(explode('@', $email, 2), 2, '');
        $visible = mb_substr($name, 0, min(2, mb_strlen($name)));
        return $visible.str_repeat('*', max(3, mb_strlen($name) - mb_strlen($visible))).'@'.$domain;
    }

    private function bypassesOtp(User $user): bool
    {
        return in_array(
            Str::lower(trim((string) $user->email)),
            config('auth_otp.bypass_emails', []),
            true
        );
    }
}
