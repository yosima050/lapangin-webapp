<?php

namespace Database\Seeders;

use App\Models\Lapangan;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // 1. Buat Akun Pegawai
        User::create([
            'nama' => 'Admin Lapangin',
            'email' => 'admin@lapangin.com',
            'no_hp' => '081234567890',
            'password_hash' => Hash::make('admin1'),
            'role' => 'admin',
            'created_at' => now(),
        ]);

        User::create([
            'nama' => 'Kasir Lapangin',
            'email' => 'kasir@lapangin.com',
            'no_hp' => '081234567891',
            'password_hash' => Hash::make('kasir1'),
            'role' => 'kasir',
            'created_at' => now(),
        ]);

        // 2. Buat Data 4 Venue Sesuai Desain Widi
        Lapangan::create([
            'nama' => 'Viva Futsal Arena Malang',
            'kategori' => 'Futsal',
            'jam_buka' => '08:00',
            'jam_tutup' => '23:00',
            'created_at' => now(),
        ]);

        Lapangan::create([
            'nama' => 'Badminton Smash Arena Malang',
            'kategori' => 'Badminton',
            'jam_buka' => '08:00',
            'jam_tutup' => '23:00',
            'created_at' => now(),
        ]);

        Lapangan::create([
            'nama' => 'Garuda Mini Soccer Stadium Malang',
            'kategori' => 'Mini Soccer',
            'jam_buka' => '06:00',
            'jam_tutup' => '23:00',
            'created_at' => now(),
        ]);

        Lapangan::create([
            'nama' => 'Supreme Futsal & Padel Hub Malang',
            'kategori' => 'Futsal',
            'jam_buka' => '08:00',
            'jam_tutup' => '24:00',
            'created_at' => now(),
        ]);
    }
}