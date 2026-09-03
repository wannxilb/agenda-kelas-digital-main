<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use App\Models\Setting;
use App\Models\User;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string',
            'password' => 'required|string',
        ]);

        $maxAttempts = (int) Setting::get('sec_max_login_attempts', 5);
        $lockoutMinutes = (int) Setting::get('sec_lockout_duration', 15);
        $throttleKey = Str::transliterate(Str::lower($request->input('email')).'|'.$request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'email' => ["Terlalu banyak percobaan login. Silakan coba lagi dalam {$seconds} detik."],
            ]);
        }

        $login = $request->input('email');
        $isEmailFormat = filter_var($login, FILTER_VALIDATE_EMAIL);
        
        $allowEmail = Setting::get('auth_login_email', '1') === '1';
        $allowUsername = Setting::get('auth_login_username', '1') === '1';

        $decaySeconds = $lockoutMinutes * 60;

        if ($isEmailFormat) {
            if (!$allowEmail) {
                RateLimiter::hit($throttleKey, $decaySeconds);
                throw ValidationException::withMessages(['email' => ['Login menggunakan email dinonaktifkan.']]);
            }
            $field = 'email';
        } else {
            if (!$allowUsername) {
                RateLimiter::hit($throttleKey, $decaySeconds);
                throw ValidationException::withMessages(['email' => ['Login menggunakan NIS/NIP dinonaktifkan.']]);
            }
            $field = 'nis';
        }

        $credentials = [
            $field => $login,
            'password' => $request->password,
            'status' => 'active',
        ];

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            /** @var User $user */
            $user = Auth::user();

            // Check if global login is disabled, allowing ONLY super_admin to login
            if (Setting::get('auth_allow_login', '1') !== '1' && !$user->hasRole('super_admin')) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                throw ValidationException::withMessages([
                    'email' => ['Sistem saat ini ditutup untuk login pengguna.'],
                ]);
            }
            
            if ($user->institution && $user->institution->status !== 'active') {
                $status = $user->institution->status;
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                $message = 'Akun instansi Anda sedang ditangguhkan.';
                if ($status === 'maintenance') $message = 'Instansi Anda sedang dalam masa pemeliharaan.';
                if ($status === 'expired') $message = 'Lisensi instansi Anda telah berakhir.';
                
                throw ValidationException::withMessages([
                    'email' => [$message],
                ]);
            }

            RateLimiter::clear($throttleKey);
            $request->session()->forget('url.intended');
            $request->session()->regenerate();

            // Periksa masa berlaku password
            $expiryDays = (int) Setting::get('sec_password_expiry_days', 0);
            if ($expiryDays > 0) {
                $changedAt = $user->password_changed_at ?? $user->created_at;
                if ($changedAt && $changedAt->addDays($expiryDays)->isPast()) {
                    $request->session()->flash('warning', 'Password Anda telah kedaluwarsa. Segera perbarui password melalui halaman profil.');
                }
            }

            // Redirect berdasarkan role
            if ($user->hasRole('super_admin')) {
                return redirect('/super-admin/dashboard');
            } elseif ($user->hasRole('admin')) {
                return redirect('/admin/dashboard');
            } elseif ($user->hasRole('wakasek')) {
                return redirect('/wakasek/dashboard');
            } elseif ($user->hasRole('wali_kelas')) {
                return redirect('/wali-kelas/dashboard');
            } elseif ($user->hasRole('teacher')) {
                return redirect('/guru/dashboard');
            } elseif ($user->hasRole('sekretaris')) {
                return redirect('/sekretaris/dashboard');
            } elseif ($user->hasRole('siswa')) {
                return redirect('/siswa/dashboard');
            }
            
            return redirect('/dashboard');
        }

        RateLimiter::hit($throttleKey, $decaySeconds);
        throw ValidationException::withMessages([
            'email' => ['Email atau password yang Anda masukkan salah.'],
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/login')->with('success', 'Anda berhasil logout.');
    }
}
