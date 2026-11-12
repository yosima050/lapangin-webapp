<?php

namespace Tests\Feature\Auth;

use App\Models\Pelanggan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_password_can_be_updated(): void
    {
        $pelanggan = Pelanggan::factory()->create();

        $response = $this
            ->actingAs($pelanggan)
            ->from('/profile')
            ->put('/password', [
                'current_password' => 'password',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertTrue(
            Hash::check('new-password', $pelanggan->refresh()->password_hash)
        );
    }

    public function test_correct_password_must_be_provided_to_update_password(): void
    {
        $pelanggan = Pelanggan::factory()->create();

        $response = $this
            ->actingAs($pelanggan)
            ->from('/profile')
            ->put('/password', [
                'current_password' => 'wrong-password',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ]);

        $response
            ->assertSessionHasErrorsIn('updatePassword', 'current_password')
            ->assertRedirect('/profile');
    }

    public function test_google_sso_user_can_set_password_without_current_password(): void
    {
        $pelanggan = Pelanggan::factory()->create();

        $response = $this
            ->actingAs($pelanggan)
            ->withSession(['login_provider' => 'google'])
            ->from('/pelanggan')
            ->put('/password', [
                'password' => 'brand-new-secret',
                'password_confirmation' => 'brand-new-secret',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/pelanggan');

        $this->assertTrue(
            Hash::check('brand-new-secret', $pelanggan->refresh()->password_hash)
        );
    }
}