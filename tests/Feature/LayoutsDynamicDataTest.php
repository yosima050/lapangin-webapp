<?php

namespace Tests\Feature;

use App\Models\Pelanggan;
use App\Models\Reschedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LayoutsDynamicDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_layout_renders_dynamic_data_and_valid_routes(): void
    {
        $admin = Pelanggan::factory()->create([
            'role' => 'admin',
            'nama' => 'Joko Admin Dinamis',
            'email' => 'joko.admin@lapangin.id',
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Joko Admin Dinamis');
        $response->assertSee(config('app.venue_name', 'LapangIn Venue'));
        $response->assertSee(route('admin.laporan.index'));
        $response->assertDontSee('Bambang S.');
        $response->assertDontSee('Arena GOR Utama');

        // Test that laporan page is also accessible
        $laporanResponse = $this->actingAs($admin)->get(route('admin.laporan.index'));
        $laporanResponse->assertStatus(200);
        $laporanResponse->assertSee('Laporan &amp; Analitik Transaksi', false);
    }

    public function test_kasir_layout_renders_dynamic_cashier_and_shift(): void
    {
        $kasir = Pelanggan::factory()->create([
            'role' => 'kasir',
            'nama' => 'Rina Kasir Shift',
            'email' => 'rina.kasir@lapangin.id',
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($kasir)->get(route('kasir.index'));

        $response->assertStatus(200);
        $response->assertSee('Rina Kasir Shift');
        $response->assertSee('Shift');
        $response->assertDontSee('Bima Kasir');

        // If no pending reschedule, badge is hidden
        $this->assertEquals(0, Reschedule::where('status', 'pending')->count());
        $response->assertDontSee('3 pending');
    }

    public function test_frontend_navbar_handles_guest_and_auth_states(): void
    {
        // 1. Guest state
        $guestResponse = $this->get(route('home'));
        $guestResponse->assertStatus(200);
        $guestResponse->assertSee(route('login'));
        $guestResponse->assertSee(route('register'));
        $guestResponse->assertSee('Masuk');
        $guestResponse->assertSee('Daftar');
        $guestResponse->assertSee(route('katalog.index'));
        $guestResponse->assertSee(route('jadwal.index'));
        $guestResponse->assertDontSee('href="#"', false);

        // 2. Auth state
        $member = Pelanggan::factory()->create([
            'role' => 'pelanggan',
            'nama' => 'Dimas Member',
            'email' => 'dimas.member@gmail.com',
            'email_verified_at' => now(),
        ]);

        $authResponse = $this->actingAs($member)->get(route('home'));
        $authResponse->assertStatus(200);
        $authResponse->assertSee('Dimas Member');
        $authResponse->assertSee(route('pelanggan.booking.index'));
        $authResponse->assertSee(route('logout'));
        $authResponse->assertDontSee('href="#"', false);
    }
}
