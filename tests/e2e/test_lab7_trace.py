from playwright.sync_api import Page, expect

def test_kegagalan_buatan_untuk_trace(page: Page):
    # Membuka halaman login LapangIn
    page.goto("http://localhost:8000/login")
    
    # Assertion yang BENAR: Tombol 'Masuk Sekarang →' ada di halaman login
    expect(page.get_by_role("button", name="Masuk Sekarang →")).to_be_visible()
