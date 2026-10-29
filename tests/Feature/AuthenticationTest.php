<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_authentication_pages_render(): void
    {
        $this->withoutVite();

        $this->get('/login')->assertOk();
        $this->get('/register')->assertOk();
        $this->get('/password/reset')->assertOk();
    }

    public function test_authenticated_users_can_open_home(): void
    {
        $this->withoutVite();

        $this->actingAs(User::factory()->create())->get('/home')->assertOk();
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/home')->assertRedirect('/login');
    }

    public function test_users_can_register(): void
    {
        $this->post('/register', [
            'name' => 'Upgrade Test',
            'email' => 'upgrade@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect('/home');

        $user = User::where('email', 'upgrade@example.com')->firstOrFail();

        $this->assertAuthenticatedAs($user);
        $this->assertTrue(Hash::check('password123', $user->password));
    }

    public function test_users_can_log_in_and_log_out(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect('/home');

        $this->assertAuthenticatedAs($user);

        $this->post('/logout')->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_passwords_can_be_reset_using_the_existing_token_table(): void
    {
        $user = User::factory()->create();
        $token = Password::createToken($user);

        $this->post('/password/reset', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-password123',
            'password_confirmation' => 'new-password123',
        ])->assertRedirect('/home');

        $this->assertTrue(Hash::check('new-password123', $user->fresh()->password));
        $this->assertDatabaseCount('password_resets', 0);
    }

    public function test_api_requires_authentication(): void
    {
        $this->getJson('/api/user')->assertUnauthorized();
    }

    public function test_sanctum_accepts_a_personal_access_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('upgrade-test')->plainTextToken;

        $this->withToken($token)->getJson('/api/user')
            ->assertOk()
            ->assertJsonPath('id', $user->id)
            ->assertJsonMissingPath('password');
    }
}
