<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLapanganRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:255'],
            'kategori' => ['required', 'string', 'max:100'],
            'jam_buka' => ['required', 'date_format:H:i'],
            'jam_tutup' => ['required', 'date_format:H:i'],
            'deskripsi' => ['nullable', 'string'],
            'foto' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:3072'],
            'harga_weekday' => ['nullable', 'numeric', 'min:0'],
            'harga_weekend' => ['nullable', 'numeric', 'min:0'],
            'harga_tanggal_merah' => ['nullable', 'numeric', 'min:0'],
            'tanggal_khusus' => ['nullable', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'nama.required' => 'Nama lapangan wajib diisi.',
            'kategori.required' => 'Kategori lapangan wajib diisi.',
            'jam_buka.required' => 'Jam buka operasional wajib diisi.',
            'jam_tutup.required' => 'Jam tutup operasional wajib diisi.',
            'foto.image' => 'File foto harus berupa gambar.',
            'foto.mimes' => 'Format foto harus jpeg, png, jpg, atau webp.',
            'foto.max' => 'Ukuran file foto maksimal 3MB.',
            'harga_weekday.numeric' => 'Tarif dasar weekday harus berupa angka.',
            'harga_weekday.min' => 'Tarif dasar weekday tidak boleh bernilai minus.',
            'harga_weekend.numeric' => 'Tarif dasar weekend harus berupa angka.',
            'harga_weekend.min' => 'Tarif dasar weekend tidak boleh bernilai minus.',
            'harga_tanggal_merah.numeric' => 'Tarif khusus tanggal merah harus berupa angka.',
            'harga_tanggal_merah.min' => 'Tarif khusus tanggal merah tidak boleh bernilai minus.',
            'tanggal_khusus.date' => 'Format tanggal merah / tanggal khusus tidak valid.',
        ];
    }
}
