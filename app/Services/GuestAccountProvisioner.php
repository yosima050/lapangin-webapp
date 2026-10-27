<?php

namespace App\Services;

use App\Models\Jadwal;
use App\Models\Pelanggan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class GuestAccountProvisioner
{
    /**
     * Normalisasi nomor telepon / WhatsApp ke format standar Indonesia (08...).
     */
    public static function normalizePhoneNumber(?string $phone): string
    {
        if (empty($phone)) {
            return '';
        }

        // Ambil hanya digit angka
        $digits = preg_replace('/[^0-9]/', '', $phone);

        // Jika diawali 628..., ubah menjadi 08...
        if (str_starts_with($digits, '628')) {
            return '08' . substr($digits, 3);
        }

        // Jika diawali 8... (tanpa 0 di depan), tambahkan 0
        if (str_starts_with($digits, '8')) {
            return '08' . substr($digits, 1);
        }

        return $digits;
    }

    /**
     * Dapatkan akun tamu yang sudah ada atau buat akun bayangan (Shadow Account) baru
     * berdasarkan nomor WhatsApp secara unik (firstOrCreate by no_hp).
     */
    public function getOrCreateGuestAccount(string $name, string $phone, ?string $email = null): Pelanggan
    {
        $normalizedPhone = self::normalizePhoneNumber($phone);
        $cleanName = trim($name) ?: 'Tamu Lapangan';

        // Variasi format nomor telepon untuk pencarian yang fleksibel
        $phoneVariants = array_unique(array_filter([
            $normalizedPhone,
            $phone,
            '62' . ltrim($normalizedPhone, '0'),
        ]));

        // 1. Cari apakah sudah ada record pelanggan dengan nomor ini
        $pelanggan = Pelanggan::whereIn('no_hp', $phoneVariants)->first();

        if ($pelanggan) {
            // Jika record ditemukan dan nama sebelumnya hanya placeholder default, perbarui namanya
            if (empty($pelanggan->nama) || str_starts_with($pelanggan->nama, 'Tamu')) {
                $pelanggan->update(['nama' => $cleanName]);
            }

            return $pelanggan;
        }

        // 2. Tentukan email unik untuk akun bayangan
        $guestEmail = $email;
        if (empty($guestEmail) || Pelanggan::where('email', $guestEmail)->exists()) {
            $baseGuestEmail = 'guest_' . $normalizedPhone . '@lapangin.local';
            $guestEmail = $baseGuestEmail;
            $counter = 1;

            while (Pelanggan::where('email', $guestEmail)->exists()) {
                $guestEmail = 'guest_' . $normalizedPhone . '_' . $counter . '@lapangin.local';
                $counter++;
            }
        }

        // 3. Buat shadow account baru
        return Pelanggan::create([
            'nama' => $cleanName,
            'email' => $guestEmail,
            'no_hp' => $normalizedPhone,
            'password_hash' => Hash::make(Str::random(32)),
            'role' => 'pelanggan',
            'email_verified_at' => null,
            'is_active' => true,
        ]);
    }

    /**
     * Sinkronkan / klaim seluruh riwayat reservasi dan e-tiket tamu lama
     * ke akun pengguna resmi yang baru aktif (Auto-Claim Order).
     */
    public function mergeOrdersToUser(Pelanggan $targetUser): int
    {
        $normalizedPhone = self::normalizePhoneNumber($targetUser->no_hp);

        if (empty($normalizedPhone)) {
            return 0;
        }

        $phoneVariants = array_unique(array_filter([
            $normalizedPhone,
            $targetUser->no_hp,
            '62' . ltrim($normalizedPhone, '0'),
        ]));

        return DB::transaction(function () use ($targetUser, $phoneVariants) {
            // Temukan ID akun bayangan lain yang memiliki nomor WhatsApp sama
            $shadowAccounts = Pelanggan::where('id', '!=', $targetUser->id)
                ->whereIn('no_hp', $phoneVariants)
                ->get();

            $shadowAccountIds = $shadowAccounts->pluck('id')->all();

            // Pindahkan seluruh jadwal (reservasi) ke akun targetUser
            $query = Jadwal::query()
                ->where(function ($q) use ($shadowAccountIds, $phoneVariants) {
                    if (! empty($shadowAccountIds)) {
                        $q->whereIn('pelanggan_id', $shadowAccountIds);
                    }
                    $q->orWhereIn('no_wa_tamu', $phoneVariants);
                })
                ->where(function ($q) use ($targetUser) {
                    $q->where('pelanggan_id', '!=', $targetUser->id)
                      ->orWhereNull('pelanggan_id');
                });

            $updatedCount = $query->update([
                'pelanggan_id' => $targetUser->id,
            ]);

            // Bersihkan akun bayangan sementara berformat guest agar data tetap rapi
            foreach ($shadowAccounts as $shadow) {
                if (str_starts_with($shadow->email, 'guest_') && str_ends_with($shadow->email, '@lapangin.local')) {
                    $shadow->delete();
                }
            }

            return $updatedCount;
        });
    }
}

