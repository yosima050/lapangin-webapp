<x-mail::message>
# Halo, {{ $nama }}!

Terima kasih telah mendaftar di **LapangIn**. Untuk menyelesaikan pendaftaran dan mengamankan akun Anda, silakan masukkan kode verifikasi (OTP) berikut:

<x-mail::panel>
<div style="font-size: 32px; font-weight: bold; letter-spacing: 8px; text-align: center; color: #1a146b; padding: 10px 0;">
{{ $otp }}
</div>
</x-mail::panel>

Kode ini berlaku selama **5 menit**. Jangan berikan kode ini kepada siapapun demi keamanan akun Anda.

Jika Anda tidak merasa mendaftar di LapangIn, silakan abaikan email ini.

Salam hangat,<br>
Tim **{{ config('app.name') }}**
</x-mail::message>
