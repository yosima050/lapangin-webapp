<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $defaultRoute = route('dashboard', absolute: false);

        // Cegah redirect loop jika intended URL mengarah ke auth routes atau role tidak sesuai
        $intended = $request->session()->get('url.intended');
        if ($intended) {
            $parsedPath = parse_url($intended, PHP_URL_PATH) ?? '';
            $isAuthPath = str_contains($parsedPath, '/login')
                || str_contains($parsedPath, '/register')
                || str_contains($parsedPath, '/logout')
                || str_contains($parsedPath, '/password');

            $isAdminPath = str_starts_with($parsedPath, '/admin');
            $isKasirPath = str_starts_with($parsedPath, '/kasir');

            $user = Auth::user();
            $roleMismatch = ($isAdminPath && $user?->role !== 'admin')
                || ($isKasirPath && $user?->role !== 'kasir');

            if ($isAuthPath || $roleMismatch) {
                $request->session()->forget('url.intended');
                return redirect()->to($defaultRoute);
            }
        }

        return redirect()->intended($defaultRoute);
    }

    public function destroy(Request $request): RedirectResponse
    {
        if (Auth::guard('web')->check()) {
            Auth::guard('web')->logout();
        }

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}