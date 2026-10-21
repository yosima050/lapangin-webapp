<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Pelanggan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class AuthLoginGoogle extends Controller
{
    public function google_redirect()
    {
        return Socialite::driver('google')
            ->with(['prompt' => 'select_account'])
            ->redirect();
    }

    public function google_callback(Request $request)
    {
        // Jika pengguna membatalkan proses di Google atau parameter code tidak ada
        if ($request->has('error') || ! $request->has('code')) {
            return redirect()->route('login')->with('status', 'Login dengan akun Google dibatalkan.');
        }

        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect()->route('login')->withErrors([
                'email' => 'Proses masuk dengan Google dibatalkan atau sesi telah kedaluwarsa. Silakan coba kembali.',
            ]);
        }

        // Cari pelanggan berdasarkan email
        $pelanggan = Pelanggan::where('email', $googleUser->getEmail())->first();

        // JIKA AKUN SUDAH TERDAFTAR:
        if ($pelanggan) {
            // Tandai email terverifikasi jika belum
            if (empty($pelanggan->email_verified_at)) {
                $pelanggan->email_verified_at = now();
                $pelanggan->save();
            }

            // Jika nomor HP masih kosong atau '-' (dari testing sebelumnya), minta lengkapi
            if (empty($pelanggan->no_hp) || $pelanggan->no_hp === '-') {
                session()->put('google_complete_user', [
                    'pelanggan_id' => $pelanggan->id,
                    'nama' => $pelanggan->nama,
                    'email' => $pelanggan->email,
                    'avatar' => $googleUser->getAvatar(),
                ]);

                return redirect()->route('auth.google.complete');
            }

            // Login dan redirect ke dashboard
            Auth::login($pelanggan, true);
            request()->session()->regenerate();

            return redirect()->intended(route('dashboard', absolute: false));
        }

        // JIKA AKUN BARU (Belum terdaftar di database):
        // Simpan data dari Google ke dalam session dan arahkan ke halaman pengisian No HP
        session()->put('google_complete_user', [
            'nama' => $googleUser->getName() ?? $googleUser->getNickname() ?? 'Pengguna Google',
            'email' => $googleUser->getEmail(),
            'avatar' => $googleUser->getAvatar(),
        ]);

        return redirect()->route('auth.google.complete');
    }

    /**
     * Tampilkan formulir untuk melengkapi Nomor HP pendaftaran Google SSO
     */
    public function completeForm()
    {
        $googleUser = session('google_complete_user');

        if (! $googleUser) {
            return redirect()->route('login');
        }

        return view('auth.google-complete', compact('googleUser'));
    }

    /**
     * Simpan data pengguna baru lengkap dengan Nomor HP
     */
    public function storeComplete(Request $request)
    {
        $googleData = session('google_complete_user');

        if (! $googleData) {
            return redirect()->route('login')->withErrors([
                'email' => 'Sesi pendaftaran Google telah kedaluwarsa. Silakan ulangi.',
            ]);
        }

        $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'no_hp' => ['required', 'string', 'min:8', 'max:15'],
        ], [
            'nama.required' => 'Nama lengkap wajib diisi.',
            'no_hp.required' => 'Nomor WhatsApp / HP wajib diisi.',
            'no_hp.min' => 'Nomor WhatsApp / HP minimal 8 digit.',
            'no_hp.max' => 'Nomor WhatsApp / HP maksimal 15 digit.',
        ]);

        // Bersihkan format nomor HP (hanya angka)
        $cleanPhone = preg_replace('/[^0-9]/', '', $request->no_hp);

        if (isset($googleData['pelanggan_id'])) {
            // Update akun yang sudah ada tapi belum punya nomor HP
            $pelanggan = Pelanggan::find($googleData['pelanggan_id']);
            if ($pelanggan) {
                $pelanggan->update([
                    'nama' => $request->nama,
                    'no_hp' => $cleanPhone,
                    'email_verified_at' => now(),
                ]);
            }
        } else {
            // Buat akun baru di tabel pelanggans
            $pelanggan = Pelanggan::create([
                'nama' => $request->nama,
                'email' => $googleData['email'],
                'no_hp' => $cleanPhone,
                'password_hash' => Hash::make(Str::random(24)),
                'role' => 'pelanggan',
                'email_verified_at' => now(),
            ]);
        }

        // Hapus session sementara Google
        session()->forget('google_complete_user');

        // Otomatis login ke aplikasi
        Auth::login($pelanggan, true);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
