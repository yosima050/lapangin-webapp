<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\RegisterOtpMail;
use App\Models\Pelanggan;
use App\Services\GuestAccountProvisioner;
use Carbon\Carbon;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisterOtpController extends Controller
{
    /**
     * Tampilkan halaman input OTP.
     */
    public function show(Request $request): View|RedirectResponse
    {
        $regData = $request->session()->get('register_data');

        if (! $regData) {
            return redirect()->route('register')->with('status', 'Silakan isi form pendaftaran terlebih dahulu.');
        }

        $expiresAt = Carbon::parse($regData['expires_at']);
        $remainingSeconds = max(0, Carbon::now()->diffInSeconds($expiresAt, false));

        if ($remainingSeconds <= 0) {
            return view('auth.verify-otp', [
                'email' => $regData['email'],
                'expired' => true,
                'remainingSeconds' => 0,
            ]);
        }

        return view('auth.verify-otp', [
            'email' => $regData['email'],
            'expired' => false,
            'remainingSeconds' => $remainingSeconds,
        ]);
    }

    /**
     * Verifikasi kode OTP.
     */
    public function verify(Request $request): RedirectResponse
    {
        $regData = $request->session()->get('register_data');

        if (! $regData) {
            return redirect()->route('register')->withErrors(['email' => 'Sesi pendaftaran telah berakhir. Silakan daftar kembali.']);
        }

        $request->validate([
            'otp' => ['required', 'string', 'size:6'],
        ]);

        $throttleKey = 'verify-otp:' . $request->ip() . '|' . $regData['email'];

        // Maksimal 5x percobaan salah untuk anti-bruteforce
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'otp' => "Terlalu banyak percobaan salah. Silakan coba lagi dalam {$seconds} detik.",
            ]);
        }

        // Cek kedaluwarsa OTP (5 menit)
        if (Carbon::now()->isAfter(Carbon::parse($regData['expires_at']))) {
            throw ValidationException::withMessages([
                'otp' => 'Kode OTP telah kedaluwarsa. Silakan klik "Kirim Ulang OTP".',
            ]);
        }

        // Cek kecocokan OTP
        if (! hash_equals((string) $regData['otp'], (string) trim($request->input('otp')))) {
            RateLimiter::hit($throttleKey, 300);
            throw ValidationException::withMessages([
                'otp' => 'Kode OTP yang Anda masukkan salah.',
            ]);
        }

        // OTP valid, bersihkan rate limiter
        RateLimiter::clear($throttleKey);

        // Pastikan email belum terdaftar (jika ada pendaftaran paralel)
        if (Pelanggan::where('email', $regData['email'])->exists()) {
            $request->session()->forget('register_data');
            return redirect()->route('login')->withErrors(['email' => 'Email ini sudah terdaftar. Silakan login.']);
        }

        // Simpan Pelanggan ke database
        $pelanggan = Pelanggan::create([
            'nama' => $regData['name'],
            'email' => $regData['email'],
            'no_hp' => GuestAccountProvisioner::normalizePhoneNumber($regData['no_hp']),
            'password_hash' => Hash::make($regData['password']),
            'role' => 'pelanggan',
            'email_verified_at' => Carbon::now(),
        ]);

        // Hapus data sesi pendaftaran
        $request->session()->forget('register_data');

        event(new Registered($pelanggan));

        Auth::login($pelanggan);

        return redirect()->intended(route('dashboard', absolute: false))
            ->with('status', 'Pendaftaran berhasil! Selamat datang di LapangIn.');
    }

    /**
     * Kirim ulang kode OTP dengan proteksi cooldown.
     */
    public function resend(Request $request): RedirectResponse
    {
        $regData = $request->session()->get('register_data');

        if (! $regData) {
            return redirect()->route('register')->withErrors(['email' => 'Sesi pendaftaran telah berakhir. Silakan daftar kembali.']);
        }

        $resendThrottleKey = 'resend-otp:' . $request->ip() . '|' . $regData['email'];

        // Cooldown 60 detik sebelum bisa request kirim ulang
        if (RateLimiter::tooManyAttempts($resendThrottleKey, 1)) {
            $seconds = RateLimiter::availableIn($resendThrottleKey);
            return back()->withErrors([
                'otp' => "Mohon tunggu {$seconds} detik sebelum meminta kode OTP baru.",
            ]);
        }

        RateLimiter::hit($resendThrottleKey, 60);

        // Generate OTP baru 6 digit
        $newOtp = (string) random_int(100000, 999999);
        $regData['otp'] = $newOtp;
        $regData['expires_at'] = Carbon::now()->addMinutes(5)->toIso8601String();

        $request->session()->put('register_data', $regData);

        // Kirim email OTP
        try {
            Mail::to($regData['email'])->send(new RegisterOtpMail($newOtp, $regData['name']));
        } catch (\Throwable $e) {
            report($e);
            return back()->withErrors(['otp' => 'Gagal mengirim email. Silakan coba sesaat lagi.']);
        }

        return back()->with('status', 'Kode OTP baru telah dikirimkan ke email Anda.');
    }
}
