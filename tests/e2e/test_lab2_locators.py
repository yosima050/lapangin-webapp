import re
import time
from playwright.sync_api import Page, expect

def login_kasir(page: Page):
    page.goto("http://127.0.0.1:8000/login")
    page.locator("#login_email").fill("kasir@lapangin.com")
    page.locator("#login_password").fill("kasir1")
    page.get_by_role("button", name="Masuk Sekarang").click()
    page.wait_for_load_state("networkidle")

def test_locator_checkin(page: Page):
    start = time.time()
    login_kasir(page)
    page.goto("http://127.0.0.1:8000/kasir/checkin")
    expect(page).to_have_url(re.compile(r"/kasir/checkin"))
    expect(page.get_by_test_id("checkin-submit")).to_be_visible()
    durasi = round(time.time() - start, 2)
    print(f"\n[LAB 2 CHECKIN] Locator Valid | Durasi: {durasi} detik")

def test_locator_rekonsiliasi_kasir(page: Page):
    start = time.time()
    login_kasir(page)
    page.goto("http://127.0.0.1:8000/kasir/transaksi")
    cash_input = page.get_by_test_id("cash-actual-input")
    expect(cash_input).to_be_visible()
    expect(cash_input).to_be_editable()
    close_button = page.get_by_test_id("shift-close-button")
    expect(close_button).to_be_visible()
    durasi = round(time.time() - start, 2)
    print(f"\n[LAB 2 REKONSILIASI] Locator Valid | Durasi: {durasi} detik")

def test_locator_katalog(page: Page):
    start = time.time()
    page.goto("http://127.0.0.1:8000/katalog")
    expect(page.get_by_test_id("katalog-sort-select")).to_be_visible()
    durasi = round(time.time() - start, 2)
    print(f"\n[LAB 2 KATALOG] Locator Valid | Durasi: {durasi} detik")