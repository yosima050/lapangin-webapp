@php
    $logoPath = public_path('images/Logo_revisi.png');
    $logoSrc = (isset($message) && file_exists($logoPath))
        ? $message->embed($logoPath)
        : asset('images/Logo_revisi.png');
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kode Verifikasi Registrasi LapangIn</title>
</head>
<body style="margin:0; padding:0; background-color:#f4f7ff; font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color:#0b1c30;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#f4f7ff; padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:520px; background-color:#ffffff; border-radius:20px; overflow:hidden; border:1px solid #e2e8f0; box-shadow:0 12px 30px rgba(11,28,48,0.06);">
                    {{-- Accent Top Bar --}}
                    <tr>
                        <td style="height:6px; background:linear-gradient(90deg, #1a146b 0%, #416900 65%, #acf847 100%); font-size:0; line-height:0;">&nbsp;</td>
                    </tr>

                    {{-- Header Logo --}}
                    <tr>
                        <td align="center" style="padding:28px 32px 16px 32px; border-bottom:1px solid #f1f5f9;">
                            <img src="{{ $logoSrc }}" alt="LapangIn" height="42" style="height:42px; width:auto; display:block; border:0;">
                        </td>
                    </tr>

                    {{-- Body Content --}}
                    <tr>
                        <td style="padding:28px 32px 24px 32px;">
                            <h1 style="margin:0 0 10px 0; font-size:20px; line-height:1.3; font-weight:800; color:#0b1c30;">
                                Halo, {{ $nama }}!
                            </h1>
                            <p style="margin:0 0 22px 0; font-size:14px; line-height:1.65; color:#475569;">
                                Terima kasih telah mendaftar di <strong style="color:#1a146b;">LapangIn</strong>. Masukkan 6 digit kode verifikasi (OTP) berikut di halaman pendaftaran untuk mengaktifkan akun Anda:
                            </p>

                            {{-- OTP Code Box --}}
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin:0 0 22px 0;">
                                <tr>
                                    <td align="center" style="background-color:#f4f7ff; border:2px dashed #1a146b; border-radius:16px; padding:22px 16px;">
                                        <div style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:1.6px; color:#64748b; margin-bottom:8px;">
                                            Kode Verifikasi OTP
                                        </div>
                                        <div style="font-family:'Courier New', Courier, monospace; font-size:36px; line-height:1.1; font-weight:900; letter-spacing:10px; color:#1a146b; padding-left:10px;">
                                            {{ $otp }}
                                        </div>
                                    </td>
                                </tr>
                            </table>

                            {{-- Expiry & Security Note --}}
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin:0 0 20px 0;">
                                <tr>
                                    <td style="background-color:#f4fbe6; border:1px solid #d7f0a6; border-radius:12px; padding:12px 14px; font-size:12px; line-height:1.55; color:#284200;">
                                        <strong>Berlaku selama 5 menit.</strong> Demi keamanan akun Anda, jangan bagikan kode ini kepada siapa pun termasuk pihak yang mengaku dari LapangIn.
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:0; font-size:12px; line-height:1.6; color:#64748b;">
                                Jika Anda tidak merasa mendaftar di LapangIn, cukup abaikan email ini.
                            </p>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td align="center" style="background-color:#f8faff; border-top:1px solid #f1f5f9; padding:18px 24px; font-size:11px; line-height:1.6; color:#64748b;">
                            <strong style="color:#1a146b;">LapangIn</strong> &middot; Platform Reservasi Lapangan Olahraga<br>
                            &copy; {{ date('Y') }} {{ config('app.name') }}. Seluruh hak cipta dilindungi.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
