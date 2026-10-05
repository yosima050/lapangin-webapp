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

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        
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

        Lapangan::create([
            'nama' => 'Lapangan Futsal A',
            'kategori' => 'Futsal',
            'jam_buka' => '08:00',
            'jam_tutup' => '22:00',
            'created_at' => now(),
        ]);

        Lapangan::create([
            'nama' => 'Lapangan Futsal B',
            'kategori' => 'Futsal',
            'jam_buka' => '08:00',
            'jam_tutup' => '22:00',
            'created_at' => now(),
        ]);
    }
}