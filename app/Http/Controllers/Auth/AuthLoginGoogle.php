<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Pelanggan;
use App\Services\GuestAccountProvisioner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class AuthLoginGoogle extends Controller
{
    protected function getGoogleRedirectUrl(): string
    {
        $rawUrl = config('services.google.redirect')
            ?: (rtrim((string) config('app.url'), '/') . '/auth/google/callback');

        return preg_replace('#(?<!:)//+#', '/', (string) $rawUrl);
    }

    public function google_redirect()
    {
        return Socialite::driver('google')
            ->redirectUrl($this->getGoogleRedirectUrl())
            ->with(['prompt' => 'select_account'])
            ->redirect();
    }


    public function google_callback(Request $request)
    {
        if ($request->has('error') || ! $request->has('code')) {
            return redirect()->route('login');
        }

        try {
            $googleUser = Socialite::driver('google')
                ->redirectUrl($this->getGoogleRedirectUrl())
                ->user();
        } catch (\Exception $e) {
            return redirect()->route('login')->withErrors([
                'email' => 'Proses masuk dengan Google dibatalkan atau sesi telah kedaluwarsa. Silakan coba kembali.',
            ]);
        }

        $pelanggan = Pelanggan::where('email', $googleUser->getEmail())->first();

        // Jika akun sudah terdaftar
        if ($pelanggan) {
            // Tolak akun yang dinonaktifkan
            if (! $pelanggan->is_active) {
                return redirect()->route('login')->withErrors([
                    'email' => 'Akun Anda dinonaktifkan. Silakan hubungi administrator.',
                ]);
            }

            // Tandai email terverifikasi jika belum
            if (empty($pelanggan->email_verified_at)) {
                $pelanggan->email_verified_at = now();
                $pelanggan->save();
            }

            // Minta lengkapi nomor HP jika masih kosong
            if (empty($pelanggan->no_hp) || $pelanggan->no_hp === '-') {
                session()->put('google_complete_user', [
                    'pelanggan_id' => $pelanggan->id,
                    'nama' => $pelanggan->nama,
                    'email' => $pelanggan->email,
                    'avatar' => $googleUser->getAvatar(),
                ]);

                return redirect()->route('auth.google.complete');
            }

            Auth::login($pelanggan, true);
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard', absolute: false));
        }

        // Jika akun baru, arahkan ke formulir pelengkapan profil
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

        // Bersihkan & normalisasi format nomor HP
        $cleanPhone = GuestAccountProvisioner::normalizePhoneNumber($request->no_hp);

        if (isset($googleData['pelanggan_id'])) {
            $pelanggan = Pelanggan::find($googleData['pelanggan_id']);

            if (! $pelanggan || ! $pelanggan->is_active) {
                session()->forget('google_complete_user');

                return redirect()->route('login')->withErrors([
                    'email' => 'Akun tidak tersedia atau telah dinonaktifkan.',
                ]);
            }

            $pelanggan->update([
                'nama' => $request->nama,
                'no_hp' => $cleanPhone,
                'email_verified_at' => now(),
            ]);
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

        // Auto-claim order tamu yang pernah dibuat dengan nomor HP ini
        app(GuestAccountProvisioner::class)->mergeOrdersToUser($pelanggan);

        // Hapus session sementara Google
        session()->forget('google_complete_user');

        // Otomatis login ke aplikasi
        Auth::login($pelanggan, true);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
