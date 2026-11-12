<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    /**
     * Update the user's password.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $isGoogleUser = session('login_provider') === 'google' || session('auth_provider') === 'google';

        $rules = [
            'password' => ['required', Password::defaults(), 'confirmed'],
        ];

        // Jika bukan login Google atau user mengisi kolom kata sandi lama, wajib divalidasi
        if (! $isGoogleUser || $request->filled('current_password')) {
            $rules['current_password'] = ['required', 'current_password'];
        }

        $validated = $request->validateWithBag('updatePassword', $rules, [
            'current_password.required' => 'Kata sandi saat ini wajib diisi.',
            'current_password.current_password' => 'Kata sandi saat ini tidak cocok dengan akun Anda.',
            'password.required' => 'Kata sandi baru wajib diisi.',
            'password.min' => 'Kata sandi baru minimal harus 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi baru tidak cocok.',
        ]);

        $user->update([
            'password_hash' => Hash::make($validated['password']),
        ]);

        return back()->with('status', 'password-updated');
    }
}
