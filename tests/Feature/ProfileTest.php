<?php

namespace Tests\Feature;

use App\Models\Pelanggan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $pelanggan = Pelanggan::factory()->create();

        $response = $this
            ->actingAs($pelanggan)
            ->get('/profile');

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $pelanggan = Pelanggan::factory()->create();

        $response = $this
            ->actingAs($pelanggan)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $pelanggan->refresh();

        $this->assertSame('Test User', $pelanggan->name);
        $this->assertSame('test@example.com', $pelanggan->email);
    }

    public function test_user_can_delete_their_account(): void
    {
        $pelanggan = Pelanggan::factory()->create();

        $response = $this
            ->actingAs($pelanggan)
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull($pelanggan->fresh());
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $pelanggan = Pelanggan::factory()->create();

        $response = $this
            ->actingAs($pelanggan)
            ->from('/profile')
            ->delete('/profile', [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrorsIn('userDeletion', 'password')
            ->assertRedirect('/profile');

        $this->assertNotNull($pelanggan->fresh());
    }
}