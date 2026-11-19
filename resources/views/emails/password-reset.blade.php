<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atur Ulang Kata Sandi — LapangIn</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f3f6fc; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #1f2937;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #f3f6fc; padding: 40px 15px;">
        <tr>
            <td align="center">
                <!-- Main Container -->
                <table role="presentation" width="100%" style="max-width: 580px; background-color: #ffffff; border-radius: 20px; overflow: hidden; box-shadow: 0 10px 25px rgba(26, 20, 107, 0.06); border: 1px solid #e5eeff;" cellspacing="0" cellpadding="0">
                    <!-- Brand Header -->
                    <tr>
                        <td style="background-color: #1a146b; padding: 32px 40px; text-align: center;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 26px; font-weight: 900; letter-spacing: -0.5px;">
                                Lapang<span style="color: #acf847;">In</span>
                            </h1>
                            <p style="margin: 6px 0 0 0; color: #cbd5e1; font-size: 13px; font-weight: 500;">
                                Sistem Informasi Reservasi & Manajemen Fasilitas Olahraga
                            </p>
                        </td>
                    </tr>

                    <!-- Email Content -->
                    <tr>
                        <td style="padding: 40px 40px 30px 40px;">
                            <h2 style="margin: 0 0 16px 0; font-size: 20px; font-weight: 800; color: #0b1c30;">
                                Permintaan Atur Ulang Kata Sandi
                            </h2>

                            <p style="margin: 0 0 16px 0; font-size: 15px; line-height: 1.6; color: #4b5563;">
                                Halo, <strong style="color: #1a146b;">{{ $nama }}</strong>!
                            </p>

                            <p style="margin: 0 0 24px 0; font-size: 14px; line-height: 1.6; color: #4b5563;">
                                Kami menerima permintaan untuk mengatur ulang kata sandi akun LapangIn Anda. Untuk melanjutkan dan membuat kata sandi baru, silakan klik tombol di bawah ini:
                            </p>

                            <!-- CTA Button -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin: 28px 0;">
                                <tr>
                                    <td align="center">
                                        <a href="{{ $url }}" target="_blank" style="display: inline-block; background-color: #1a146b; color: #ffffff; text-decoration: none; font-size: 15px; font-weight: 700; padding: 14px 34px; border-radius: 12px; box-shadow: 0 4px 12px rgba(26, 20, 107, 0.25);">
                                            Atur Ulang Kata Sandi &rarr;
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <!-- Expiry Info Box -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #f8faff; border-left: 4px solid #1a146b; border-radius: 8px; margin: 24px 0;">
                                <tr>
                                    <td style="padding: 14px 18px;">
                                        <p style="margin: 0; font-size: 13px; color: #374151; line-height: 1.5;">
                                            ⏱️ <strong>Batas Waktu:</strong> Tautan pemulihan kata sandi ini hanya berlaku selama <strong>60 menit</strong> sejak email ini dikirimkan.
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin: 0 0 24px 0; font-size: 13px; line-height: 1.6; color: #6b7280;">
                                🛡️ <strong>Bukan Anda yang meminta?</strong><br>
                                Jika Anda tidak merasa melakukan permintaan ini, Anda dapat mengabaikan email ini dengan aman. Kata sandi akun Anda tidak akan berubah tanpa akses ke tautan di atas.
                            </p>

                            <hr style="border: 0; border-top: 1px solid #e5e7eb; margin: 30px 0 20px 0;">

                            <!-- Fallback Link -->
                            <p style="margin: 0; font-size: 12px; line-height: 1.5; color: #9ca3af;">
                                Jika tombol di atas tidak dapat diklik, salin dan tempel tautan berikut ke peramban (browser) Anda:<br>
                                <a href="{{ $url }}" style="color: #1a146b; word-break: break-all; text-decoration: underline;">{{ $url }}</a>
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f9fafb; padding: 24px 40px; text-align: center; border-top: 1px solid #f1f5f9;">
                            <p style="margin: 0 0 6px 0; font-size: 12px; color: #64748b;">
                                LapangIn Arena &bull; Layanan Pemesanan Lapangan Olahraga
                            </p>
                            <p style="margin: 0; font-size: 11px; color: #94a3b8;">
                                Email ini dikirim secara otomatis dari <a href="https://lapangin.biz.id" style="color: #64748b; text-decoration: underline;">https://lapangin.biz.id</a>. Mohon untuk tidak membalas langsung ke alamat email ini.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
