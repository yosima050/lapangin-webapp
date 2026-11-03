import time
import pytest
from playwright.sync_api import Page, expect

def test_precondition_via_antarmuka(page: Page, base_url: str):
    """Skenario UI: Harus login manual lewat form antarmuka (lambat & rentan)"""
    start_time = time.time()
    page.goto(f"{base_url}/login")
    page.locator("#login_email").fill("dimas.pratama@gmail.com")
    page.locator("#login_password").fill("password")
    page.get_by_role("button", name="Masuk Sekarang").click()
    page.wait_for_url("**/katalog**")
    durasi = time.time() - start_time
    print(f"\n[UI Klik] Waktu penyiapan: {durasi:.2f} detik")

def test_precondition_via_fixture(auth_pelanggan: Page):
    """Skenario Fixture: Kondisi awal siap pakai melalui fixture conftest"""
    start_time = time.time()
    # Menggunakan state terotentikasi langsung dari fixture
    expect(auth_pelanggan).to_have_url("http://127.0.0.1:8000/katalog")
    durasi = time.time() - start_time
    print(f"\n[Fixture] Waktu penyiapan: {durasi:.2f} detik")
