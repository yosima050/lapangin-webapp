<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\RegisterOtpMail;
use App\Models\Pelanggan;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     * Validasi data registrasi, simpan ke sesi sementara, dan kirim OTP via email.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . Pelanggan::class],
            'no_hp' => ['required', 'string', 'max:15'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Email akun wajib diisi.',
            'email.email' => 'Format alamat email tidak valid.',
            'email.unique' => 'Alamat email ini sudah terdaftar. Silakan masuk atau gunakan email lain.',
            'no_hp.required' => 'Nomor WhatsApp / HP wajib diisi.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
            'password.min' => 'Kata sandi minimal harus 8 karakter.',
            'password' => 'Kata sandi minimal harus 8 karakter.',
        ]);

        // Rate limiting anti-spam pendaftaran dari IP / email
        $throttleKey = 'register-attempt:' . $request->ip() . '|' . strtolower((string) $request->input('email'));
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'email' => "Terlalu banyak permintaan pendaftaran. Silakan coba kembali dalam {$seconds} detik.",
            ]);
        }
        RateLimiter::hit($throttleKey, 300);

        // Generate kode OTP 6 digit
        $otp = (string) random_int(100000, 999999);

        // Simpan data pendaftaran sementara ke session (5 menit)
        $request->session()->put('register_data', [
            'name' => $request->input('name'),
            'email' => strtolower((string) $request->input('email')),
            'no_hp' => $request->input('no_hp'),
            'password' => $request->password,
            'otp' => $otp,
            'expires_at' => Carbon::now()->addMinutes(5)->toIso8601String(),
        ]);

        // Kirim OTP via email
        try {
            Mail::to($request->input('email'))->send(new RegisterOtpMail($otp, $request->input('name')));
        } catch (\Throwable $e) {
            report($e);
            return back()->withInput()->withErrors([
                'email' => 'Gagal mengirim email verifikasi. Pastikan konfigurasi email sudah benar.',
            ]);
        }

        return redirect()->route('register.otp.show')
            ->with('status', 'Kode verifikasi telah dikirimkan ke email Anda.');
    }

    /**
     * Pengecekan real-time ketersediaan email tanpa reload halaman.
     */
    public function checkEmail(Request $request): \Illuminate\Http\JsonResponse
    {
        $email = strtolower(trim((string) $request->input('email')));

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json([
                'available' => false,
                'message' => 'Format alamat email tidak valid.',
            ]);
        }

        $exists = Pelanggan::where('email', $email)->exists();

        return response()->json([
            'available' => ! $exists,
            'message' => $exists
                ? 'Alamat email ini sudah terdaftar. Silakan masuk atau gunakan email lain.'
                : 'Email dapat digunakan.',
        ]);
    }
}
