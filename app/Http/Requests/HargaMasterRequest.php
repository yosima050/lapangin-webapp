<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class HargaMasterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'lapangan_id' => ['required', 'uuid', 'exists:lapangans,id'],
            'jenis_hari' => ['required', 'in:weekday,weekend'],
            'tanggal_khusus' => ['nullable', 'date'],
            'jam_mulai' => ['required', 'date_format:H:i'],
            'jam_selesai' => ['required', 'date_format:H:i'],
            'harga' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'lapangan_id.required' => 'Lapangan wajib dipilih.',
            'lapangan_id.exists' => 'Lapangan tidak ditemukan di database.',
            'jenis_hari.required' => 'Jenis hari wajib dipilih.',
            'jenis_hari.in' => 'Jenis hari harus berupa weekday atau weekend.',
            'jam_mulai.required' => 'Jam mulai wajib diisi.',
            'jam_selesai.required' => 'Jam selesai wajib diisi.',
            'harga.required' => 'Tarif harga wajib diisi.',
            'harga.numeric' => 'Tarif harga harus berupa angka.',
            'harga.min' => 'Tarif harga tidak boleh bernilai minus.',
        ];
    }
}
