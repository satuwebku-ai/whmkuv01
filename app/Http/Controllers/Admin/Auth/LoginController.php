<?php

namespace App\Http\Controllers\Admin\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    /**
     * URL tujuan setelah login berhasil.
     */
    protected string $redirectTo = '/admin';

    /**
     * Maksimal percobaan login sebelum dibatasi.
     */
    protected int $maxAttempts = 5;

    /**
     * Durasi pembatasan dalam detik.
     */
    protected int $decaySeconds = 60;

    /**
     * Tampilkan halaman login admin.
     */
    public function showLoginForm()
    {
        $pageTitle = 'Admin Login';

        return view('admin.auth.login', compact('pageTitle'));
    }

    /**
     * Guard autentikasi admin.
     */
    protected function guard()
    {
        return Auth::guard('admin');
    }

    /**
     * Nama kolom login admin.
     */
    public function username(): string
    {
        return 'username';
    }

    /**
     * Proses login admin.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
        ]);

        // Pertahankan verifikasi CAPTCHA dari sistem sebelumnya.
        if (!verifyCaptcha()) {
            return back()
                ->withErrors([
                    'captcha' => 'Invalid captcha provided.',
                ])
                ->withInput($request->only('username'));
        }

        $username = Str::lower($credentials['username']);

        $key = Str::transliterate(
            $username . '|' . $request->ip()
        );

        // Cegah percobaan login berulang.
        if (RateLimiter::tooManyAttempts($key, $this->maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);

            return back()
                ->withErrors([
                    'username' => "Terlalu banyak percobaan login. Coba lagi dalam {$seconds} detik.",
                ])
                ->withInput($request->only('username'));
        }

        // Gunakan guard admin, bukan guard pelanggan.
        $authenticated = $this->guard()->attempt([
            'username' => $credentials['username'],
            'password' => $credentials['password'],
        ]);

        if (!$authenticated) {
            RateLimiter::hit($key, $this->decaySeconds);

            return back()
                ->withErrors([
                    'username' => 'Username atau password salah.',
                ])
                ->withInput($request->only('username'));
        }

        // Login berhasil: hapus hitungan percobaan dan regenerasi session.
        RateLimiter::clear($key);
        $request->session()->regenerate();

        // Hanya izinkan redirect relatif menuju area admin.
        $redirect = $request->input('redirect');

        if (
            is_string($redirect) &&
            $redirect !== '' &&
            !str_starts_with($redirect, '//') &&
            !str_contains($redirect, '://')
        ) {
            $redirect = ltrim($redirect, '/');

            if (
                str_starts_with($redirect, 'admin/') &&
                !str_contains($redirect, '..')
            ) {
                return redirect('/' . $redirect);
            }
        }

        return redirect($this->redirectTo);
    }

    /**
     * Logout admin.
     */
    public function logout(Request $request)
    {
        $this->guard()->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/admin');
    }
}
