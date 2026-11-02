import subprocess
import re

from playwright.sync_api import Page, expect


TIKET_POSITIF = "LPGN-3390"
TIKET_NEGATIF = "LPGN-1102"
KODE_QR_POSITIF = "LPGN-20250513-3390"


def siapkan_tiket(kode: str, sudah_checkin: bool) -> None:
    """
    Menyiapkan tiket pada database lapangin_test.
    Tidak mengubah database utama.
    """
    if sudah_checkin:
        data = (
            "['checked_in_at' => now(), "
            "'status' => 'terverifikasi']"
        )
    else:
        data = (
            "['checked_in_at' => null, "
            "'status' => 'lunas', "
            "'kasir_id' => null]"
        )

    perintah = (
        "\\App\\Models\\Jadwal::where('token_manual', "
        f"'{kode}')->update({data});"
    )

    subprocess.run(
        [
            "php",
            "artisan",
            "tinker",
            "--env=testing",
            f"--execute={perintah}",
        ],
        check=True,
        capture_output=True,
        text=True,
    )


def reset_tiket_checkin(kode: str) -> None:
    """
    Simulasi perubahan check-in yang tidak bertahan
    pada database testing.
    """
    perintah = (
        "\\App\\Models\\Jadwal::where('token_manual', "
        f"'{kode}')->update(["
        "'checked_in_at' => null, "
        "'status' => 'lunas', "
        "'kasir_id' => null"
        "]);"
    )

    subprocess.run(
        [
            "php",
            "artisan",
            "tinker",
            "--env=testing",
            f"--execute={perintah}",
        ],
        check=True,
        capture_output=True,
        text=True,
    )


def test_checkin_positif_dan_anti_duplicate(
    auth_kasir: Page,
    base_url: str,
):
    # Persiapan tiket valid yang belum check-in.
    siapkan_tiket(TIKET_POSITIF, sudah_checkin=False)

    page = auth_kasir
    page.add_init_script("window.print = () => {};")
    page.goto(f"{base_url}/kasir/checkin")

    # Lapisan 1: UI menampilkan bukti check-in berhasil.
    page.locator("#barcode-input").fill(TIKET_POSITIF)
    page.get_by_test_id("checkin-submit").click()

    expect(page).to_have_url(re.compile(r"/struk/"))
    expect(page.get_by_text(KODE_QR_POSITIF)).to_be_visible()
    expect(page.get_by_text("LUNAS - VALID")).to_be_visible()

    # Lapisan 2: kembali ke scanner dan reload.
    page.get_by_role("link", name="Scanner").click()
    page.reload()

    expect(page.get_by_text("Checked In").first).to_be_visible()
    expect(page.get_by_text(KODE_QR_POSITIF)).to_be_visible()

    # Lapisan 3: kirim tiket yang sama untuk kedua kalinya.
    page.locator("#barcode-input").fill(TIKET_POSITIF)
    page.get_by_test_id("checkin-submit").click()

    expect(
        page.get_by_text("Peringatan / Validasi Gagal")
    ).to_be_visible()

    expect(
        page.get_by_text("Tiket ini sudah pernah digunakan")
    ).to_be_visible()


def test_checkin_negatif_tiket_sudah_digunakan(
    auth_kasir: Page,
    base_url: str,
):
    # Persiapan tiket yang sudah pernah check-in.
    siapkan_tiket(TIKET_NEGATIF, sudah_checkin=True)

    page = auth_kasir
    page.goto(f"{base_url}/kasir/checkin")

    # Kirim tiket yang sudah digunakan.
    page.locator("#barcode-input").fill(TIKET_NEGATIF)
    page.get_by_test_id("checkin-submit").click()

    # Pastikan sistem menolak tiket.
    expect(
        page.get_by_text("Peringatan / Validasi Gagal")
    ).to_be_visible()

    expect(
        page.get_by_text("Tiket ini sudah pernah digunakan")
    ).to_be_visible()


def test_eksperimen_tanpa_reload_ui_saja_bisa_menyesatkan(
    auth_kasir: Page,
    base_url: str,
):
    # Siapkan tiket yang belum check-in.
    siapkan_tiket(TIKET_POSITIF, sudah_checkin=False)

    page = auth_kasir
    page.add_init_script("window.print = () => {};")
    page.goto(f"{base_url}/kasir/checkin")

    # Eksperimen: hanya memeriksa UI tanpa reload.
    page.locator("#barcode-input").fill(TIKET_POSITIF)
    page.get_by_test_id("checkin-submit").click()

    expect(page).to_have_url(re.compile(r"/struk/"))
    expect(page.get_by_text(KODE_QR_POSITIF)).to_be_visible()
    expect(page.get_by_text("LUNAS - VALID")).to_be_visible()

    # Simulasikan kondisi perubahan database tidak bertahan.
    # Halaman struk tetap terbuka tanpa reload.
    reset_tiket_checkin(TIKET_POSITIF)

    # UI lama masih terlihat sukses.
    expect(page.get_by_text("LUNAS - VALID")).to_be_visible()

    # Periksa ulang data melalui halaman scanner.
    page.get_by_role("link", name="Scanner").click()
    page.reload()

    # Ambil bagian daftar check-in terbaru.
    bagian_checkin = page.locator("section").filter(
        has=page.get_by_role(
            "heading",
            name="Baru Saja Check-In (Hari Ini)"
        )
    )

    # Tiket tidak lagi tercatat sebagai sudah check-in.
    expect(
        bagian_checkin.get_by_text(KODE_QR_POSITIF)
    ).to_have_count(0)

   # Pastikan tiket tidak lagi tercatat sebagai check-in.
    expect(
        bagian_checkin.get_by_text(KODE_QR_POSITIF)
    ).to_have_count(0)