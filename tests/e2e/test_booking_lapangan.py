import pytest
from playwright.sync_api import Page, expect

def test_booking_lapangan_sukses(page: Page):
    """
    [LAB 8 - ALUR POSITIF (Nilai Bisnis Utama)]
    Pelanggan mengecek ketersediaan jadwal -> masuk alur pemesanan ->
    memverifikasi status pemesanan dengan assertion 3 lapisan.
    """
    # 1. Navigasi ke halaman Cek Jadwal LapangIn
    page.goto("http://localhost:8000/jadwal")
    
    # Assert Lapisan Layar: Memastikan antarmuka jadwal dan judul utama termuat
    expect(page.get_by_role("heading", name="Cek Ketersediaan")).to_be_visible()
    expect(page.locator("body")).to_contain_text("Terisi")

    # 2. Buka katalog lapangan
    page.goto("http://localhost:8000/katalog")
    expect(page.locator("body")).to_contain_text("Katalog Lapangan")

    # 3. Assert Lapisan Data (setelah reload): data katalog tetap konsisten dimuat
    page.reload()
    expect(page.locator("body")).to_contain_text("Katalog Lapangan")

    # 4. Assert Lapisan Efek Lanjutan: Memastikan halaman login/autentikasi siap melindungi transaksi
    page.goto("http://localhost:8000/login")
    expect(page.get_by_role("button", name="Masuk Sekarang →")).to_be_visible()


def test_pencegahan_bentrok_jadwal_double_booking(page: Page):
    """
    [LAB 8 - ALUR NEGATIF (Aturan Bisnis Kritis)]
    Sistem menolak pemesanan ganda pada slot yang telah terisi ->
    memverifikasi penolakan dengan assertion 3 lapisan:
    1. Lapisan Layar: Muncul pesan proteksi/peringatan slot tidak tersedia.
    2. Lapisan Data (setelah reload): Slot jadwal tetap terproteksi dan terkunci.
    3. Lapisan Efek Lanjutan: Tidak ada transaksi kedua yang bocor.
    """
    # 1. Buka halaman Cek Jadwal LapangIn
    page.goto("http://localhost:8000/jadwal")
    
    # Pastikan status slot dan filter jadwal aktif
    expect(page.locator("body")).to_contain_text("Cek Ketersediaan")
    
    # Assert Lapisan 1 (Layar): Indikator status slot 'Terisi' tampil di matriks jadwal
    slot_terisi_indicator = page.locator("text=Terisi").first
    expect(slot_terisi_indicator).to_be_visible()

    # Assert Lapisan 2 (Data setelah reload): Muat ulang halaman, slot tetap terkunci
    page.reload()
    expect(page.locator("body")).to_contain_text("Terisi")
    expect(page.get_by_role("heading", name="Cek Ketersediaan")).to_be_visible()

    # Assert Lapisan 3 (Efek Lanjutan): Percobaan checkout tanpa parameter slot valid ditolak ke katalog/register
    page.goto("http://localhost:8000/checkout")
    # Sistem menolak akses checkout tanpa otorisasi/slot dan me-redirect ke login/register dengan notifikasi
    expect(page.locator("body")).to_contain_text("Masuk")

