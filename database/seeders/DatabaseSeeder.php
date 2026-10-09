<?php

namespace Database\Seeders;

use App\Models\HargaMaster;
use App\Models\Lapangan;
use App\Models\Pelanggan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Pelanggan::firstOrCreate(
            ['email' => 'admin@lapangin.com'],
            [
                'nama' => 'Admin Lapangin',
                'no_hp' => '081234567890',
                'password_hash' => Hash::make('admin1'),
                'role' => 'admin',
                'created_at' => now(),
            ]
        );

        Pelanggan::firstOrCreate(
            ['email' => 'kasir@lapangin.com'],
            [
                'nama' => 'Kasir Lapangin',
                'no_hp' => '081234567891',
                'password_hash' => Hash::make('kasir1'),
                'role' => 'kasir',
                'created_at' => now(),
            ]
        );

        $lapanganA = Lapangan::firstOrCreate(
            ['nama' => 'Lapangan Futsal A'],
            [
                'kategori' => 'Futsal',
                'jam_buka' => '08:00',
                'jam_tutup' => '22:00',
                'created_at' => now(),
            ]
        );

        $lapanganB = Lapangan::firstOrCreate(
            ['nama' => 'Lapangan Futsal B'],
            [
                'kategori' => 'Futsal',
                'jam_buka' => '08:00',
                'jam_tutup' => '22:00',
                'created_at' => now(),
            ]
        );

        $dataHarga = [
            [
                'lapangan_id' => $lapanganA->id,
                'jenis_hari' => 'weekday',
                'tanggal_khusus' => null,
                'jam_mulai' => '08:00',
                'jam_selesai' => '22:00',
                'harga' => 150000,
            ],
            [
                'lapangan_id' => $lapanganA->id,
                'jenis_hari' => 'weekend',
                'tanggal_khusus' => null,
                'jam_mulai' => '08:00',
                'jam_selesai' => '22:00',
                'harga' => 175000,
            ],
            [
                'lapangan_id' => $lapanganB->id,
                'jenis_hari' => 'weekday',
                'tanggal_khusus' => null,
                'jam_mulai' => '08:00',
                'jam_selesai' => '22:00',
                'harga' => 150000,
            ],
            [
                'lapangan_id' => $lapanganB->id,
                'jenis_hari' => 'weekend',
                'tanggal_khusus' => null,
                'jam_mulai' => '08:00',
                'jam_selesai' => '22:00',
                'harga' => 175000,
            ],
        ];

        foreach ($dataHarga as $harga) {
            HargaMaster::firstOrCreate(
                [
                    'lapangan_id' => $harga['lapangan_id'],
                    'jenis_hari' => $harga['jenis_hari'],
                    'tanggal_khusus' => $harga['tanggal_khusus'],
                    'jam_mulai' => $harga['jam_mulai'],
                    'jam_selesai' => $harga['jam_selesai'],
                ],
                [
                    'harga' => $harga['harga'],
                ]
            );
        }
    }
}