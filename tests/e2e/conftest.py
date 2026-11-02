import pytest
from playwright.sync_api import sync_playwright

BASE_URL = "http://127.0.0.1:8000"

@pytest.fixture(scope="session")
def base_url():
    """Alamat basis web aplikasi LapangIn lokal"""
    return BASE_URL

@pytest.fixture
def auth_kasir(page, base_url):
    """
    Precondition: Login langsung sebagai Kasir LapangIn
    Akun terdaftar dari DatabaseSeeder: kasir@lapangin.com / kasir1
    """
    page.goto(f"{base_url}/login")
    page.locator("#login_email").fill("kasir@lapangin.com")
    page.locator("#login_password").fill("kasir1")
    page.get_by_role("button", name="Masuk Sekarang").click()
    page.wait_for_url("**/kasir**")
    yield page

@pytest.fixture
def auth_pelanggan(page, base_url):
    """
    Precondition: Login langsung sebagai Pelanggan LapangIn
    Akun terdaftar dari DatabaseSeeder: dimas.pratama@gmail.com / password
    """
    page.goto(f"{base_url}/login")
    page.locator("#login_email").fill("dimas.pratama@gmail.com")
    page.locator("#login_password").fill("password")
    page.get_by_role("button", name="Masuk Sekarang").click()
    page.wait_for_url("**/katalog**")
    yield page

