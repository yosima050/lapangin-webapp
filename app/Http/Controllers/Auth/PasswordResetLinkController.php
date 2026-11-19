<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        try {
            // Send the password reset link using the configured Pelanggan broker
            $status = Password::broker('Pelanggan')->sendResetLink(
                $request->only('email')
            );
        } catch (\Throwable $e) {
            Log::error('Password reset email failed: ' . $e->getMessage(), [
                'email' => $request->input('email'),
                'exception' => $e,
            ]);

            return back()->withInput($request->only('email'))
                ->withErrors(['email' => 'Gagal mengirim email reset kata sandi. Pastikan konfigurasi mail server sudah aktif atau hubungi administrator.']);
        }

        return $status == Password::RESET_LINK_SENT
                    ? back()->with('status', __($status))
                    : back()->withInput($request->only('email'))
                        ->withErrors(['email' => __($status)]);
    }
}
