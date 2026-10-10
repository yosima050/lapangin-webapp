<?php

namespace App\Http\Requests;

use App\Models\Pelanggan;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(Pelanggan::class)->ignore($this->user()->id),
            ],
        ];
    }

    protected function passedValidation(): void
    {
        $this->merge([
            'nama' => $this->input('name'),
        ]);
    }
}