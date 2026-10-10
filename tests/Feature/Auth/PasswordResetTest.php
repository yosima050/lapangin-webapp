<?php

namespace Tests\Feature\Auth;

use App\Models\Pelanggan;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_password_link_screen_can_be_rendered(): void
    {
        $response = $this->get('/forgot-password');

        $response->assertStatus(200);
    }

    public function test_reset_password_link_can_be_requested(): void
    {
        Notification::fake();

        $pelanggan = Pelanggan::factory()->create();

        $this->post('/forgot-password', ['email' => $pelanggan->email]);

        Notification::assertSentTo($pelanggan, ResetPassword::class);
    }

    public function test_reset_password_screen_can_be_rendered(): void
    {
        Notification::fake();

        $pelanggan = Pelanggan::factory()->create();

        $this->post('/forgot-password', ['email' => $pelanggan->email]);

        Notification::assertSentTo(
            $pelanggan,
            ResetPassword::class,
            function ($notification) {
                $response = $this->get('/reset-password/'.$notification->token);

                $response->assertStatus(200);

                return true;
            }
        );
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        Notification::fake();

        $pelanggan = Pelanggan::factory()->create();

        $this->post('/forgot-password', ['email' => $pelanggan->email]);

        Notification::assertSentTo(
            $pelanggan,
            ResetPassword::class,
            function ($notification) use ($pelanggan) {
                $response = $this->post('/reset-password', [
                    'token' => $notification->token,
                    'email' => $pelanggan->email,
                    'password' => 'new-password',
                    'password_confirmation' => 'new-password',
                ]);

                $response
                    ->assertSessionHasNoErrors()
                    ->assertRedirect(route('login'));

                return true;
            }
        );
    }
}