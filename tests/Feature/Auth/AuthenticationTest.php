<?php

namespace Tests\Feature\Auth;

use App\Models\Pelanggan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_pelanggans_can_authenticate_using_the_login_screen(): void
    {
        $pelanggan = Pelanggan::factory()->create();

        $response = $this->post('/login', [
            'email' => $pelanggan->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_pelanggans_can_not_authenticate_with_invalid_password(): void
    {
        $pelanggan = Pelanggan::factory()->create();

        $this->post('/login', [
            'email' => $pelanggan->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_pelanggans_can_logout(): void
    {
        $pelanggan = Pelanggan::factory()->create();

        $response = $this->actingAs($pelanggan)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }

    public function test_kasir_login_redirects_to_kasir_portal(): void
    {
        $kasir = Pelanggan::factory()->create([
            'role' => 'kasir',
            'password_hash' => bcrypt('kasir1'),
            'email_verified_at' => now(),
        ]);

        $response = $this->post('/login', [
            'email' => $kasir->email,
            'password' => 'kasir1',
        ]);

        $this->assertAuthenticatedAs($kasir);
        $response->assertRedirect(route('dashboard', absolute: false));

        // Follow redirect to /dashboard which forwards to /kasir
        $dashboardResponse = $this->actingAs($kasir)->get('/dashboard');
        $dashboardResponse->assertRedirect(route('kasir.index'));
    }

    public function test_admin_login_redirects_to_admin_portal(): void
    {
        $admin = Pelanggan::factory()->create([
            'role' => 'admin',
            'password_hash' => bcrypt('admin1'),
            'email_verified_at' => now(),
        ]);

        $response = $this->post('/login', [
            'email' => $admin->email,
            'password' => 'admin1',
        ]);

        $this->assertAuthenticatedAs($admin);
        $response->assertRedirect(route('dashboard', absolute: false));

        $dashboardResponse = $this->actingAs($admin)->get('/dashboard');
        $dashboardResponse->assertRedirect(route('admin.dashboard'));
    }

    public function test_unverified_user_can_see_logout_option_on_verify_screen(): void
    {
        $unverified = Pelanggan::factory()->create([
            'email_verified_at' => null,
        ]);

        $response = $this->actingAs($unverified)->get('/verify-email');
        $response->assertStatus(200);
        $response->assertSee(route('logout'));
        $response->assertSee('Keluar (Log Out)');
    }
}